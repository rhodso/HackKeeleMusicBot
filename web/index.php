<?php 
    include 'header.php'; 
    require_once 'db.php';
?>

<?php
    // Refresh the page every 60 seconds
    header("Refresh:60");

?>

<?php
    function showLog($message) {
        echo '<p class="log">' . $message . '</p>';
    }

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
    echo '<p>Welcome, <i>' . $username . '</i><p>';

    showLog("Get a list of requests");
    
    // Get a list of songRequests that have not been played
    $sql = 'SELECT * FROM SongRequest WHERE Request_Played = 0';
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $songRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    showLog("Display the list of requests");

    $requestArray = array();

    // Log the length of the array
    showLog("Length of array: " . count($songRequests));

    // Loop through the songRequests
    foreach ($songRequests as $songRequest) {
        var_dump($songRequest);

        // Get the user that requested the song
        $sql = 'SELECT User_Name FROM user WHERE User_ID = :user_id';
        showLog("Get the user that requested the song");
        $stmt = $db->prepare($sql);
        showLog("Bind the user_id");
        $stmt->bindParam(':user_id', $songRequest['User_ID']);
        showLog("Execute the query");
        $stmt->execute();
        showLog("Fetch the result");
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        var_dump($user);
        showLog("Get the user name");

        // If a user was not found, show an error
        if (!$user) {
            showError('Invalid user');
            exit;
        }
        showLog("Add the request to the array");

        // Get the song name and url from ID
        $sql = 'SELECT Song_Title, Song_URL FROM song WHERE Song_ID = :song_id';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':song_id', $songRequest['Song_ID']);
        $stmt->execute();
        $song = $stmt->fetch(PDO::FETCH_ASSOC);
        var_dump($song);

        // Put the request in a list so it can be tabulated later
        $tmp = array();
        $tmp['user'] = $user['User_Name'];
        $tmp['song'] = $song['Song_Title'];
        $tmp['url'] = $song['Song_URL'];
        $requestArray.push($tmp);

        var_dump($tmp);
    }

    var_dump($requestArray);
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