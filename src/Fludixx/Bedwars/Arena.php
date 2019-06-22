<?php

/**
 * Bedwars - Arena.php
 * @author Fludixx
 * @license MIT
 */

declare(strict_types=1);

namespace Fludixx\Bedwars;

use Fludixx\Bedwars\utils\Utils;
use pocketmine\level\Level;
use pocketmine\level\sound\GhastShootSound;
use pocketmine\math\Vector3;
use pocketmine\Player;

/**
 * Class Arena
 * @package Fludixx\Bedwars
 * This Arena class is an Class that contains infos about an Arena, all Arenas should be converted to this class
 */
class Arena {

	const STATE_OPEN = 0;
	const STATE_INUSE = 1;

	/** @var string */
	private $name;
	/** @var int */
	private $playersProTeam;
	/** @var int  */
	private $teams;
	/** @var Level  */
	private $level;
	/** @var int */
	private $countdown;
	private $timer;
	/** @var int */
	private $state;
	/** @var Vector3[] */
	private $spawns = [];
	/** @var bool[] */
	private $beds = [];
	/** @var bool */
	private $hasGold = TRUE;

	/**
	 * Arena constructor.
	 * @param string $name
	 * @param int    $playersProTeam
	 * @param int    $teams
	 * @param Level  $level
	 * @param array  $spawns
	 */
	public function __construct(string $name, int $playersProTeam, int $teams, Level $level, array $spawns)
	{
		$this->name = $name;
		$this->playersProTeam = $playersProTeam;
		$this->teams = $teams;
		$this->level = $level;
		$this->countdown = 60;
		$this->timer = 0;
		$this->state = Arena::STATE_OPEN;
		foreach ($spawns as $id => $spawn) {
			$this->spawns[$id] = new Vector3($spawn['x'], $spawn['y'], $spawn['z']);
			$this->beds[$id] = TRUE;
		}
		$this->level->setAutoSave(FALSE);
	}

	/**
	 * @return string
	 */
	public function getName() : string
	{
		return $this->name;
	}

	/**
	 * @return int
	 */
	public function getPlayersProTeam() : int
	{
		return $this->playersProTeam;
	}

	/**
	 * @return int
	 */
	public function getTeams() : int
	{
		return $this->teams;
	}

	/**
	 * @return int
	 */
	public function getState() : int
	{
		return $this->state;
	}

	/**
	 * @return int
	 */
	public function getCountdown() : int
	{
		return $this->countdown;
	}

	/**
	 * @param int $countdown
	 */
	public function setCountdown(int $countdown) : void
	{
		$this->countdown = $countdown;
	}

	/**
	 * @param int $state
	 */
	public function setState(int $state) : void
	{
		$this->state = $state;
	}

	/**
	 * @param int $vaule
	 */
	public function CountDownSubtract(int $vaule = 1) {
		$this->countdown -= $vaule;
	}

	/**
	 * @return Level
	 */
	public function getLevel() : Level
	{
		return $this->level;
	}

	public function reset() {
		foreach ($this->getPlayers() as $player) {
			Bedwars::$players[$player->getName()]->saveTeleport(Bedwars::getInstance()->getServer()->getDefaultLevel()->getSafeSpawn());
		}
		// $this->level->unload();
		Bedwars::getInstance()->getServer()->unloadLevel($this->level);
		Bedwars::getInstance()->getServer()->loadLevel($this->name);
		$this->level = Bedwars::getInstance()->getServer()->getLevelByName($this->name);
		$this->countdown = 60;
		$this->state = Arena::STATE_OPEN;
		foreach ($this->spawns as $id => $spawn) {
			$this->beds[$id] = TRUE;
		}
		$this->level->setAutoSave(FALSE);
		$this->setState(Arena::STATE_OPEN);
		Bedwars::getInstance()->getServer()->broadcastMessage(Bedwars::PREFIX."Arena §b".$this->level->getFolderName()."§f is now free!");
	}

	/**
	 * @return Vector3[]
	 */
	public function getSpawns() : array
	{
		return $this->spawns;
	}

	public function broadcast(string $msg) {
		foreach ($this->level->getPlayers() as $player) {
			$player->sendMessage(Bedwars::PREFIX."$msg");
		}
	}

	public function destroyBed(int $id) {
		$this->beds[$id] = FALSE;
		$this->broadcast("The Bed of Team ".Utils::ColorInt2Color(Utils::teamIntToColorInt($id))." was destroyed!");
		$this->level->addSound(new GhastShootSound($this->spawns[$id]));
	}

	/**
	 * @return bool[]
	 */
	public function getBeds() : array
	{
		return $this->beds;
	}

	/**
	 * @return Player[]
	 */
	public function getPlayers() : array {
		return $this->level->getPlayers();
	}

    /**
     * @param bool $state
     */
	public function setHasGold(bool $state = TRUE) {
	    $this->hasGold = $state;
    }

    /**
     * @return bool
     */
    public function hasGold() : bool {
	    return $this->hasGold;
    }

    /**
     * @return bool
     */
    public function isDuelMap() : bool {
        return $this->playersProTeam === 1 and $this->teams === 2;
    }

    /**
     * @return int
     */
    public function getTimer(): int
    {
        return $this->timer;
    }

    /**
     * @param int $timer
     */
    public function setTimer(int $timer): void
    {
        $this->timer = $timer;
    }

}