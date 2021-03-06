<?php

/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * @package     VoidBuilder
 * @copyright   2019 Podvirnyy Nikita (KRypt0n_)
 * @license     GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.html>
 * @license     Enfesto Studio Group license <https://vk.com/topic-113350174_36400959>
 * @author      Podvirnyy Nikita (KRypt0n_)
 * 
 * Contacts:
 *
 * Email: <suimin.tu.mu.ga.mi@gmail.com>
 * VK:    vk.com/technomindlp
 *        vk.com/hphp_convertation
 * 
 */

namespace VoidBuilder;

const BUILDER_DIR = __DIR__;

$dir = 'qero-packages';

if (!file_exists ($dir))
	$dir = dirname (__DIR__, 2);

if (file_exists ($dir))
{
	$authors = array_slice (scandir ($dir), 2);
	
	foreach ($authors as $author)
		if (strtolower ($author) == 'krypt0nn')
		{
			$packages = array_slice (scandir ($dir .'/'. $author), 2);
			
			foreach ($packages as $package)
				if (strtolower ($package) == 'consoleargs')
				{
					require_once $dir .'/'. $author .'/'. $package .'/ConsoleArgs.php';
					
					break 2;
				}
		}
}

require 'php/Builder.php';
require 'php/Joiner.php';
