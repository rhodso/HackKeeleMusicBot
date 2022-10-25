DROP TABLE IF EXISTS "user";
CREATE TABLE 'user' (
	'User_ID' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	'User_Name' TEXT, 
	'User_PasswordHash' TEXT
);

DROP TABLE IF EXISTS "Song";
CREATE TABLE 'Song' (
	'Song_ID' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	'Song_LastRequest' INTEGER, 
	'Song_Url' TEXT, 
	'Song_Title' TEXT
);

DROP TABLE IF EXISTS "SongRequest";
CREATE TABLE 'SongRequest' (
	'Request_ID' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	'User_ID' INTEGER, 
	'Song_ID' INTEGER, 
	'Request_Time' DATETIME, 
	'Request_Played' BOOLEAN
);

DROP TABLE IF EXISTS "SongVote";
CREATE TABLE 'SongVote' (
	'Vote_ID' INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
	'Request_ID' INTEGER, 
	'User_ID' INTEGER, 
	'Vote_Value' INTEGER
);
