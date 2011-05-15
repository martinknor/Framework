<?php

// required constants
define('APP_DIR', __DIR__);
define('VENDORS_DIR', __DIR__ . '/../libs/vendors');

// Take care of autoloading
require_once VENDORS_DIR . '/autoload.php';
require_once APP_DIR . '/../Kdyby/loader.php';

// Setup Nette debuger
Nette\Diagnostics\Debugger::enable(Nette\Diagnostics\Debugger::PRODUCTION);
Nette\Diagnostics\Debugger::$logDirectory = APP_DIR;
Nette\Diagnostics\Debugger::$maxLen = 4096;

// Init Nette Framework robot loader
$loader = new Nette\Loaders\RobotLoader;
$loader->setCacheStorage(new Nette\Caching\Storages\MemoryStorage);
$loader->addDirectory(APP_DIR);
$loader->register();
