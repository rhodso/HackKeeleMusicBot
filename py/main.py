# Import libs
import time
import datetime
import json
import os
import threading
from threading import Thread
import requests

# Other libs
import youtube_dl
from audioplayer import AudioPlayer

def log(message):
    # Log a message to the console and include the current time
    print(str(datetime.datetime.now()) + '   ' + str(message))

def getIsSongDownloaded(songDict):
    # Get the song's video id
    songID = songDict['Song_ID']

    # Check if the song has already been downloaded
    return os.path.isfile('songs/' + songID + '.mp3')

def getIsSongLivestreamFromUrl(url):
    # Use a system call to youtube-dl to get if it's a livestream
    # This is a bit of a hack, but it works

    video = url
    ydl_opts = {
        'format': 'bestaudio/best',
        'outtmpl': 'songs/tmp.%(ext)s',
        'postprocessors': [{
            'key': 'FFmpegExtractAudio',
            'preferredcodec': 'mp3',
            'preferredquality': '192',
        }],
    }

    with youtube_dl.YoutubeDL(ydl_opts) as ydl:
        info_dict = ydl.extract_info(video, download=False)
        is_live = info_dict.get('is_live', None)

    return is_live

def skipSong(url):
    # Get info about the song
    ydl_opts = {
        'format': 'bestaudio/best',
        'outtmpl': 'songs/tmp.%(ext)s',
        'postprocessors': [{
            'key': 'FFmpegExtractAudio',
            'preferredcodec': 'mp3',
            'preferredquality': '192',
        }],
    }

    with youtube_dl.YoutubeDL(ydl_opts) as ydl:
        info_dict = ydl.extract_info(url, download=False)   
        duration = info_dict.get('duration', None)
        is_live = info_dict.get('is_live', None)
        age_limit = info_dict.get('age_limit', None)

    if(duration < 5):
        return True
    
    if(duration > 720):
        return True

    if(age_limit > 15):
        return True

    if(is_live is None):
        return True
    
    return is_live

