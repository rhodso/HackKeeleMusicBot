<?php
    // Ensure the user is logged in
    if (!isset($_SESSION['user_id'])) {
        // If the user is not logged in, redirect to the login page
        header('Location: login.php');
        exit;
    }

    // Log the user out
    unset($_SESSION['user_id']);
    
    // Redirect to the login page
    header('Location: login.php');
?>