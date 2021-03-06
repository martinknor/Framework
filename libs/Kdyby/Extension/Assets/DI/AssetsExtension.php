<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Assets\DI;

use Kdyby;
use Nette;
use Nette\DI\ContainerBuilder;
use Nette\Utils\Validators;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class AssetsExtension extends Nette\Config\CompilerExtension
{

	/** @var array */
	public $asseticDefaults = array(
		'publicDir' => '%wwwDir%',
		'prefix' => 'static',
		'debug' => '%kdyby.debug%'
	);



	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		$options = $this->getConfig($this->asseticDefaults);
		$builder->parameters += array(
			'assets' => array(
				'debug' => $debug = (bool)$builder->expand($options['debug']),
				'prefix' => $options['prefix'],
				'outputMask' => $options['prefix'] . '/*',
				'publicDir' => $options['publicDir']
			)
		);

		if ($debug) {
			$builder->addDefinition($this->prefix('assetStorage'))
				->setClass('Kdyby\Extension\Assets\Storage\CacheStorage', array('@kdyby.cacheStorage', '%tempDir%/cache'));

			$builder->addDefinition($this->prefix('route.asset'))
				->setClass('Kdyby\Extension\Assets\Router\AssetRoute', array('%assets.prefix%'))
				->setAutowired(FALSE);

			$builder->getDefinition('router')
				->addSetup('offsetSet', array(NULL, $this->prefix('@route.asset')));

		} else {
			$builder->addDefinition($this->prefix('assetStorage'))
				->setClass('Kdyby\Extension\Assets\Storage\PublicStorage', array('%assets.publicDir%'));
		}

		$builder->addDefinition($this->prefix('filterManager'))
			->setClass('Kdyby\Extension\Assets\FilterManager');

		$builder->addDefinition($this->prefix('assetManager'))
			->setClass('Kdyby\Extension\Assets\AssetManager');

		$factory = $builder->addDefinition($this->prefix('assetFactory'))
			->setClass('Kdyby\Extension\Assets\AssetFactory', array(1 => '%assets.publicDir%'))
			->addSetup('setAssetManager')
			->addSetup('setFilterManager')
			->addSetup('setDefaultOutput', array('%assets.outputMask%'))
			->addSetup('setDebug', array('%assets.debug%'));

		if (class_exists('Kdyby\Packages\PackageManager')) {
			$resolver = new Nette\DI\Statement('Kdyby\Extension\Assets\Resolver\PackagePathResolver');
			$factory->addSetup('addResolver', array($resolver));

			$builder->addDefinition($this->prefix('repository'))
				->setClass('Kdyby\Extension\Assets\Repository\KdybyPackagesRepository');

		} else {
			$builder->addDefinition($this->prefix('repository'))
				->setClass('Kdyby\Extension\Assets\Repository\PackagesRepository');
		}

		$builder->addDefinition($this->prefix('formulaeManager'))
			->setClass('Kdyby\Extension\Assets\FormulaeManager')
			->addSetup('setDebug', array('%assets.debug%'));

		// macros
		$macroFactory = 'Kdyby\Extension\Assets\Latte\AssetMacros::install($service->compiler)';
		$builder->getDefinition('nette.latte')
			->addSetup($macroFactory . '->setFactory(?)->setRepository(?)', array(
				$this->prefix('@assetFactory'),
				$this->prefix('@repository')
			));
	}

	// todo: register filters by tags

}