def getSong(songDict):
    songID = songDict['Song_ID']
    fileName = songDict['Song_ID']
    
    # Check if the song has already been downloaded
    if getIsSongDownloaded(songDict):
        log('GetSong: Song already downloaded, skipping...')
        # Set the song filepath, and return it
        songFP = 'songs/' + fileName + '.mp3'
        return songFP
    
    log("GetSong: Downloading song...")
    # Ask the API for song info
    songInfoJson = ApiComms.getSongInfo(songID)
    if(songInfoJson == None):
        return None
    songInfo = json.loads(songInfoJson)

    #Get the url
    url = songInfo['Song_Url']
    # Set the song filepath
    songFP = ""

    # Check if the song should be skipped
    if(skipSong(url)):
        # Tell the API that the song has been skipped
        ApiComms.skipSong(songID)
        return None

    # Song not downloaded, so download it
    ydl_opts = {
        'format': 'bestaudio/best',
        'outtmpl': 'songs/' + fileName + '.%(ext)s',
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
            songFP = 'songs/' + fileName + '.mp3'
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
        pass

    @staticmethod
    def testConnection():
        # Test API connection
        log("APIComms: Testing connection...")
        url = "https://richard.keithsoft.com/hackKeeleMusicBot/api.php?request=-1"
        responseData = ""
        try:
            # Get the response
            response = requests.get(url)
            if response.status_code == 200:
                responseData = response.text
                if(responseData == "OK"):
                    log("APIComms: Connection successful")
                    return True
                else:
                    log("APIComms: Connection unsuccessful")
                    return False
        except:
            log("ApiComms: Connection unsuccessful, exiting...")
            return False

    @staticmethod
    def skipSong(songID):
        log("Skipping song " + songID)
        url = "https://richard.keithsoft.com/hackKeeleMusicBot/api.php?request=5&song_id=" + songID
        responseData = ""
        try:
            response = requests.get(url)
            if response.status_code == 200:
                responseData = response.text
                if(responseData == "OK"):
                    log("APIComms: Skip successful")
                else:
                    log("APIComms: Skip unsuccessful")
        except:
            log("APIComms: Skip unsuccessful")


    @staticmethod
    def getSongInfo(songID):
        log("APIComms: Getting song info for song ID: " + str(songID))
        url = "https://richard.keithsoft.com/hackKeeleMusicBot/api.php?request=4&song_id=" + str(songID)
        jsonData = ""
        try:
            # Get the response
            response = requests.get(url)
            if response.status_code == 200:
                jsonData = response.text
                return jsonData

        except:
            log("APIComms: Error getting song info")
            return None
        
    @staticmethod
    def songHasBeenPlayed(requestID):
        # Send a request to the API to say that the song has been played
        log("ApiComms: Song has been played: " + requestID)
        url = "https://richard.keithsoft.com/hackKeeleMusicBot/api.php?request=1"
        jsonData = ""
        try:
            # Append the request ID to the URL
            url += "&request_id=" + requestID
            response = requests.get(url)
            if response.status_code == 200:
                jsonData = json.loads(response.text)
                if(jsonData['OK']):
                    log("ApiComms: Song marked as played!")
                else:
                    log("ApiComms: Error marking song as played!")
            else:
                log("ApiComms: Error marking song as played!")
        except:
            log("ApiComms: Error marking song as played!")
        return None
    
    @staticmethod
    def updateRequestsList():
        # Get the requests list from the API
        log("ApiComms: Getting requests list...")
        url = "https://richard.keithsoft.com/hackKeeleMusicBot/api.php?request=0"
        jsonData = ""
        try:
            response = requests.get(url)
            if response.status_code == 200:
                jsonData = json.loads(response.text)
                log("ApiComms: Requests list received!")
                requestsDictList = []
                # Loop through the requests
                for songRequest in jsonData:
                    # If the song's votes is set to null, then set it to 0
                    if songRequest['votes'] == None:
                        songRequest['votes'] = 0
                    requestsDictList.append(songRequest)
                        
                return requestsDictList
            else:
                log("ApiComms: Error getting requests list!")
        except:
            log("ApiComms: Error getting requests list!")
        return None
        
    @staticmethod
    def updateSongslist():
        # Send a request to the API to get the song list
        log("ApiComms: Getting updated song list")
        
        url = "https://richard.keithsoft.com/hackKeeleMusicBot/api.php?request=3"
        jsonData = ""
        # Perform the request
        try:
            response = requests.get(url)
            # Check if the request was successful
            if response.status_code == 200:
                # Request was successful, so return the response
                jsonData = response.json()
                return jsonData

        except:
            log("ApiComms: Error getting song list")
            return None

        # Return the song list
        return jsonData

class SongList():
    songList = []
    requstList = []
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
    def updateRequestsList():
        # Update the requests list
        SongList.lock.acquire()
        SongList.requestList = ApiComms.updateRequestsList()
        SongList.lock.release()

    @staticmethod
    def getSongList():
        sl = ApiComms.updateSongslist()
        return sl

    @staticmethod
    def getRequestsList():
        rl = ApiComms.updateRequestsList()
        return rl

    @staticmethod
    def getNextRequest(ptr):
        rl = SongList.getRequestsList()
        if(rl == None):
            log("SongList: Request list is empty")
            return "Need to update request list"
        if(ptr >= len(rl)):
            log("SongList: Pointer out of range")
            return "Pointer out of range"

        # Return the request at the given position
        return rl[ptr]
        
        
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
            song = SongList.getNextRequest(self.ptr)    

            if song == "Need to update request list":
                log("SongPlayer: Need to update request list")
                time.sleep(10)
                SongList.updateRequestsList()
                continue
            if song == "Pointer out of range":
                log("SongPlayer: Pointer out of range")
                self.ptr = 0
                continue
            
            # Check that the song is downloaded
            # If not downloaded, increment the pointer, and continue
            if not getIsSongDownloaded(song):
                log("SongPlayer: Song not downloaded, incrementing pointer")
                self.ptr += 1
                time.sleep(5)
                continue

            # Check that the song returned is a dict
            if type(song) is not dict:
                log("SongPlayer: Song is not a dict")
                self.ptr += 1
                time.sleep(5)
                continue
        
            # Get the important song details
            songID = song['Song_ID']


            log("SongPlayer: Playing song " + song['Song_ID'])
            # Song exists, so play it
            songFP = getSong(song)
            if(songFP == "" or songFP == None):
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
            ApiComms.songHasBeenPlayed(song['Request_ID'])

            log("SongPlayer: finished playing song, playing the next song")
            time.sleep(5)

class SongDownloader(Thread):
    def __init__(self, songDict):
        Thread.__init__(self)
        self.songDict = songDict

    def run(self):
        # Download the song
        log("SongDownloader: Downloading song " + self.songDict["Song_Url"])
        getSong(self.songDict)

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
        # Test connection to the server
        if not ApiComms.testConnection():
            log("Main: Connection to the server failed")
            time.sleep(15)
            continue

        # Update the song list from the API
        log("Main: Updating song list...")

        # Get the song list from the API
        SongList.updateSongList()
        sl = SongList.getSongList()
        log("Main: Got song list from API")

        # Create some threads to download the songs
        log("Main: Creating threads to download songs")
        threads = []
        for song in sl:
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

