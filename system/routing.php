<?php
$router	= Router::getInstance();

//Home
$router->addRoute('/', 'HomeController', 'index');

//Auth
$router->addRoute('/auth/login', 'AuthController', 'login');
$router->addRoute('/auth/logout', 'AuthController', 'logout');
$router->addRoute('/auth/lostpw', 'AuthController', 'lostpw');
$router->addRoute('/auth/lostpw/:hash:', 'AuthController', 'lostpw_check');
$router->addRoute('/auth/file/:alias:', 'AuthController', 'authenticateFile');

//Download
$router->addRoute('/show/:alias:', 'DownloadController', 'show');
$router->addRoute('/download/:alias:/force', 'DownloadController', 'force');
$router->addRoute('/download/:alias:/raw', 'DownloadController', 'raw');
$router->addRoute('/download/:alias:/embed', 'DownloadController', 'embed');
$router->addRoute('/download/:alias:/resize', 'DownloadController', 'resize');
$router->addRoute('/download/folder/:id:', 'DownloadController', 'folder');

//Upload
$router->addRoute('/upload', 'UploadController', 'upload');

//Browser
$router->addRoute('/browser', 'BrowserController', 'index');
$router->addRoute('/browser/folders/add', 'BrowserController', 'addFolder');
$router->addRoute('/browser/folders/:id:/:path:', 'BrowserController', 'show');

//Profile
$router->addRoute('/profile', 'ProfileController', 'index');

//Log
$router->addRoute('/log', 'LogController', 'index');
$router->addRoute('/log/clear', 'LogController', 'clear');
$router->addRoute('/log/php', 'LogController', 'php');

// Users
$router->addRoute('/users', 'UsersController', 'index');
$router->addRoute('/users/add', 'UsersController', 'add');
$router->addRoute('/users/edit/:uid:', 'UsersController', 'edit');
$router->addRoute('/users/delete/:uid:', 'UsersController', 'delete');

// Admin panel
$router->addRoute('/admin', 'AdminController', 'index');
$router->addRoute('/admin/updateCheck', 'AdminController', 'updateCheck');

//Ajax
$router->addRoute('/api', 'ApiController', 'index');

$router->addRoute('/api/quota', 'ApiController', 'quota');

$router->addRoute('/api/folders/add', 'ApiController', 'addFolder');
$router->addRoute('/api/folders/folderSize', 'ApiController', 'getFolderSize');
$router->addRoute('/api/file', 'ApiController', 'getFile');
$router->addRoute('/api/file/permission', 'ApiController', 'permission');

$router->addRoute('/api/browser', 'ApiController', 'listDirectory');
$router->addRoute('/api/browser/delete', 'ApiController', 'delete');
$router->addRoute('/api/browser/move', 'ApiController', 'move');
$router->addRoute('/api/browser/rename', 'ApiController', 'rename');

$router->addRoute('/api/upload/:filename:', 'ApiController', 'upload');
$router->addRoute('/api/upload/:folder:/:filename:', 'ApiController', 'upload');
$router->addRoute('/api/remote', 'ApiController', 'remote');

$router->addRoute('/api/auth', 'ApiController', 'login');
$router->addRoute('/api/auth/logout', 'ApiController', 'logout');
$router->addRoute('/api/download', 'ApiController', 'download');
?>