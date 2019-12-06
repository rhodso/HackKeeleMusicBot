<html>
    <head>
		<title>HACK Keele | Music Voting System</title>
		<style>
			<div> 
				background-image: url('../img/background.jpg');
			</div>
		</style>

    </head>
    <body>
		<form action="/index.php">
			Video ID:<br>
			<input type="text" name="videoID" value="OWc1jaycOlQ">
			<br>
			<input type="submit" value="Submit">
		</form> 
		<?PHP
			$ID = $_GET['videoID'];
			print ("now playing: ".$ID);
			#$ID = "OWc1jaycOlQ";
			echo"<iframe width='560' height='315' src='https://www.youtube.com/embed/".$ID."?&autoplay=1' frameborder='0' allow='accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>";
		?>
	</body>
</html>