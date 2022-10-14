<?php
    function connectToDB() {
        // Connect to the database
        $db_name = "./db/songDB";
        $db = new SQLite3($db_name);
        return $db;
    }

    function ensureNoFunnyBusiness($string) {
        $string = trim($string);
        $string = stripslashes($string);
        $string = htmlspecialchars($string);
        return $string;
    }

    function detectFunnyBusiness($string, $expected_type){
        switch($expected_type){
            case "string":
                // Check that the string is a string
                if (!is_string($string)) {
                    return true;
                }
                break;
            case "integer":
                // Check that the string is an integer
                if (!is_int($string)) {
                    return true;
                }
                break;
            case "number":
                // Check that the string is a number
                if (!is_numeric($string)) {
                    return true;
                }
                break;
        }

        // Type is ok, trim
        $string = trim($string);

        $testString = ensureNoFunnyBusiness($string);
        return $testString != $string;
    }
?>