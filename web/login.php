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
        // Get the username and password from the form
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

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
        $stmt = $db->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // If a user was not found, show an error
        if (!$user) {
            showError('Invalid username');
            exit;
        }

        // Check that the password matches
        if (!password_verify($password, $user['password'])) {
            // If the password does not match, show an error
            showError('Username and password do not match');
            exit;
        }

        // Set the user_id session variable
        $_SESSION['user_id'] = $user['id'];

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