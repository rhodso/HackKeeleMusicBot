<?php
    require_once 'db.php';

    // phpinfo();

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Start the session
    session_start();

    // Show an error message
    function showError($message) {
        echo '<p class="error">' . $message . '</p>';
        echo '<br><a href=main.php>Return to home page</a>';
    }

    // Get the request from html parameter
    $request = $_GET['request'];

    // If the request is not set, show an error
    if (!isset($request)) {
        showError('Request not set');
        exit;
    }

    // Detect funny business
    if (detectFunnyBusiness($request, "string")) {
        showError('Nice try, but no banana ;)');
        exit;
    }

    // Figure out what the request is
    /*
        -1 - Test connection
        0 - Get a list of requests
        1 - Set a song as played
        2 - Change the vote count for a song
        3 - Get a list of songs in the DB
        4 - Get info about a song from its ID
    */

    // Deal with request -1
    if ($request == -1) {
        echo 'OK';
    }

    // Deal with request 0
    if ($request == 0) {
        // Connect to the database
        $db = connectToDB();

        // Query the database for the song requests
        $sql = "SELECT sr.request_id, sr.song_id, sum(sv.vote_value) as votes FROM Songrequest sr left join songvote sv on sr.request_id = sv.request_id where sr.request_played is false group by sr.request_id, sr.song_id order by votes desc";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the songs
        echo json_encode($songs);
        exit;
    }
    
    // Deal with request 3
    if ($request == 3){
        $db = connectToDB();
        $stmt = $db->prepare('SELECT * FROM Song');
        $stmt->execute();
        $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Encode the songs as JSON
        echo json_encode($songs);
        exit;
    }

    if($request == 4){
        if(!isset($_GET['song_id'])){
            showError('Song ID not set');
            exit;
        }

        $song_id = intval(trim($_GET['song_id']));
        if(detectFunnyBusiness($song_id, "int")){
            showError('Nice try, but no banana ;)');
            exit;
        }

        $db = connectToDB();
        $stmt = $db->prepare('SELECT * FROM Song WHERE Song_ID = :song_id');
        $stmt->bindParam(':song_id', $song_id);
        $stmt->execute();
        $song = $stmt->fetch(PDO::FETCH_ASSOC);

        // Encode the info as JSON
        echo json_encode($song);
        exit;
    }

    // Deal with request 5
    if($request == 5){
        if(!isset($_GET['song_id'])){
            showError('Song ID not set');
            exit;
        }

        $song_id = intval(trim($_GET['song_id']));
        if(detectFunnyBusiness($song_id, "int")){
            showError('Nice try, but no banana ;)');
            exit;
        }

        $db = connectToDB();
        $stmt = $db->prepare('UPDATE SongRequest SET Request_Played = 1 WHERE Song_ID = :song_id');
        $stmt->bindParam(':song_id', $song_id);
        $stmt->execute();

        echo "OK";
        exit;
    }

    // Deal with request 1
    if ($request == 1) {
        // Check that $request_id is set
        if (!isset($_GET['request_id'])){
            showError("Request ID not set");
            exit;
        }

        // Get the song_id from the parameters
        $request_id = intval(trim($_GET['request_id']));
        
        // Detect funny business
        if (detectFunnyBusiness($request_id, "integer")) {
            showError('Nice try, but no banana ;)');
            exit;
        }

        // Connect to the database
        $db = connectToDB();

        // Query the database for the user
        $stmt = $db->prepare('UPDATE SongRequest SET Request_Played = 1 WHERE Request_Played = 0 AND Request_ID = :request_id');
        $stmt->bindParam(':request_id', $request_id);
        $stmt->execute();

        // Return success message
        echo json_encode(array("OK" => true));
        exit;
    }

    // Deal with request 2
    if ($request == 2) {

        // Check that the user is logged in
        if (!isset($_SESSION['user_id'])) {
            showError('You must be logged in to vote');
            // Show the login button
            echo '<a href="login.php">Login</a>';
            exit;
        }
        $user_id = $_SESSION['user_id'];

        // Check that $request_id is set
        if (!isset($_GET['request_id'])){
            showError("Song ID not set");
            exit;
        }

        // Get the request_id from the parameters
        $request_id = intval($_GET['request_id']);
        
        // Detect funny business
        if (detectFunnyBusiness($request_id, "integer")) {
            showError('Nice try, but no banana ;)');
            exit;
        }

        // Check that $vote is set
        if (!isset($_GET['vote'])){
            showError("Vote not set");
            exit;
        }

        // Get the vote from the parameters
        $vote = intval($_GET['vote']);

        // Detect funny business
        if (detectFunnyBusiness($vote, "integer")) {
            showError('Nice try, but no banana ;)');
            exit;
        }

        // Detect invalid vote, must be 1, 0, or -1
        if ($vote != 1 && $vote != 0 && $vote != -1) {
            showError('Invalid vote');
            exit;
        }

        // Connect to the database
        $db = connectToDB();

        // Check that the user is valid
        $stmt = $db->prepare('SELECT * FROM user WHERE User_ID = :user_id');
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // If a user was not found, show an error
        if (!$user) {
            showError('Invalid user');
            exit;
        }

        // Get the request info
        $stmt = $db->prepare('SELECT * FROM SongRequest WHERE Request_ID = :request_id');
        $stmt->bindParam(':request_id', $request_id);
        $stmt->execute();
        $songRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        // If a song request was not found, show an error
        if (!$songRequest) {
            showError('Invalid song request');
            exit;
        }

        // If the user submitted the request, show an error
        if ($songRequest['User_ID'] == $user_id) {
            showError('You cannot vote for your own request');
            exit;
        }

        // Check if the user has already voted
        $stmt = $db->prepare('SELECT * FROM SongVote WHERE User_ID = :user_id AND Request_ID = :request_id');
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':request_id', $request_id);
        $stmt->execute();
        $pVote = $stmt->fetch(PDO::FETCH_ASSOC);

        // If the user hasn't voted, insert a new vote
        if(!$pVote) {    
            // Add the vote
            $stmt = $db->prepare('INSERT INTO SongVote (User_ID, Request_ID, Vote_Value) VALUES (:user_id, :request_id, :vote)');
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':request_id', $request_id);
            $stmt->bindParam(':vote', $vote);
            $stmt->execute();
        }else{
            // If the old vote is the same as the new vote, set the vote to 0
            if ($pVote['Vote_Value'] == $vote) {
                $vote = 0;
            }

            // User has already voted, so update the vote
            $stmt = $db->prepare('UPDATE SongVote SET Vote_Value = :vote WHERE User_ID = :user_id AND Request_ID = :request_id');
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':request_id', $request_id);
            $stmt->bindParam(':vote', $vote);
            $stmt->execute();
        }

        // Return to main.php
        header('Location: main.php');
    }
    
?>
