<?php 
    include 'header.php'; 
    require_once 'db.php';
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
    $stmt = $db->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // If a user was not found, redirect to the login page
    if (!$user) {
        header('Location: login.php');
        exit;
    }

    // Handle the form submission
    $songUrl = trim($_POST['song']);

    // Detect funny business
    if (detectFunnyBusiness($songUrl)) {
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

    // Check when the song was last requested
    // TODO
    $lastRequest = 0;

    // If the song was requested less than an hour ago, show an error
    if ($lastRequest > 0 && time() - $lastRequest < 3600) {
        showError('Please wait at least an hour before requesting the same song again');
        exit;
    }

    // Insert the song into the database
    // TODO

    // Redirect to the home page
    header('Location: index.php');
?>

<!-- HTML form to request a song -->
<!-- Song requres a valid YouTube URL -->
<form action="request.php" method="post">
    <label for="song">Song</label>
    <input type="text" name="song" id="song" placeholder="YouTube URL" />
    <input type="submit" value="Request" />
</form>
        
<?php include 'footer.php'; ?>