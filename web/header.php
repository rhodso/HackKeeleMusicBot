<?php
    session_start();
?>

<Doctype html>
    <html>
        <head>
            <title>Hack Keele Music Bot</title>
        </head>
        <body>
            <h1>Hack Keele Music Bot</h1>
            <?php 
                // read the taglines.txt file into an array
                $taglines = file('taglines.txt');
                // pick a random line
                $tagline = $taglines[array_rand($taglines)];
                // print the tagline
                echo "<h2><i>" . $tagline . "</i></h2>";
            ?>