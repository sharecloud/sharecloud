<?php

// Files
class FileNotFoundException extends Exception { }
class InvalidFilenameException extends Exception { }
class InvalidPasswordException extends Exception { }
class NotAuthorisedException extends Exception { }
class QuotaExceededException extends Exception { }

//Upload Files
class UploadException extends Exception {
	public $message;
	public function __construct($message, $code = 0, $previous = NULL) {
		parent::__construct(System::getLanguage()->_($message), $code, $previous);
	}
}
class InvalidFilesizeException extends Exception { }

// Folders
class FolderNotFoundException extends Exception { }
class FolderAlreadyExistsException extends Exception { }
class InvalidFolderNameException extends Exception { }

// L10N
class LanguageNotFoundException extends Exception { }
class InvalidLanguageFileException extends Exception { }

// User
class UserNotFoundException extends Exception { }

// Util
class DirectoryNotFoundException extends Exception { }

// ImageResizeHandler
class UnsupportedImageFormatException extends Exception { }

// FilesystemException
class FilesystemException extends Exception { }

// Mail
class MailFailureException extends Exception { }

// Lost PW
class HashNotFoundException extends Exception { }

class RequestException extends Exception { }
?>