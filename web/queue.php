<?php 
    include 'header.php'; 
    require_once 'db.php';
?>

<?php
    // Refresh the page every 60 seconds
    header("Refresh:30");

?>

<?php
    // Connect to the database
    $db = connectToDB();
    
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

<!-- Table of requests -->
<?php
    echo("<h1>Currently queued songs</h1>");

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
        echo '</tr>';
        foreach ($requestArray as $request) {
            echo '<tr>';
            echo '<td>' . $request['user'] . '</td>';
            echo '<td>' . $request['song'] . '</td>';
            echo '<td><a href=' . $request['url'] . '>' . $request['url'] . '</a></td>';
            echo '<td>' . $request['score'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
?>

<?php include 'footer.php'; ?>