<?php
    // Show an error message
    function showError($message) {
        echo '<p class="error">' . $message . '</p>';
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
        0 - Get a list of songs that haven't been played yet
        1 - Set a song as played
        2 - Change the vote count for a song
    */

    // Deal with request 0
    if ($request == 0) {
        // Connect to the database
        $db = connectToDB();

        // Query the database for the song requests
        $stmt = $db->prepare('SELECT * FROM SongRequest WHERE Request_Played = 0');
        $stmt->execute();
        $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the songs
        echo json_encode($songs);
        exit;
    }

    // Deal with request 1
    if ($request == 1) {
        // Check that $song_id is set
        if (!isset($_GET['request_id'])){
            showError("Song ID not set");
            exit;
        }

        // Get the song_id from the parameters
        $request_id = $_GET['request_id'];

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
        // Check that $user_id is set
        if (!isset($_GET['user_id'])){
            showError("User ID not set");
            exit;
        }

        // Get the user_id from the session
        $user_id = $_GET['user_id'];
        
        // Detect funny business
        if (detectFunnyBusiness($user_id, "integer")) {
            showError('Nice try, but no banana ;)');
            exit;
        }

        // Check that the user is logged in
        if (!isset($_SESSION['user_id'])) {
            showError('You must be logged in to vote');
            exit;
        }

        // Check that the user is not voting for themselves
        if ($user_id == $_SESSION['user_id']) {
            showError('You cannot vote for yourself');
            exit;
        }

        // If a user was not found, show an error
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            showError('Invalid user');
            exit;
        }

        // Check that $request_id is set
        if (!isset($_GET['request_id'])){
            showError("Song ID not set");
            exit;
        }

        // Get the request_id from the parameters
        $request_id = $_GET['request_id'];
        
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
        $vote = $_GET['vote'];

        // Detect funny business
        if (detectFunnyBusiness($vote, "integer")) {
            showError('Nice try, but no banana ;)');
            exit;
        }

        // Connect to the database
        $db = connectToDB();

        // Check that the user is valid
        $stmt = $db->prepare('SELECT * FROM users WHERE user_id = :user_id');
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        // If a user was not found, show an error
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            showError('Invalid user');
            exit;
        }        

        // Query the database for the user
        $stmt = $db->prepare('UPDATE SongRequest SET Request_Votes = Request_Votes + :vote WHERE Request_ID = :request_id');
        $stmt->bindParam(':request_id', $request_id);
        $stmt->bindParam(':vote', $vote);
        $stmt->execute();

        // Return success message
        echo json_encode(array("OK" => true));
        exit;
    }

?>
