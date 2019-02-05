<?php

/**
 * Bedwars - EntityDamageListener.php
 * @author Fludixx
 * @license MIT
 */

declare(strict_types=1);

namespace Fludixx\Bedwars\event;

use Fludixx\Bedwars\Arena;
use Fludixx\Bedwars\Bedwars;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\item\Item;
use pocketmine\Player;

class EntityDamageListener implements Listener {

	public function onDamageByEntity(EntityDamageByEntityEvent $event) {
		$player = $event->getEntity();
		$damager = $event->getDamager();
		if($player instanceof Player and $damager instanceof Player) {
			if(Bedwars::$players[$player->getName()]->getPos() > 0 and Bedwars::$players[$player->getName()]->getPos() !== Bedwars::$players[$damager->getName()]->getPos()) {
				Bedwars::$players[$player->getName()]->setKnocker($damager->getName());
				if ($player->getHealth() <= $event->getFinalDamage()) {
					$event->setCancelled(TRUE);
					$levelname = $player->getLevel()->getFolderName();
					$pos = Bedwars::$arenas[$levelname]->getSpawns()[Bedwars::$players[$player->getName()]->getPos()];
					$player->teleport($pos);
					Bedwars::$arenas[$levelname]->broadcast("§e" . Bedwars::$players[$player->getName()]->getName() . "§f wurde von §e" .
						Bedwars::$players[$damager->getName()]->getName() . " §fgetötet!");
                    Bedwars::$statsSystem->set($player, 'deaths', (int)Bedwars::$statsSystem->get($player, 'deaths')+1);
                    Bedwars::$statsSystem->set($damager, 'deaths', (int)Bedwars::$statsSystem->get($damager, 'deaths')+1);
                    Bedwars::$players[$player->getName()]->die();
				}
				return;
			}
			$event->setCancelled(TRUE);
			if($damager->getInventory()->getItemInHand()->getId() === Item::IRON_SWORD) {
			    $mdamager = Bedwars::$players[$damager->getName()];
			    $mdamager->setVaule('hit', $player->getName());
			    $mplayer = Bedwars::$players[$player->getName()];
			    $mdamager->sendMsg("Du hast {$mplayer->getName()} herrausgefordet!");
			    $mplayer->sendMsg("{$mplayer->getName()} hat dich herrausgefordet!");
			    if($mplayer->getVaule('hit') === $damager->getName()) {
			        $mplayer->sendMsg("Suche Arena...");
			        $mdamager->sendMsg("Suche Arena...");
			        foreach (Bedwars::$arenas as $name => $class) {
			            if($class->isDuelMap() and count($class->getPlayers()) === 0 and $class->getState() === Arena::STATE_OPEN) {
			                $mplayer->sendMsg("Arena $name gefunden!");
			                $mdamager->sendMsg("Arena $name gefunden!");
                            $mplayer->setTeam(1);
			                $mdamager->setTeam(2);
                            $mplayer->saveTeleport($class->getLevel()->getSafeSpawn());
                            $mdamager->saveTeleport($class->getLevel()->getSafeSpawn());
                            $class->setCountdown(5);
                            $player->getInventory()->clearAll();
                            $damager->getInventory()->clearAll();
                            return;
                        }
                    }
			        $mplayer->sendMsg("Keine freie Arena gefunden!");
			        $mdamager->sendMsg("Keine freie Arena gefunden!");
			        return;
                }
            }
		}
	}

	public function onDamage(EntityDamageEvent $event) {
		$player = $event->getEntity();
		if($player instanceof Player) {
            if(Bedwars::$players[$player->getName()]->getPos() <= 0 and $event->getCause() !== EntityDamageEvent::CAUSE_VOID) {
                $event->setCancelled(TRUE);
                return;
            }
			if ($event->getCause() === EntityDamageEvent::CAUSE_FALL) {
				if($player->getHealth() <= $event->getFinalDamage()) {
					$event->setCancelled(TRUE);
					$levelname = $player->getLevel()->getFolderName();
					if(!is_null(Bedwars::$players[$player->getName()]->getKnocker())) {
						Bedwars::$arenas[$levelname]->broadcast("§e" . Bedwars::$players[$player->getName()]->getName() . "§f wurde von §e" .
							Bedwars::$players[$player->getName()]->getKnocker() . " §fgetötet!");
					} else {
						Bedwars::$arenas[$levelname]->broadcast("§e" . Bedwars::$players[$player->getName()]->getName() . "§f ist gestorben!");
					}
                    Bedwars::$statsSystem->set($player, 'deaths', (int)Bedwars::$statsSystem->get($player, 'deaths')+1);
					if(is_string(Bedwars::$players[$player->getName()]->getKnocker())) {
                        $killer = Bedwars::getInstance()->getServer()->getPlayerExact(Bedwars::$players[$player->getName()]->getKnocker());
                        if ($killer instanceof Player)
                            Bedwars::$statsSystem->set($killer, 'kills', (int)Bedwars::$statsSystem->get($killer, 'kills') + 1);
                    }
                    Bedwars::$players[$player->getName()]->die();
					return;
				}
				$event->setCancelled(FALSE);
			}
			else if($event->getCause() === EntityDamageEvent::CAUSE_VOID) {
				$event->setCancelled(TRUE);
				if(Bedwars::$players[$player->getName()]->getPos() > 0) {
					$levelname = $player->getLevel()->getFolderName();
					$pos = Bedwars::$arenas[$levelname]->getSpawns()[Bedwars::$players[$player->getName()]->getPos()];
					$player->teleport($pos);
					Bedwars::$arenas[$levelname]->broadcast("§e" . Bedwars::$players[$player->getName()]->getName() . "§f ist gestorben!");
					Bedwars::$players[$player->getName()]->die();
				} else {
					$player->teleport($player->getLevel()->getSafeSpawn());
				}
			}
		}
	}

	public function onHunger(PlayerExhaustEvent $event) {
	    $event->getPlayer()->setFood(20);
    }

}