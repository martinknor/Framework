<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\ORM;

use Doctrine;
use Doctrine\Common\Collections\Collection;
use Kdyby;
use Nette;
use Nette\ObjectMixin;



/**
 * @author Filip Procházka
 */
class EntityRepository extends Doctrine\ORM\EntityRepository
{

	/**
	 * @param object|array|Collection $entity
	 * @param boolean $validate
	 * @param boolean $withoutFlush
	 * @return object|array
	 */
	public function save($entity, $validate = TRUE, $withoutFlush = FALSE)
	{
		if ($entity instanceof Collection) {
			return $this->save($entity->toArray(), $validate, $withoutFlush);
		}

		if (is_array($entity)) {
			$repository = $this;
			$result = array_map(function ($entity) use ($repository, $validate) {
				return $repository->save($entity, $validate, TRUE);
			}, $entity);

			if ($withoutFlush === FALSE) {
				$this->_em->flush();
			}

			return $result;
		}

		if (!$entity instanceof $this->_entityName) {
			throw new Nette\InvalidArgumentException("Entity is not instanceof " . $this->_entityName . ', ' . get_class($entity) . ' given.');
		}

		$this->_em->persist($entity);
		if ($withoutFlush === FALSE) {
			$this->_em->flush();
		}

		return $entity;
	}



	/**
	 * @param object|array|Collection $entity
	 * @param boolean $withoutFlush
	 */
	public function delete($entity, $withoutFlush = FALSE)
	{
		if ($entity instanceof Collection) {
			return $this->delete($entity->toArray(), $withoutFlush);
		}

		if (is_array($entity)) {
			$repository = $this;
			array_map(function ($entity) use ($repository) {
				return $repository->delete($entity, TRUE);
			}, $entity);

			if ($withoutFlush === FALSE) {
				$this->_em->flush();
			}
			return;
		}

		if (!$entity instanceof $this->_entityName) {
			throw new Nette\InvalidArgumentException("Entity is not instanceof " . $this->_entityName . ', ' . get_class($entity) . ' given.');
		}

		$this->_em->remove($entity);
		if ($withoutFlush === FALSE) {
			$this->_em->flush();
		}
	}



	/**
	 * @param IQueryObject $queryObject
	 * @return integer
	 */
	public function count(IQueryObject $queryObject)
	{
		return $queryObject->count($this);
	}



	/**
	 * @param IQueryObject $queryObject
	 * @return array
	 */
	public function fetch(IQueryObject $queryObject)
	{
		return $queryObject->fetch($this);
	}



	/**
	 * @param IQueryObject $queryObject
	 * @return object
	 */
	public function fetchOne(IQueryObject $queryObject)
	{
		return $queryObject->fetchOne($this);
	}



	/**
	 * Create a new QueryBuilder instance that is prepopulated for this entity name
	 *
	 * @param string $alias
	 * @return QueryBuilder $qb
	 */
	public function createQueryBuilder($alias)
	{
		return $this->doCreateQueryBuilder()->select($alias)
			->from($this->_entityName, $alias);
	}



	/**
	 * @return QueryBuilder
	 */
	protected function doCreateQueryBuilder()
	{
		return new QueryBuilder($this->getEntityManager());
	}



	/**
	 * @param string $attribute
	 * @param mixed $value
	 * @throws QueryException
	 * @return int
	 */
	public function countByAttribute($attribute, $value)
	{
		$qb = $this->createQueryBuilder('e')
			->select('count(e) fullcount')
			->where('e.' . $attribute . ' = :value')
			->setParameter('value', $value);

		try {
			return (int)$qb->getQuery()->getSingleResult(Query::HYDRATE_SINGLE_SCALAR);

		} catch (Doctrine\ORM\ORMException $e) {
			throw new QueryException($e->getMessage(), $this->qb->getQuery(), $e);
		}
	}



	/**
	 * @param object $entity
	 * @return array
	 */
	public function getIdentifierValues($entity)
	{
		if (!is_object($entity)) {
			return $entity;
		}

		return $this->_em->getClassMetadata(get_class($entity))->getIdentifierValues($entity);
	}



	/********************* Nette\Object behaviour ****************d*g**/



	/**
	 * @return Nette\Reflection\ClassType
	 */
	public /**/static/**/ function getReflection()
	{
		return new Nette\Reflection\ClassType(/*5.2*$this*//**/get_called_class()/**/);
	}



	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}



	public function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}



	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}



	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}

}