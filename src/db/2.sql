CREATE TABLE `liteauth` (
	`id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	`user`	TEXT NOT NULL UNIQUE,
	`pass`	TEXT NOT NULL
);