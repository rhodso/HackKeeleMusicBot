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

    // Get a list of songRequests that have not been played
    $stmt = $db->prepare('SELECT * FROM songRequests WHERE played = 0');
    $stmt->execute();
    $songRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If there are no songRequests, show a message
    if (count($songRequests) == 0) {
        echo '<p>There are no songs in the queue, why not request some?</p>';
    }

    $requestArray = array();

    // Loop through the songRequests
    foreach ($songRequests as $songRequest) {
        // Get the user that requested the song
        $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->bindParam(':id', $songRequest['user_id']);
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

<!-- Headers, etc -->


<!-- Link to request a song-->
<a href="request.php">Request a song</a>


<!-- Display table of song requests -->
<table>
    <tr>
        <th>User</th>
        <th>Song</th>
        <th>URL</th>
    </tr>
    <?php foreach ($requestArray as $request) { ?>
        <tr>
            <td><?php echo $request['user']; ?></td>
            <td><?php echo $request['song']; ?></td>
            <td><?php echo $request['url']; ?></td>
        </tr>
    <?php } ?>

</table>

<!-- Link to logout -->
<a href="logout.php">Logout</a>
    
<?php include 'footer.php'; ?>