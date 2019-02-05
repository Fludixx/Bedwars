<?php

/**
 * Bedwars - ProviderInterface.php
 * @author Fludixx
 * @license MIT
 */

declare(strict_types=1);

namespace Fludixx\Bedwars\provider;

use pocketmine\Player;

interface ProviderInterface {

	/**
	 * @param string $name
	 * @param array  $data
	 */
	public function addArena(string $name, array $data) : void;

	/**
	 * @param string $name
	 * @return array
	 */
	public function getArena(string $name) : array;

	/**
	 * @return array
	 */
	public function getArenas() : array;

}