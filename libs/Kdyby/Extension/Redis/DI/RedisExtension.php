<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Redis\DI;

use Kdyby;
use Nette;
use Nette\Config\Configurator;
use Nette\Config\Compiler;
use Nette\DI\ContainerBuilder;
use Nette\DI\Statement;
use Nette\Utils\Validators;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class RedisExtension extends Nette\Config\CompilerExtension
{

	/**
	 * @var array
	 */
	public $defaults = array(
		'journal' => FALSE,
		'storage' => FALSE,
		'session' => FALSE,
		'host' => 'localhost',
		'port' => 6379,
		'timeout' => 10,
		'database' => 0
	);



	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$client = $builder->addDefinition($this->prefix('client'))
			->setClass('Kdyby\Extension\Redis\RedisClient', array(
				'host' => $config['host'],
				'port' => $config['port'],
				'database' => $config['database'],
				'timeout' => $config['timeout']
			));

		if ($builder->parameters['debugMode']) {
			$builder->addDefinition($this->prefix('panel'))
				->setFactory('Kdyby\Extension\Redis\Diagnostics\Panel::register');

			$client->addSetup('setPanel');
		}

		if ($config['journal']) {
			$builder->addDefinition($this->prefix('cacheJournal'))
				->setClass('Kdyby\Extension\Redis\RedisJournal');

			// overwrite
			$builder->removeDefinition('nette.cacheJournal');
			$builder->addDefinition('nette.cacheJournal')->setFactory($this->prefix('@cacheJournal'));
		}

		if ($config['storage']) {
			$builder->addDefinition($this->prefix('cacheStorage'))
				->setClass('Kdyby\Extension\Redis\RedisStorage');

			$builder->removeDefinition('cacheStorage');
			$builder->addDefinition('cacheStorage')->setFactory($this->prefix('@cacheStorage'));
		}

		if ($config['session']) {
			$builder->addDefinition($this->prefix('sessionHandler'))
				->setClass('Kdyby\Extension\Redis\RedisSessionHandler', array(1 => '%tempDir%'));

			$builder->getDefinition('session')
				->addSetup('setStorage', array($this->prefix('@sessionHandler')));
		}
	}



	/**
	 * @param \Nette\Config\Configurator $config
	 */
	public static function register(Configurator $config)
	{
		$config->onCompile[] = function (Configurator $config, Compiler $compiler) {
			$compiler->addExtension('redis', new RedisExtension());
		};
	}

}
