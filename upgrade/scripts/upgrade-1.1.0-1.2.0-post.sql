DROP TABLE `file_permissions`;

ALTER TABLE `files`
	CHANGE COLUMN `folder_ID` `folder_ID` INT NULL,
	DROP COLUMN `file_permissions_ID`,
	DROP PRIMARY KEY,
	ADD PRIMARY KEY (`_id`);
	
UPDATE `files`
	SET `folder_ID` = NULL
	WHERE `folder_ID` = 0;

UPDATE `folders`
	SET `parent` = NULL
	WHERE `parent` = 0;

ALTER TABLE `files` 
	ADD INDEX `files_folderID_idx` (`folder_ID` ASC),
	ADD INDEX `files_userID_idx` (`user_ID` ASC);

ALTER TABLE `files` 
	ADD CONSTRAINT `files_folderID`
		FOREIGN KEY (`folder_ID`)
		REFERENCES `folders` (`_id`)
		ON DELETE RESTRICT
		ON UPDATE NO ACTION,
	ADD CONSTRAINT `files_userID`
		FOREIGN KEY (`user_ID`)
		REFERENCES `users` (`_id`)
		ON DELETE RESTRICT
		ON UPDATE NO ACTION;

ALTER TABLE `folders` 
	DROP PRIMARY KEY,
	ADD PRIMARY KEY (`_id`),
	ADD INDEX `folders_userID_idx` (`user_ID` ASC);

ALTER TABLE `folders` 
	ADD CONSTRAINT `folders_userID`
		FOREIGN KEY (`user_ID`)
		REFERENCES `users` (`_id`)
		ON DELETE NO RESTRICT
		ON UPDATE NO ACTION;
		
ALTER TABLE `folders` 
ADD INDEX `folders_parentID_idx` (`parent` ASC);

ALTER TABLE `folders` 
ADD CONSTRAINT `folders_parentID`
	FOREIGN KEY (`parent`)
	REFERENCES `folders` (`_id`)
	ON DELETE RESTRICT
	ON UPDATE NO ACTION;

ALTER TABLE `lostpw` 
	DROP PRIMARY KEY,
	ADD PRIMARY KEY (`_id`),
	ADD INDEX `lostpw_userID_idx` (`user_ID` ASC);

ALTER TABLE `lostpw` 
	ADD CONSTRAINT `lostpw_userID`
		FOREIGN KEY (`user_ID`)
		REFERENCES `users` (`_id`)
		ON DELETE CASCADE
		ON UPDATE NO ACTION;
		
ALTER TABLE `sessions` 
	CHANGE COLUMN `uid` `uid` INT NULL DEFAULT NULL ,
	ADD INDEX `sessions_userID_idx` (`uid` ASC);

ALTER TABLE `sessions` 
	ADD CONSTRAINT `sessions_userID`
		FOREIGN KEY (`uid`)
		REFERENCES `users` (`_id`)
		ON DELETE CASCADE
		ON UPDATE NO ACTION;
