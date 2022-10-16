<?php 
    include 'header.php'; 
    require_once 'db.php';
?>


<?php
    function showError($message) {
        echo '<p class="error">' . $message . '</p>';
    }

    // Detect if the user is logged in
    if (isset($_SESSION['user_id'])) {
        // If the user is logged in, redirect to the home page
        header('Location: index.php');
        exit;
    }

    // Handle login form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Check that $username and $password are set
        if (!isset($_POST['username'])){
            showError("Username not set");
            exit;
        }

        if (!isset($_POST['password'])){
            showError("Password not set");
            exit;
        }

        // Get the username and password from the form
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Strip characters that could be used to inject SQL or HTML from the username
        $username = removeTagsAndStuff($username);

        // Detect funny business
        if (detectFunnyBusiness($username, "string") || detectFunnyBusiness($password, "string")) {
            showError('Nice try, but no banana ;)');
            exit;
        }

        // Check that the username and password are not empty
        if(empty($username) || empty($password)) {
            // If either the username or password are empty, show an error
            showError('Please fill out all fields');
            exit;
        }

        // Connect to the database
        $db = connectToDB();

        // Query the database for the user
        $stmt = $db->prepare('SELECT * FROM user WHERE User_Name = :username');
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $userReturn = $stmt->fetch(PDO::FETCH_ASSOC);

        // If a user was not found, show an error
        if (!$userReturn) {
            showError('Invalid username');
            exit;
        }

        // Check that the password matches
        if (!password_verify($password, $userReturn['User_PasswordHash'])) {
            // If the password does not match, show an error
            showError('Username and password do not match');
            exit;
        }

        showError('Login successful!');

        // Set the user_id session variable
        $_SESSION['user_id'] = $userReturn['User_ID'];

        // Redirect to the home page
        header('Location: index.php');
        exit;
    }

?>

<h2>Login</h2>
<!-- HTML form to handle login -->
<!-- Login using username/password -->
<form action="login.php" method="post">
    <label for="username">Username</label><br>
    <input type="text" name="username" id="username"><br>
    <label for="password">Password</label><br>
    <input type="password" name="password" id="password"><br>
    <input type="submit" value="Login">
</form>
<br>
<br>
<p><i>Don't have an account? <a href="register.php">Register here</a></i></p>

<?php include 'footer.php'; ?>