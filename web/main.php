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

    
    
    // Get a list of songRequests that have not been played
    $sql = 'SELECT * FROM SongRequest WHERE Request_Played = 0';
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $songRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $requestArray = array();

    // Loop through the songRequests
    foreach ($songRequests as $songRequest) {
        $score = 0;

        // Get a list of votes for the songRequest
        $sql = 'SELECT * FROM SongVote WHERE Request_ID = :songRequest_id';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':songRequest_id', $songRequest['Request_ID']);
        $stmt->execute();
        $songVotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Loop through the songVotes
        foreach ($songVotes as $songVote) {
            // Add the vote to the score
            $score += $songVote['Vote_Value'];
        }

        // Get the user that requested the song
        $sql = 'SELECT User_Name FROM user WHERE User_ID = :user_id';        
        $stmt = $db->prepare($sql);        
        $stmt->bindParam(':user_id', $songRequest['User_ID']);        
        $stmt->execute();        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
              
        // If a user was not found, show an error
        if (!$user) {
            showError('Invalid user');
            exit;
        }        

        // Get the song name and url from ID
        $sql = 'SELECT Song_Title, Song_Url FROM song WHERE Song_ID = :song_id';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':song_id', $songRequest['Song_ID']);
        $stmt->execute();
        $song = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Put the request in a list so it can be tabulated later
        $tmp = array();        
        $tmp['user'] = $user['User_Name'];        
        $tmp['song'] = $song['Song_Title'];        
        $tmp['url'] = $song['Song_Url'];
        $tmp['score'] = $score;
        $tmp['request_id'] = $songRequest['Request_ID'];
        
        // Add the request to the array
        array_push($requestArray, $tmp);     
    }

    // Sort the array by score
    usort($requestArray, function($a, $b) {
        return $b['score'] - $a['score'];
    });
?>

<!-- Link to request a song-->
<a href="request.php">Request a song</a><br><br>

<!-- Table of requests -->
<?php
    // If there are no requests, show a message
    if (count($requestArray) == 0) {
        echo '<p>There\'s no requests, why not add some?</p>';
    } else {
        // Otherwise, show the requests in a table
        echo '<table>';
        echo '<tr>';
        echo '<th>User</th>';
        echo '<th>Song</th>';
        echo '<th>URL</th>';
        echo '<th>Score</th>';
        echo '<th>Vote Up</th>';
        echo '<th>Vote Down</th>';
        echo '</tr>';
        foreach ($requestArray as $request) {
            echo '<tr>';
            echo '<td>' . $request['user'] . '</td>';
            echo '<td>' . $request['song'] . '</td>';
            echo '<td><a href=' . $request['url'] . '>' . $request['url'] . '</a></td>';
            echo '<td>' . $request['score'] . '</td>';
            echo '<td><a href="https://richard.keithsoft.com/hkmb/api.php?request=2&request_id='.$request['request_id'].'&vote=1">Vote up</a></td>';
            echo '<td><a href="https://richard.keithsoft.com/hkmb/api.php?request=2&request_id='.$request['request_id'].'&vote=-1">Vote down</a></td>';
            echo '</tr>';
        }
        echo '</table>';
    }
?>
<br><br>
<!-- Link to logout -->
<a href="logout.php">Logout</a>
    
<?php include 'footer.php'; ?>