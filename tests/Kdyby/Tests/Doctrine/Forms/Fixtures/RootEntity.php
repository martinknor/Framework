<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Doctrine\Forms\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby;
use Nette;



/**
 * @ORM\Entity()
 * @author Filip Procházka <filip@prochazka.su>
 */
class RootEntity extends SharedFieldsEntity
{

	/**
	 * @ORM\Column(type="string")
	 */
	public $name;

	/**
	 * @ORM\ManyToOne(targetEntity="RelatedEntity", cascade={"persist"})
	 * @var \Kdyby\Tests\Doctrine\Forms\Fixtures\RelatedEntity
	 */
	public $daddy;

	/**
	 * @ORM\OneToMany(targetEntity="RelatedEntity", mappedBy="daddy", cascade={"persist"})
	 * @var \Kdyby\Tests\Doctrine\Forms\Fixtures\RelatedEntity[]
	 */
	public $children;

	/**
	 * @ORM\ManyToMany(targetEntity="RelatedEntity", inversedBy="buddies", cascade={"persist"})
	 * @var \Kdyby\Tests\Doctrine\Forms\Fixtures\RelatedEntity[]
	 */
	public $buddies;



	/**
	 * @param string $name
	 */
	public function __construct($name = NULL)
	{
		$this->name = $name;
		$this->children = new ArrayCollection();
		$this->buddies = new ArrayCollection();
	}

}
