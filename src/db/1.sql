CREATE TABLE `liteauth_migrations` (
	`id`	INTEGER NOT NULL PRIMARY KEY UNIQUE,
	`run`	INTEGER default CURRENT_TIMESTAMP
);

CREATE TABLE `liteauth_users` (
	`id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	`user`	TEXT NOT NULL UNIQUE,
	`pass`	TEXT NOT NULL,
	'admin' integer NOT NULL default 0
);

CREATE TABLE `liteauth_authtokens` (
	`token`	TEXT NOT NULL,
	`user_id`	INTEGER NOT NULL,
	`created`	INTEGER NOT NULL
);