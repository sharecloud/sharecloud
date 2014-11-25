DROP TABLE IF EXISTS `globalpreferences`;

ALTER TABLE `files`
	ADD COLUMN password TEXT,
	ADD COLUMN salt TEXT,
	ADD COLUMN permission INT;