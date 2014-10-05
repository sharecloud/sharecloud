CREATE TABLE `users` (
  `_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `salt` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` text COLLATE utf8_unicode_ci,
  `admin` varchar(1) COLLATE utf8_unicode_ci DEFAULT '0',
  `quota` bigint(20) DEFAULT '0',
  `design` varchar(45) COLLATE utf8_unicode_ci DEFAULT 'dynamic',
  `last_login` int(11) DEFAULT NULL,
  `lang` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firstname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `folders` (
  `_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) DEFAULT NULL,
  `user_ID` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `folders_userID_idx` (`user_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `files` (
  `_id` int(11) NOT NULL AUTO_INCREMENT,
  `folder_ID` int(11) DEFAULT NULL,
  `user_ID` int(11) NOT NULL,
  `alias` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `filename` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mime` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `file` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hashes` text COLLATE utf8_unicode_ci,
  `downloads` int(11) DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `password` text COLLATE utf8_unicode_ci,
  `salt` text COLLATE utf8_unicode_ci,
  `permission` int(11) DEFAULT NULL,
  PRIMARY KEY (`_id`),
  KEY `files_folderID_idx` (`folder_ID`),
  KEY `files_userID_idx` (`user_ID`),
  CONSTRAINT `files_folderID` FOREIGN KEY (`folder_ID`) REFERENCES `folders` (`_id`) ON UPDATE NO ACTION,
  CONSTRAINT `files_userID` FOREIGN KEY (`user_ID`) REFERENCES `users` (`_id`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `log` (
  `_id` int(11) NOT NULL AUTO_INCREMENT,
  `errlevel` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `errmsg` text COLLATE utf8_unicode_ci NOT NULL,
  `file` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `line` smallint(6) NOT NULL,
  `sender` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `counter` int(11) unsigned DEFAULT NULL,
  `log` varchar(200) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `lostpw` (
  `_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_ID` int(11) NOT NULL,
  `hash` varchar(256) COLLATE utf8_unicode_ci DEFAULT NULL,
  `time` int(11) DEFAULT NULL,
  PRIMARY KEY (`_id`,`user_ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `sessions` (
  `sid` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `browser` text COLLATE utf8_unicode_ci,
  `lastactivity` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT '0',
  `data` text COLLATE utf8_unicode_ci,
  UNIQUE KEY `sid_UNIQUE` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
