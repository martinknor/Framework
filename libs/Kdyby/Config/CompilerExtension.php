<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Config;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @method \Nette\DI\ContainerBuilder getContainerBuilder() getContainerBuilder()
 */
class CompilerExtension extends Nette\Config\CompilerExtension
{

	/**
	 * @param string $alias
	 * @param string $service
	 */
	public function addAlias($alias, $service)
	{
		$this->getContainerBuilder()
			->addDefinition($alias)->setFactory('@' . $service);
	}



	/**
	 * Intersects the keys of defaults and given options and returns only not NULL values.
	 *
	 * @param array $given	   Configurations options
	 * @param array $defaults  Defaults
	 * @param bool $keepNull
	 *
	 * @return array
	 */
	public static function getOptions(array $given, array $defaults, $keepNull = FALSE)
	{
		$options = array_intersect_key($given, $defaults) + $defaults;

		if ($keepNull === TRUE) {
			return $options;
		}

		return array_filter($options, function ($value) {
			return $value !== NULL;
		});
	}

}
