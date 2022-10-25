# Import libs
import time
import datetime
import json
import os
import threading
from threading import Thread

# Other libs
import youtube_dl
from audioplayer import AudioPlayer

def log(message):
    # Log a message to the console and include the current time
    print(str(datetime.datetime.now()) + '   ' + str(message))

def getIsSongDownloaded(url):
    # Get the song's video id
    videoID = url.split('=')[1]

    # Check if the song has already been downloaded
    return os.path.isfile('songs/' + videoID + '.mp3')

def getSong(url):
    # Set the song filepath
    songFP = ""

    # Get the song's video id
    videoID = url.split('=')[1]

    # Check if the song has already been downloaded
    if getIsSongDownloaded(url):
        # Set the song filepath, and return it
        songFP = 'songs/' + videoID + '.mp3'
        return songFP
    
    # Song not downloaded, so download it
    ydl_opts = {
        'format': 'bestaudio/best',
        'outtmpl': 'songs/' + videoID + '.%(ext)s',
        'postprocessors': [{
            'key': 'FFmpegExtractAudio',
            'preferredcodec': 'mp3',
            'preferredquality': '192',
        }],
    }
    # Try to download the song
    try:
        with youtube_dl.YoutubeDL(ydl_opts) as ydl:
            ydl.download([url])
            songFP = 'songs/' + videoID + '.mp3'
            return songFP

    except:
        return ""
        
    # Return the filepath of the song
    return songFP

def playSong(filepath):
    # Playsound function
    p = AudioPlayer(filename=filepath)
    p.play(block=True)
    # time.sleep(1)
    p.stop()
    return None

class ApiComms():
    def __init__(self):
        # Test API connection
        log("APIComms: Testing connection...")
        try:
            # TODO: Test API connection
            log("ApiComms: Connection successful!")
        except:
            log("ApiComms: Connection unsuccessful :(")
            exit()

    @staticmethod
    def songHasBeenPlayed(song):
        # Send a request to the API to say that the song has been played
        log("ApiComms: Song has been played: " + song)
        # TODO: Send request to API
        
    @staticmethod
    def updateSongslist():
        # Send a request to the API to get the song list
        log("ApiComms: Getting updated song list")
        pass

        # For now, just return a test song list
        jsonData = {}
        with open("test.json") as jsonFile:
            jsonData = json.load(jsonFile)

        log("ApiComms: Got updated song list")
        # Convert the json data to a list
        songList = []   
        for song in jsonData['songs']:
            songList.append(song)

        # Return the song list
        return songList

class SongList():
    songList = []
    lock = threading.Lock() # Threading lock

    def __init__(self):
        pass
    
    @staticmethod
    def updateSongList():
        # Update the song list
        SongList.lock.acquire()
        SongList.songList = ApiComms.updateSongslist()
        SongList.lock.release()

    @staticmethod
    def getSongList(ptr):
        # Lock the songList
        SongList.lock.acquire()
        log("SongList: Locking songList")
        songList = None
        try:
            if(len(SongList.songList) == 0):
                return "No songs in queue"
            if(ptr >= len(SongList.songList)):
                return "Need to reset pointer"
            if(ptr < 0):
                return "Need to reset pointer"
            
            # Return the songList
            songList = SongList.songList[ptr]
        finally:
            # Unlock the songList
            SongList.lock.release()
            log("SongList: Unlocking songList")
        
        if(songList == None):
            return "Other error"
        else:
            log("SongList: Returning songList")
            return songList

    @staticmethod
    def popSong(ptr):
        # Lock the songList
        SongList.lock.acquire()
        log("SongList: Locking songList")
        try:
            if(ptr >= len(SongList.songList)):
                return 1
            if(ptr < 0):
                return 1
            if(len(SongList.songList) == 0):
                return 1
            
            # Pop the song we just played from the songList
            SongList.songList.pop(ptr)
            log("SongList: Popped song from songList")
            return 0
        finally:
            # Unlock the songList
            SongList.lock.release()
            log("SongList: Unlocking songList")
        return 1

class SongPlayer(Thread):
    def __init__(self):
        Thread.__init__(self)
        self.ptr = 0

    def run(self):
        # Sleep some time on startup
        time.sleep(10)

        while True:
            log("SongPlayer: Getting song")
            # Get a song from the songList
            song = SongList.getSongList(self.ptr)

            if(song == "Other error"):
                # Increment the pointer, and continue
                log("SongPlayer: Other error, incrementing pointer")
                self.ptr += 1
                time.sleep(5)
                continue

            if(song == "Need to reset pointer"):
                # Reset the pointer, and continue
                log("SongPlayer: Need to reset pointer")
                self.ptr = 0
                time.sleep(5)
                continue

            if(song == "No songs in queue"):
                # Wait 65 seconds, and continue
                log("SongPlayer: No songs in queue, waiting 65 seconds")
                time.sleep(15)
                continue

            # Check that the song is downloaded
            # If not downloaded, increment the pointer, and continue
            if not getIsSongDownloaded(song):
                log("SongPlayer: Song not downloaded, incrementing pointer")
                self.ptr += 1
                time.sleep(5)
                continue

            log("SongPlayer: Playing song " + song)
            # Song exists, so play it
            songFP = getSong(song)
            if(songFP == ""):
                # Increment the pointer, and continue
                log("SongPlayer: Something went wrong playing song, incrementing pointer")
                self.ptr += 1
                time.sleep(5)
                continue

            # Play the song
            playSong(songFP)

            # Also reset the pointer
            self.ptr = 0
            log("SongPlayer: Song played")

            # Pop the song we just played from the songList
            log("SongPlayer: Popping song from songList")
            SongList.popSong(self.ptr)

            # Tell the API that the song has been played
            log("SongPlayer: Telling API that song has been played")
            ApiComms.songHasBeenPlayed(song)

            log("SongPlayer: finished playing song, playing the next song")
            time.sleep(5)

class SongDownloader(Thread):
    def __init__(self, url):
        Thread.__init__(self)
        self.url = url

    def run(self):
        # Download the song
        log("SongDownloader: Downloading song " + self.url)
        getSong(self.url)

log("Main: Starting up...")
player = []
try:
    # Create the PlaySongs thread
    player = SongPlayer()
    player.start()

    # Temporary testing player
    # time.sleep(10)
    # exit()

    while(True):
        # Update the song list from the API
        log("Main: Updating song list...")

        # Get the song list from the API
        songList = ApiComms.updateSongslist()
        SongList.updateSongList()
        log("Main: Got song list from API")

        # Create some threads to download the songs
        log("Main: Creating threads to download songs")
        threads = []
        for song in songList:
            thread = SongDownloader(song)
            thread.start()
            threads.append(thread)

        # Wait for the threads to finish
        for thread in threads:
            thread.join()
        log("Main: Finished downloading songs")

        # Wait some time
        time.sleep(30)

except KeyboardInterrupt:
    log("Main: Shutting down...")
    exit()

