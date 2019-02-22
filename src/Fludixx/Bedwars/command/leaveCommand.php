<?php

/**
 * Bedwars - leaveCommandCommand.php
 * @author Fludixx
 * @license MIT
 */

declare(strict_types=1);

namespace Fludixx\Bedwars\command;

use Fludixx\Bedwars\Bedwars;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\Player;

class leaveCommand extends Command {

	public function __construct()
	{
		parent::__construct("leave",
			"Teleportiert dich zum Spawn",
			"/leave",  ["l"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if($sender instanceof Player) {
			$player = Bedwars::$players[$sender->getName()];
			$player->getPlayer()->setGamemode(0);
			$player->rmScoreboard($sender->getLevel()->getFolderName());
			$player->saveTeleport(Bedwars::getInstance()->getServer()->getDefaultLevel()->getSafeSpawn());
			$player->setPos(0);
			$player->setSpectator(FALSE);
            $sender->getInventory()->setContents([
                0 => Item::get(Item::IRON_SWORD)
            ]);
			$player->getPlayer()->getArmorInventory()->clearAll();
		}
	}

}