<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

use Nette\Callback;
use Nette\Diagnostics\Debugger;
use Nette\Diagnostics\Helpers;



/**
 * Outputs the variable content to file
 *
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @param mixed $variable
 * @param int $maxDepth
 *
 * @return mixed
 */
function fd($variable, $maxDepth = 3) {
	$style = <<<CSS
	pre.nette-dump { color: #444; background: white; }
	pre.nette-dump .php-array, pre.nette-dump .php-object { color: #C22; }
	pre.nette-dump .php-string { color: #080; }
	pre.nette-dump .php-int, pre.nette-dump .php-float { color: #37D; }
	pre.nette-dump .php-null, pre.nette-dump .php-bool { color: black; }
	pre.nette-dump .php-visibility { font-size: 85%; color: #999; }
CSS;

	$originalDepth = Debugger::$maxDepth;
	Debugger::$maxDepth = $maxDepth;
	$dump = "<pre class=\"nette-dump\">" . Nette\Diagnostics\Helpers::htmlDump($variable) . "</pre>\n";
	Debugger::$maxDepth = $originalDepth;
	$dump .= "<style>" . $style . "</style>";
	$file = Debugger::$logDirectory . '/dump_' . substr(md5($dump), 0, 6) . '.html';

	file_put_contents($file, $dump);
	Kdyby\Diagnostics\ConsoleDebugger::openFile($file);

	return $variable;
}


/**
 * @param string $message
 */
function l($message) {
	$message = array_map(function ($message) {
		return !is_scalar($message) ? Nette\Utils\Json::encode($message) : $message;
	}, func_get_args());

	Nette\Diagnostics\Debugger::log(implode(', ', $message));
}


/**
 * Bar dump shortcut.
 * @see Nette\Diagnostics\Debugger::barDump
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @param mixed $var
 * @param string $title
 *
 * @return mixed
 */
function bd($var, $title = NULL) {
	if (Debugger::$productionMode) { return $var; }

	$trace = debug_backtrace();
	$traceTitle = (isset($trace[1]['class']) ? htmlspecialchars($trace[1]['class']) . "->" : NULL) .
		htmlspecialchars($trace[1]['function']) . '()' .
		':' . $trace[0]['line'];

	if (!is_scalar($title) && $title !== NULL) {
		foreach (func_get_args() as $arg) {
			Nette\Diagnostics\Debugger::barDump($arg, $traceTitle);
		}
		return $var;
	}

	return Nette\Diagnostics\Debugger::barDump($var, $title ?: $traceTitle);
}



/**
 * Deep dump shortcut.
 * @see Nette\Diagnostics\Debugger::dump
 *
 * @param mixed $var
 * @param integer $maxDepth
 *
 * @return mixed
 */
function dd($var, $maxDepth = 0) {
	if (is_string($var)) {
		$originalLen = Debugger::$maxLen;
		Debugger::$maxLen = $maxDepth;
		Debugger::dump($var);
		Debugger::$maxLen = $originalLen;

	} else {
		$originalDepth = Debugger::$maxDepth;
		Debugger::$maxDepth = $maxDepth;
		Debugger::dump($var);
		Debugger::$maxDepth = $originalDepth;
	}
	return $var;
}



/**
 * Function prints from where were method/function called
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @param int $level
 * @param bool $return
 * @param bool $fullTrace
 */
function wc($level = 1, $return = FALSE, $fullTrace = FALSE) {
	if (Debugger::$productionMode) { return; }

	$o = function ($t) { return (isset($t->class) ? htmlspecialchars($t->class) . "->" : NULL) . htmlspecialchars($t->function) . '()'; };
	$f = function ($t) { return isset($t->file) ? '(' . Helpers::editorLink($t->file, $t->line) . ')' : NULL; };

	$trace = debug_backtrace();
	$target = (object)$trace[$level];
	$caller = (object)$trace[$level+1];
	$message = NULL;

	if ($fullTrace) {
		array_shift($trace);
		foreach ($trace as $call) {
			$message .= $o((object)$call) . " \n";
		}

	} else {
		$message = $o($target) . " called from " . $o($caller) . $f($caller);
	}

	if ($return) {
		return strip_tags($message);
	}
	echo "<pre class='nette-dump'>" . nl2br($message) . "</pre>";
}



/**
 * @param string $message
 * @throws Kdyby\InvalidStateException
 */
function zmq_push($message)
{
	if (Debugger::$productionMode) { return; }
	static $publisher, $id;
	if ($publisher === NULL) {
		$context = new ZMQContext();
		$id = substr(md5(spl_object_hash($context) . microtime(true)), 0, 6);

		$publisher = $context->getSocket(ZMQ::SOCKET_PUSH);
		$publisher->connect("tcp://127.0.0.1:5556");
//		$publisher->send($id . ' connected');
//		register_shutdown_function(function () use ($publisher, $id) {
//			$publisher->send($id . ' disconnected');
//			sleep(0.1);
//		});
	}

	$message = array_map(function ($message) {
		return !is_scalar($message) ? Nette\Utils\Json::encode($message) : $message;
	}, func_get_args());
	$publisher->send($id . ' ' . implode(', ', $message));
}
