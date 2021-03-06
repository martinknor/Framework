<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Reflection;

$psycho = function () { $insider = function () { return 'doesnt\' work'; }; return $insider; };
$psycho();

$working = function () { return 'works'; };

return array($working, $psycho);
