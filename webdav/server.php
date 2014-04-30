<?php
class WebDAVServer {
	public static $server;
}

define('WEBDAV', true);

require_once 'lib/Sabre/autoload.php';
require_once '../system/init.php';

require_once 'adapter/DAVAuth.class.php';
require_once 'adapter/DAVFile.class.php';
require_once 'adapter/DAVFolder.class.php';

System::init();



use Sabre\DAV;

// Make sure there is a directory in your current directory named 'public'. We will be exposing that directory to WebDAV
$publicDir = new DAVFolder('/');


// The root directory is passed to Sabre\DAV\Server.
WebDAVServer::$server = new DAV\Server($publicDir);

// We're required to set the base uri, it is recommended to put your webdav server on a root of a domain
WebDAVServer::$server->setBaseUri(HOST_PATH."/webdav/");

$authBackend = new DAVAuth;
$auth = new \Sabre\DAV\Auth\Plugin($authBackend,'Login for Filehoster');
WebDAVServer::$server->addPlugin($auth);

$lockBackend = new DAV\Locks\Backend\File('locks.dat');
$lockPlugin = new DAV\Locks\Plugin($lockBackend);
WebDAVServer::$server->addPlugin($lockPlugin);
/*
// Also make sure there is a 'data' directory, writable by the server. This directory is used to store information about locks
$lockBackend = new DAV\Locks\Backend\File('data/locks.dat');
$lockPlugin = new DAV\Locks\Plugin($lockBackend);
WebDAVServer::$server->addPlugin($lockPlugin);
*/
// And off we go!
WebDAVServer::$server->exec();







?>