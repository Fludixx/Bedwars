<?php

/**
 * Bedwars - removeLoadingScreen.php
 * @author Fludixx
 * @license MIT
 */

declare(strict_types=1);

namespace Fludixx\Bedwars\task;

use Fludixx\Bedwars\Bedwars;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\ChangeDimensionPacket;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\Player;
use pocketmine\scheduler\Task;

/**
 * Class removeLoadingScreen
 * @package Fludixx\Bedwars\task
 * This class is required to remove the "Building Terrain..." screen
 * @see BWPlayer::saveTeleport()
 */
class removeLoadingScreen extends Task {

	/** @var Player  */
	protected $player;
	/** @var Position  */
	protected $pos;

	/**
	 * removeLoadingScreen constructor.
	 * @param Player   $player
	 * @param Position|false $pos
	 */
	public function __construct(Player $player, $pos = false)
	{
		$this->player = $player;
		$this->pos = $pos;
	}

	public function onRun(int $currentTick)
	{
		$pk = new PlayStatusPacket();
		$pk->status = 3;
		$this->player->sendDataPacket($pk);
		if($this->pos instanceof Position) {
			$spawn = $this->pos;
			$this->player->teleport($spawn);
			$pk = new ChangeDimensionPacket();
			$pk->position = $this->pos;
			$pk->dimension = DimensionIds::OVERWORLD;
			$pk->respawn = true;
			$this->player->sendDataPacket($pk);
			Bedwars::getInstance()->getScheduler()->scheduleDelayedTask(new removeLoadingScreen($this->player), 40);
		}
	}

}