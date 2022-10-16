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

    // Handle the form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Check that $username, $password, and $password2 are set
        if (!isset($_POST['username'])){
            showError("Username not set");
            exit;
        }

        if (!isset($_POST['password'])){
            showError("Password not set");
            exit;
        }

        if (!isset($_POST['password2'])){
            showError("Password2 not set");
            exit;
        }
        // Get the username, password, and retyped password from the form
        $username = $_POST['username'];
        $password = $_POST['password'];
        $password2 = $_POST['password2'];

        // Strip characters that could be used to inject SQL or HTML from the username
        $username = removeTagsAndStuff($username);
        
        // Detect funny business
        if (detectFunnyBusiness($username, "string") || detectFunnyBusiness($password, "string") || detectFunnyBusiness($password2, "string")) {
            showError('Nice try, but no banana ;)');
            exit;
        }

        // Check that the username, password, and retyped password are not empty
        if(empty($username) || empty($password) || empty($password2)) {
            // If either the username or password are empty, show an error
            showError('Please fill out all fields');
            exit;
        }

        // Connect to the database
        $db = connectToDB();

        // Query the database for the user
        $sql = "SELECT * FROM user WHERE User_Name = :username";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $userExists = $stmt->fetch(PDO::FETCH_ASSOC);

        // If a user was found, show an error
        if ($userExists) {
            showError('That username is already taken');
            exit;
        }

        if ($password != $password2) {
            // If the password and retyped password do not match, show an error
            showError('Passwords do not match');
            exit;
        }

        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert the user into the database
        $sql = 'INSERT INTO user (User_Name, User_PasswordHash) VALUES (:username, :passwordHash)';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':passwordHash', $passwordHash);
        $stmt->execute();

        // Query the database for the user that was just created
        $sql = "SELECT User_ID FROM user WHERE User_Name = :username";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $userExists = $stmt->fetch(PDO::FETCH_ASSOC);

        if(count($userExists) == 1){
            // If the user was inserted into the database correctly, set the user_id session variable, and redirect to the home page
            $_SESSION['user_id'] = $userExists['User_ID'];
            header('Location: index.php');
        } else {
            // If the user was not inserted into the database correctly, show an error
            showError('There was an error creating your account');
            exit;
        }
    }
?>

<h2>Register</h2>
<!-- HTML form to handle registration -->
<!-- Regiter using username/password -->
<form action="register.php" method="post">
    <label for="username">Username</label><br>
    <input type="text" name="username" id="username"><br>
    <label for="password">Password</label><br>
    <input type="password" name="password" id="password"><br>
    <label for="password">Retype password</label><br>
    <input type="password" name="password2" id="password2"><br>
    <input type="submit" value="Register">
</form>
<br>
<br>
<p><i>Already have an account? <a href="login.php">Login here</a></i></p>

<?php include 'footer.php'; ?>
