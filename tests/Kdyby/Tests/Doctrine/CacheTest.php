<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Doctrine;

use Doctrine;
use Kdyby;
use Kdyby\Doctrine\Cache;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class CacheTest extends Kdyby\Tests\TestCase
{

	/** @var \Nette\Caching\Storages\FileStorage */
	private $storage;

	/** @var \Kdyby\Doctrine\Cache */
	private $cache;



	public function setUp()
	{
		$tempDir = $this->getContext()->expand('%tempDir%/cache');
		Kdyby\Tools\Filesystem::cleanDir($tempDir);

		$journal = $this->getContext()->nette->cacheJournal;
		$this->storage = new Nette\Caching\Storages\FileStorage($tempDir, $journal);
		$this->cache = new Cache($this->storage);
	}



	public function testSaving()
	{
		$id = '10#20#30';
		$data = "před pikolou, za pikolou!";
		$this->cache->save($id, $data);

		$this->assertTrue($this->cache->contains($id));
		$this->assertSame($data, $this->cache->fetch($id));
	}



	public function testSavingOfEntityThatChanges()
	{
		$className = $this->touchTempClass();
		$metadata = new Doctrine\ORM\Mapping\ClassMetadata($className);
		$metadata->name = $className;

		// save
		$this->cache->save('meta', $metadata);

		// contains
		$this->assertTrue($this->cache->contains('meta'));
		$this->assertEquals($className, $this->cache->fetch('meta')->name);

		// update file
		sleep(1);
		$this->touchTempClass($className);

		// contains no more
		$this->assertFalse($this->cache->contains('meta'));
	}

}
