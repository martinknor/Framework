<?php

/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2010 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nellacms.com
 */
/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */

namespace Kdyby\Doctrine;

use Doctrine;
use Nette;
use Kdyby;



/**
 * Factories for doctrine
 *
 * @author	Patrik Votoček
 * @package	Nella\Doctrine
 */
class Factory extends Nette\Object
{

	/**
	 * @throws InvalidStateException
	 */
	final public function __construct()
	{
		throw new \InvalidStateException("Cannot instantiate static class " . get_called_class());
	}


	/**
	 * @return Kdyby\Doctrine\Cache
	 */
	protected  static function createCache()
	{
		$dataStorage = Nette\Environment::getApplication()->getService('Nette\\Caching\\ICacheStorage');
		return new Cache(new Nette\Caching\Cache($dataStorage, 'Doctrine'));
	}


	/**
	 * @return Doctrine\Common\EventManager
	 */
	protected static function createEventManager()
	{
		return new Doctrine\Common\EventManager;
	}


	/**
	 * @return Nella\Doctrine\Panel
	 */
	protected static function createLogger($serviceName = 'Doctrine\ORM\EntityManager')
	{
		return \Nella\Doctrine\Panel::createAndRegister($serviceName);
	}


	/**
	 * @param string
	 * @param string|bool
	 * @return Doctrine\DBAL\Event\Listeners\MysqlSessionInit
	 */
	protected static function createMysqlSessionListener($charset = 'utf8', $collation = FALSE)
	{
		return new Doctrine\DBAL\Event\Listeners\MysqlSessionInit($charset, $collation);

	}


	/**
	 * @return Doctrine\ORM\Configuration
	 */
	protected static function createConfiguration(array $database, array $options, $serviceName = 'Doctrine\ORM\EntityManager')
	{
		$config = new Doctrine\ORM\Configuration;

		// Cache
		$cache = static::createCache();
		$config->setMetadataCacheImpl($cache);
		$config->setQueryCacheImpl($cache);

		// Metadata
		$metadataDriver = self::newDefaultAnnotationDriver((array)$options['entityDir']);
		$config->setMetadataDriverImpl($metadataDriver);

		// Proxies
		$config->setProxyDir(Nette\Environment::getVariable('proxyDir', $options['proxyDir']));
		$config->setProxyNamespace('Kdyby\Models\Proxies');

		$config->setAutoGenerateProxyClasses(!Nette\Environment::isProduction());

		// Profiler
		if (isset($database['profiler']) && $database['profiler']) {
			$config->setSQLLogger(static::createLogger($serviceName));
		}

		return $config;
	}



    /**
     * Add a new default annotation driver with a correctly configured annotation reader.
     *
     * @param array $paths
     * @return Mapping\Driver\AnnotationDriver
     */
	public static function newDefaultAnnotationDriver($paths = array())
    {
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
		$reader->setAnnotationNamespaceAlias('Kdyby\Doctrine\Mapping\\', 'Kdyby');

        return new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($reader, (array)$paths);
    }



	/**
	 * @param string
	 * @return Doctrine\ORM\EntityManager
	 */
	public static function createEntityManager($options)
	{
		$context = Nette\Environment::getApplication()->getContext();
		$serviceName = 'Doctrine\ORM\EntityManager';
		$database = (array) Nette\Environment::getConfig('database');

		// Load config
		$config = self::createConfiguration($database, $options, $serviceName);

		$event = static::createEventManager();
		// Special event for MySQL
		if (isset($database['driver']) && $database['driver'] == "pdo_mysql" && isset($database['charset'])) {
			$event->addEventSubscriber(self::createMysqlSessionListener(
				$database['charset'],
				isset($database['collation']) ? $database['collation'] : FALSE
			));
		}

		// Entity manager
		return Doctrine\ORM\EntityManager::create($database, $config, $event);
	}
}