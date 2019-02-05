<?php

/**
 * Bedwars - BWPlayer.php
 * @author Fludixx
 * @license MIT
 */

declare(strict_types=1);

namespace Fludixx\Bedwars;

use Fludixx\Bedwars\task\removeLoadingScreen;
use Fludixx\Bedwars\utils\Scoreboard;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\ChangeDimensionPacket;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;

/**
 * Class BWPlayer
 * @package Fludixx\Bedwars
 * In the BWPlayer class info about an Player will be saved, for example teams
 */
class BWPlayer {

	/** @var Player */
	protected $player;
	/** @var int */
	protected $pos;
	/** @var int */
	protected $team;
	/** @var string */
	protected $knocker = NULL;
	/** @var bool */
	protected $fuerGold = TRUE;
	/** @var array */
	protected $extraData = [];
	/** @var bool */
	protected $canBuild = FALSE;

	/**
	 * BWPlayer constructor.
	 * @param Player $player
	 */
	public function __construct(Player $player)
	{
		$this->player = $player;
		$this->pos = 0;
		$this->team = 0;
	}

    /**
     * @return bool
     */
	public function canBuild() : bool {
	    return $this->canBuild;
    }

    /**
     * @param bool $canBuild
     */
    public function setCanBuild(bool $canBuild): void
    {
        $this->canBuild = $canBuild;
    }

	/**
	 * @return int
	 */
	public function getPos() : int
	{
		return $this->pos;
	}

	/**
	 * @return int
	 */
	public function getTeam() : int
	{
		return $this->team;
	}

	/**
	 * @param int $pos
	 */
	public function setPos(int $pos) : void
	{
		$this->pos = $pos;
	}

	/**
	 * @param int $team
	 */
	public function setTeam(int $team) : void
	{
		$this->team = $team;
	}

	/**
	 * @return Player
	 */
	public function getPlayer() : Player
	{
		return $this->player;
	}

	/**
	 * @param string $knocker
	 */
	public function setKnocker(string $knocker) : void
	{
		$this->knocker = $knocker;
	}

	/**
	 * @return string|null
	 */
	public function getKnocker()
	{
		return $this->knocker;
	}

	/**
	 * @return string
	 */
	public function getName() : string {
		// TODO Implement Nicks
		return $this->player->getName();
	}

	/**
	 * @param Position $position
	 */
	public function saveTeleport(Position $position) {
		$this->player->teleport(Bedwars::getInstance()->getServer()->getLevelByName("transfare")->getSafeSpawn());
		$pk = new ChangeDimensionPacket();
		$pk->position = Bedwars::getInstance()->getServer()->getLevelByName("transfare")->getSafeSpawn();
		$pk->dimension = DimensionIds::NETHER;
		$pk->respawn = true;
		$this->player->sendDataPacket($pk);
		Bedwars::getInstance()->getScheduler()->scheduleDelayedTask(new removeLoadingScreen($this->player, $position), 20);
	}

	public function sendMsg(string $msg) {
		$this->player->sendMessage(Bedwars::PREFIX."$msg");
	}

	/**
	 * @param Scoreboard $sb
	 */
	public function sendScoreboard(Scoreboard $sb) {
		$pk = new RemoveObjectivePacket();
		$pk->objectiveName = $sb->objName;
		$this->player->sendDataPacket($pk);
		$pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = "sidebar";
		$pk->objectiveName = $sb->objName;
		$pk->displayName = $sb->title;
		$pk->criteriaName = "dummy";
		$pk->sortOrder = 0;
		$this->player->sendDataPacket($pk);
		foreach ($sb->lines as $num => $line) {
			if($line === "")
				$line = str_repeat("\0", $num);
			$entry = new ScorePacketEntry();
			$entry->objectiveName = $sb->objName;
			$entry->type = 3;
			$entry->customName = " $line ";
			$entry->score = $num;
			$entry->scoreboardId = $num;
			$pk = new SetScorePacket();
			$pk->type = 0;
			$pk->entries[$num] = $entry;
			$this->player->sendDataPacket($pk);
		}
	}

	public function die() {
		$levelname = $this->player->getLevel()->getFolderName();
		if(Bedwars::$arenas[$levelname]->getBeds()[$this->pos]) {
			$this->player->setHealth(20.0);
			$this->player->setFood(20.0);
			$this->player->getInventory()->clearAll();
			$this->player->getArmorInventory()->clearAll();
			$pos = Bedwars::$arenas[$levelname]->getSpawns()[Bedwars::$players[$this->player->getName()]->getPos()];
			$this->player->teleport($pos);
		} else {
		    $this->setPos(0);
		    $this->rmScoreboard($this->player->getLevel()->getFolderName());
            $this->player->getInventory()->setContents([
                0 => Item::get(Item::IRON_SWORD)
            ]);
			$this->player->getArmorInventory()->clearAll();
			$this->saveTeleport(Bedwars::getInstance()->getServer()->getDefaultLevel()->getSafeSpawn());
		}
	}

    /**
     * @param string $objname
     */
	public function rmScoreboard(string $objname) {
		$pk = new RemoveObjectivePacket();
		$pk->objectiveName = $objname;
		$this->player->sendDataPacket($pk);
	}

    /**
     * @return bool
     */
	public function isForGold() : bool {
	    return $this->fuerGold;
    }

    /**
     * @param bool $state
     */
    public function setForGold(bool $state = TRUE) {
	    $this->fuerGold = $state;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setVaule($key, $value) {
	    if(!isset($this->extraData[$key]))
	        $this->extraData[$key] = 0;
	    $this->extraData[$key] = $value;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getVaule($key) {
	    return isset($this->extraData[$key]) ? $this->extraData[$key] : 0;
    }

}