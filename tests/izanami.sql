CREATE TABLE IF NOT EXISTS `users` (
	`id` int(10) NOT NULL auto_increment,
	`username` varchar(255),
	`password` varchar(255),
	`phone_id` numeric(9,2),
	PRIMARY KEY( `id` )
);

insert into users (username, password, phone_id) values ('Test', '123456', 1);
insert into users (username, password, phone_id) values ('Teste', '123456', 2);

CREATE TABLE IF NOT EXISTS `phones` (
	`id` int(10) NOT NULL auto_increment,
	`value` varchar(255),
	PRIMARY KEY( `id` )
);

insert into phones (value) values ('0999-9999');
insert into phones (value) values ('9999-9999');

CREATE TABLE IF NOT EXISTS `posts` (
	`id` int(10) NOT NULL auto_increment,
	`text` varchar(255),
	`user_id` numeric(9,2),
	PRIMARY KEY( `id` )
);

insert into posts (text, user_id) values ('sla', 1);

CREATE TABLE IF NOT EXISTS `tags` (
	`id` int(10) NOT NULL auto_increment,
	`value` varchar(255),
	PRIMARY KEY( `id` )
);

insert into tags (value) values('comida');
insert into tags (value) values('fofoca');

CREATE TABLE IF NOT EXISTS `taggables` (
	`id` int(10) NOT NULL auto_increment,
	`taggable_type` varchar(255),
	`taggable_id` numeric(9,2),
    `tag_id` numeric(10, 0),
	PRIMARY KEY( `id` )
);

insert into taggables (taggable_type, taggable_id, tag_id) values ('Test\\Models\\Post', 1, 2);
insert into taggables (taggable_type, taggable_id, tag_id) values ('Test\\Models\\Post', 1, 1);