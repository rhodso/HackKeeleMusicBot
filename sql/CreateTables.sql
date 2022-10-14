-- Drop tables if they exist
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `SongRequest`;
DROP TABLE IF EXISTS `Song`;
DROP TABLE IF EXISTS `SongVote`;

CREATE TABLE `user` (
	`User_ID` INT NOT NULL AUTO_INCREMENT,
	`User_Name` TEXT,
	`User_PasswordHash` TEXT,
	PRIMARY KEY (`User_ID`)
);

CREATE TABLE `SongRequest` (
	`Request_ID` INT NOT NULL AUTO_INCREMENT,
	`User_ID` INT,
	`Song_ID` INT,
	`Request_Time` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`Request_Played` BOOLEAN,
	PRIMARY KEY (`Request_ID`)
);

CREATE TABLE `Song` (
	`Song_ID` INT NOT NULL AUTO_INCREMENT,
	`Song_LastRequest` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`Song_Url` TEXT,
	`Song_Title` TEXT,
	PRIMARY KEY (`Song_ID`)
);

CREATE TABLE `SongVote` (
	`Vote_ID` INT NOT NULL AUTO_INCREMENT,
	`Request_ID` INT,
	`User_ID` INT,
	`Vote_Value` INT,
	PRIMARY KEY (`Vote_ID`)
);

