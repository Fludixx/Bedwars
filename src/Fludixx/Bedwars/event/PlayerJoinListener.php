<?php

/**
 * Bedwars - PlayerJoinListener.php
 * @author Fludixx
 * @license MIT
 */

declare(strict_types=1);

namespace Fludixx\Bedwars\event;

use Fludixx\Bedwars\Bedwars;
use Fludixx\Bedwars\BWPlayer;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\item\Item;

class PlayerJoinListener implements Listener {

	public function onPlayerJoin(PlayerLoginEvent $event) {
		$event->getPlayer()->teleport(Bedwars::getInstance()->getServer()->getDefaultLevel()->getSafeSpawn());
		Bedwars::$players[$event->getPlayer()->getName()] = new BWPlayer($event->getPlayer());
		if(!Bedwars::$statsSystem->isRegistered($event->getPlayer())) {
		    Bedwars::$statsSystem->register($event->getPlayer());
            Bedwars::$statsSystem->set($event->getPlayer(), 'kills', 0);
            Bedwars::$statsSystem->set($event->getPlayer(), 'deaths', 0);
            Bedwars::$statsSystem->set($event->getPlayer(), 'beds', 0);
            Bedwars::$statsSystem->set($event->getPlayer(), '.anfragen', TRUE);
        }
		$event->getPlayer()->getInventory()->setContents([
            0 => Item::get(Item::IRON_SWORD)
        ]);
	}

}