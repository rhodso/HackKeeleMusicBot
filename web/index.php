<?php 
    include 'header.php'; 
    require_once 'db.php';
?>

<?php
    // Refresh the page every 60 seconds
    header("Refresh:60");

?>

<?php

    // Ensure the user is logged in
    if (!isset($_SESSION['user_id'])) {
        // If the user is not logged in, redirect to the login page
        header('Location: login.php');
        exit;
    }

    // Connect to the database
    $db = connectToDB();

    // Query the database for the user
    $stmt = $db->prepare('SELECT User_Name FROM user WHERE User_ID = :user_id');
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $userReturn = $stmt->fetch(PDO::FETCH_ASSOC);

    // If a user was not found, show an error
    if (!$userReturn) {
        showError('Invalid user, please log in again');
        exit;
    }

    // Get the user's name
    $username = $userReturn['User_Name'];

    // Show a welcome message
    echo '<p><i>Welcome ' . $username . '</i><p>';
    
    // Get a list of songRequests that have not been played
    $stmt = $db->prepare('SELECT * FROM songRequests WHERE played = 0');
    $stmt->execute();
    $songRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $requestArray = array();

    // Loop through the songRequests
    foreach ($songRequests as $songRequest) {
        // Get the user that requested the song
        $sql = 'SELECT * FROM SongRequest WHERE Request_Played = 0';
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Put the request in a list so it can be tabulated later
        $tmp = array();
        $tmp['user'] = $user['username'];
        $tmp['song'] = $songRequest['song'];
        $tmp['url'] = $songRequest['url'];
        $requestArray.push($tmp);
    }
?>


<!-- Link to request a song-->
<a href="request.php">Request a song</a>

<!-- Table of requests -->
<?php
    // If there are no requests, show a message
    if (count($requestArray) == 0) {
        echo '<p>No requests</p>';
    } else {
        // Otherwise, show the requests
        echo '<table>';
        echo '<tr><th>URL</th><th>Votes</th><th>User ID</th></tr>';
        foreach ($requestArray as $request) {
            // Get a list of SongVote entries for this request ID
            $stmt = $db->prepare('SELECT * FROM SongVote WHERE Vote_Request_ID = :request_id');
            $stmt->bindParam(':request_id', $request['request_id']);
            $stmt->execute();
            $votes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Create a sum, and then add the voteValue of each vote
            $voteSum = 0;
            foreach ($votes as $vote) {
                $voteSum += $vote['Vote_Value'];
            }

            echo '<tr>';
            echo '<td><a href="' . $request['url'] . '">' . $request['song'] . '</a></td>';
            echo '<td>' . $voteSum . '</td>';
            echo '<td>' . $request['user'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
?>

<!-- Link to logout -->
<a href="logout.php">Logout</a>
    
<?php include 'footer.php'; ?>