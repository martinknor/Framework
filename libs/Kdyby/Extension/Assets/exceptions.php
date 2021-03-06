<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Assets;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
interface Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class InvalidArgumentException extends \InvalidArgumentException implements Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class MissingServiceException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class AssetNotFoundException extends \OutOfRangeException implements Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class FileNotFoundException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class NotSupportedException extends \LogicException implements Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class UnexpectedValueException extends \UnexpectedValueException implements Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class InvalidDefinitionFileException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class LatteCompileException extends \Nette\Latte\CompileException implements Exception
{

	/**
	 * @param string $message
	 * @param \Exception|null $previous
	 */
	public function __construct($message = NULL, \Exception $previous = NULL)
	{
		\Exception::__construct($previous ? $previous->getMessage() : $message, 0, $previous);
	}

}
