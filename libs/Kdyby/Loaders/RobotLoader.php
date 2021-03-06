<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Loaders;

use Kdyby;
use Kdyby\Iterators\TypeIterator;
use Nette;
use Nette\Caching\Cache;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class RobotLoader extends Nette\Loaders\RobotLoader
{

	const CACHE_NAMESPACE = 'Nette.RobotLoader';

	/**
	 * @var bool
	 */
	public $autoRebuild = FALSE;



	/**
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setCacheStorage(new Nette\Caching\Storages\MemoryStorage());
	}



	/**
	 * @return \DateTime
	 */
	public function getIndexCreateTime()
	{
		$key = is_scalar($key = $this->getKey()) ? $key : serialize($key);
		$key = self::CACHE_NAMESPACE . Cache::NAMESPACE_SEPARATOR . md5($key);
		return $this->getCacheStorage()->getCreateTime($key);
	}



	/**
	 * @return \Kdyby\Iterators\TypeIterator
	 */
	public function createIndexFilter()
	{
		$classes = new \ArrayIterator(array_keys($this->getIndexedClasses()));
		return new TypeIterator($classes);
	}

}
