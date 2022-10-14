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
                if(get_class("a string") != get_class($string)){
                    return false;
                }
                break;
            case "integer":
                if(get_class(420) != get_class($string)){
                    return false;
                }
                break;
            case "number":
                if(get_class(420.69) != get_class($string)){
                    return false;
                }
                break;
        }
        $testString = ensureNoFunnyBusiness($string);
        return $testString != $string;
    }
?>