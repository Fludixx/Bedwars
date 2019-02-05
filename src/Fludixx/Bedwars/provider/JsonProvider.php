<?php

/**
 * Bedwars - JsonProvider.php
 * @author Fludixx
 * @license MIT
 */

declare(strict_types=1);

namespace Fludixx\Bedwars\provider;

use Fludixx\Bedwars\Bedwars;
use pocketmine\Player;
use pocketmine\utils\Config;

class JsonProvider implements ProviderInterface {

	/** @var Config */
	protected $playerconfig;
	protected $arenaconfig;

	public function __construct()
	{
		$this->arenaconfig = new Config(Bedwars::getInstance()->getDataFolder()."/arenas.json", 1);
	}

	public function addArena(string $name, array $data) : void
	{
		$this->arenaconfig->set($name, $data);
		$this->arenaconfig->save();
	}

	public function getArena(string $name) : array
	{
		return $this->arenaconfig->get($name);
	}

	public function getArenas() : array
	{
		return $this->arenaconfig->getAll();
	}

}