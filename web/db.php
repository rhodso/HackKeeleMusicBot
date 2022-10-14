<?php
    function connectToDB() {
        // Connect to the database
        $db = new PDO('mysql:host=localhost;dbname=phpclassfall2014', 'root', '');
        return $db;
    }

    function ensureNoFunnyBusiness($string) {
        $string = trim($string);
        $string = stripslashes($string);
        $string = htmlspecialchars($string);
        return $string;
    }

    function detectFunnyBusiness($string){
        $testString = ensureNoFunnyBusiness($string);
        return $testString != $string;
    }
?>