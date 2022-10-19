<?php 
    include 'header.php'; 
    require_once 'db.php';
    ?>

<?php
    function showError($message) {
        echo '<p class="error">' . $message . '</p><br><a href=index.php>Return to home page</a>';
    }
    // Set the request timeout
    $requestTimeout = 3600;

    // Ensure the user is logged in
    if (!isset($_SESSION['user_id'])) {
        // If the user is not logged in, redirect to the login page
        header('Location: login.php');
        exit;
    }

    // Connect to the database
    $db = connectToDB();

    // Query the database for the user
    $stmt = $db->prepare('SELECT * FROM user WHERE User_ID = :id');
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If a user was not found, redirect to the login page
    if (!$user) {
        header('Location: login.php');
        exit;
    }

    // Get the user's request
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        

        // Check that $song is set
        if (!isset($_POST['song'])){
            showError("Song not set");
            exit;
        }

        // Handle the form submission
        $songUrl = $_POST['song'];

        // Detect funny business
        if (detectFunnyBusiness($songUrl, "string")) {
            showError('Nice try, but no banana ;)');
            exit;
        }

        // Check that the song URL is not empty
        if(empty($songUrl)) {
            // If the song URL is empty, show an error
            showError('Please fill out all fields');
            exit;
        }

        // Check that the song URL is valid
        if (!filter_var($songUrl, FILTER_VALIDATE_URL)) {
            // If the song URL is not valid, show an error
            showError('Please enter a valid URL');
            exit;
        }

        // Check that the song URL is a youtube video, m.youtube.com, or youtu.be
        if (strpos($songUrl, 'youtube.com') === false && strpos($songUrl, 'youtu.be') === false && strpos($songUrl, 'm.youtube.com') === false) {
            // If the song URL is not a youtube video, show an error
            showError('Please enter a youtube video URL');
            exit;
        }

        // Check if song has been requested before
        $stmt = $db -> prepare ('SELECT * FROM song WHERE Song_Url = :songUrl');
        $stmt -> bindParam(':songUrl', $songUrl);
        $stmt -> execute();
        $song = $stmt -> fetch(PDO::FETCH_ASSOC);
        
        // If $song is empty, insert the song into the database
        if (!$song) {

            // Get the title of the song from the youtube api
            $songTitle = "";

            // TODO, set to id (remove the youtube.com/watch?v= part)
            $songTitleParts = explode("=", $songUrl);
            $songTitle = $songTitleParts[1];

            $songInitTime = 1;
            
            // Insert the song into the database
            $sql = 'INSERT INTO Song (Song_LastRequest, Song_Url, Song_Title ) VALUES (:songLastRequest, :songUrl, :songTitle)';
            $stmt = $db -> prepare ($sql);
            $stmt -> bindParam(':songLastRequest', $songInitTime);
            $stmt -> bindParam(':songUrl', $songUrl);
            $stmt -> bindParam(':songTitle', $songTitle);
            $stmt -> execute();

            // Get the song ID
            $stmt = $db -> prepare('SELECT * FROM Song WHERE Song_Url = :songUrl');
            $stmt -> bindParam(':songUrl', $songUrl);
            $stmt -> execute();
            $sng = $stmt -> fetch(PDO::FETCH_ASSOC);

            $songId = $sng['Song_ID'];
        } else {

            // Get the song ID
            $songId = $song['Song_ID'];
        }


        // Check when the song was last requested
        $sql = "SELECT * FROM SongRequest WHERE Song_ID = :songId ORDER BY Request_Time DESC LIMIT 1";
        $stmt = $db -> prepare ($sql);
        $stmt -> bindParam(':songId', $songId);
        $stmt -> execute();
        $lastRequest = $stmt -> fetch(PDO::FETCH_ASSOC);

        // If no request was found 
        if (!$lastRequest) {
            $lastRequestTime = 1;
        } else {
            $lastRequestTime = $lastRequest['Request_Time'];
        }

        // Check if the song has been requested in the last hour
        if (time() - $lastRequestTime < $requestTimeout) {
            // If the song has been requested in the last hour, show an error
            showError('This song has already been requested in the last hour');
            exit;
        }

        $requestPlayed = 0;

        // Insert the songrequest into the database
        $sql = 'INSERT INTO SongRequest (User_ID, Song_ID, Request_Time, Request_Played) VALUES (:userId, :songId, :requestTime, :requestPlayed)';
        $stmt = $db -> prepare ($sql);
        $stmt -> bindParam(':userId', $_SESSION['user_id']);
        $stmt -> bindParam(':songId', $songId);
        $stmt -> bindParam(':requestTime', time());
        $stmt -> bindParam(':requestPlayed', $requestPlayed);
        $stmt -> execute();
        
        // Redirect to the home page
        header('Location: index.php');
    }
?>

<!-- HTML form to request a song -->
<!-- Song requres a valid YouTube URL -->

<form action="request.php" method="post">
    <label for="song">Song</label>
    <input type="text" name="song" id="song" placeholder="YouTube URL" />
    <input type="submit" value="Request" />
</form>
        
<?php include 'footer.php'; ?>