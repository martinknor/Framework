<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Browser;

use Kdyby;
use Nette;
use Symfony\Component\CssSelector\CssSelector;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class DomElement extends Nette\Object
{

	/** @var \DOMElement|\Kdyby\Extension\Browser\DomDocument */
	protected $element;



	/**
	 * @param \DOMNode $element
	 */
	public function __construct(\DOMNode $element)
	{
		$this->element = $element;
	}



	/**
	 * @return \DOMElement|\Kdyby\Extension\Browser\DomDocument
	 */
	public function getElement()
	{
		return $this->element;
	}



	/**
	 * @param string $selector
	 * @param \DOMNode $context
	 *
	 * @return \DOMNode[]|\DOMElement[]|NULL
	 */
	public function find($selector, $context = NULL)
	{
		$document = $this->element->ownerDocument ?: $this->element;
		return $document->find($selector, $context ?: $this->element);
	}



	/**
	 * @param string $selector
	 * @param \DOMNode $context
	 *
	 * @return \DOMNode|\DOMElement
	 */
	public function findOne($selector, $context = NULL)
	{
		$document = $this->element->ownerDocument ? : $this->element;
		return $document->findOne($selector, $context ?: $this->element);
	}



	/**
	 * @param string $text
	 * @param string $element
	 *
	 * @return array|NULL
	 */
	public function findText($text, $element = NULL)
	{
		$xpath = new \DOMXPath($this->element->ownerDocument);
		$element = $element ? CssSelector::toXPath($element) : NULL;
		return DomDocument::nodeListToArray($xpath->query($element . '[contains(., "' . $text . '")]', $this->element));
	}



	/**
	 * @param string $selector
	 * @param \Kdyby\Extension\Browser\ISnippetProcessor $processor
	 *
	 * @return mixed
	 */
	public function processSnippets($selector, ISnippetProcessor $processor)
	{
		$result = array();
		foreach ($this->find($selector) as $node) {
			$result[] = $processor->process($node);
		}

		return $result;
	}

}
