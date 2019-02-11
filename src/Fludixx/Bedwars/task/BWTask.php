<?php

/**
 * Bedwars - BWTask.php
 * @author Fludixx
 * @license MIT
 */

declare(strict_types=1);

namespace Fludixx\Bedwars\task;

use Fludixx\Bedwars\Arena;
use Fludixx\Bedwars\Bedwars;
use Fludixx\Bedwars\event\TakeItemListener;
use Fludixx\Bedwars\utils\Scoreboard;
use Fludixx\Bedwars\utils\Utils;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\tile\Sign;

class BWTask extends Task {

    /**
     * @param int $currentTick
     * This function manages all the Servers, without this task running no games will start & end
     */
	public function onRun(int $currentTick)
	{
		foreach (Bedwars::$arenas as $name => $arena) {
			if((count($arena->getPlayers()) >= (int)$arena->getPlayersProTeam()+1) and $arena->getCountdown() !== 0) {
				$arena->CountDownSubtract();
				$sb = new Scoreboard($name);
				$sb->setTitle("§e§l$name");
				$sb->addLine("Timer: §b".$arena->getCountdown());
				$color = count($arena->getPlayers()) < ((int)$arena->getPlayersProTeam() * (int)$arena->getTeams()) ?
					"§a" : "§c";
				$sb->addLine("$color".count($arena->getPlayers())."§7 / "."§c".(int)$arena->getPlayersProTeam() * (int)$arena->getTeams());
				foreach ($arena->getPlayers() as $player) {
					$mplayer = Bedwars::$players[$player->getName()];
					$mplayer->sendScoreboard($sb);
				}
				if($arena->getCountdown() === 5) {
				    $gold = 0;
				    foreach ($arena->getPlayers() as $playeraaa) {
				        $playerabw = Bedwars::$players[$playeraaa->getName()];
				        $playerabw->isForGold() ? $gold++ : $gold--;
                    }
				    $result = $gold >= 0 ? "§aMIT GOLD" : "§cOHNE GOLD";
				    $arena->broadcast("Goldvoting vorbei!");
				    $arena->broadcast("Ergebniss: $result");
				    $arena->setHasGold($gold >= 0 ? TRUE : FALSE);
                }
				if($arena->getCountdown() === 0) {
					$arena->setState(Arena::STATE_INUSE);
                    /**
                     * Setup the Fake spawners, this code will only spawn 1 material so phone or low spec players will have a smooth experience
                     * @see TakeItemListener.php
                     */
					foreach ($arena->getLevel()->getTiles() as $tile) {
					    if($tile instanceof Sign) {
                            $pos = $tile->asVector3();
                            $id = $pos->x.$pos->y.$pos->z;
                            $arena->drops_count[$id] = 0;
                            $pos = $tile->asVector3();
                            $pos->y = $pos->y+2;
                            $pos->x = $pos->x+0.5;
                            $pos->z = $pos->z+0.5;
                            switch (strtolower($tile->getLine(0))[0]) {
                                case 'b':
                                    $i = Item::get(Item::BRICK);
                                    $i->setCustomName("BRICK");
                                    $tile->getLevel()->dropItem($pos, $i, new Vector3(0, 0, 0));
                                    break;
                                case 'i':
                                    $i = Item::get(Item::IRON_INGOT);
                                    $i->setCustomName("IRON");
                                    $tile->getLevel()->dropItem($pos, $i, new Vector3(0, 0, 0));
                                    break;
                                case 'g':
                                    $i = Item::get(Item::GOLD_INGOT);
                                    $i->setCustomName("GOLD");
                                    $tile->getLevel()->dropItem($pos, $i, new Vector3(0, 0, 0));
                            }
                        }
                    }
					foreach ($arena->getPlayers() as $player) {
						$mplayer = Bedwars::$players[$player->getName()];
						$mplayer->setPos($mplayer->getTeam());
						$mplayer->setTeam(0);
						$player->getInventory()->clearAll();
						$player->getArmorInventory()->clearAll();
						$mplayer->sendMsg("Das Spiel ist gestartet!");
						$player->teleport($arena->getSpawns()[$mplayer->getPos()]);
						$player->setDisplayName(Utils::ColorInt2Color(Utils::teamIntToColorInt($mplayer->getPos()))." ".$player->getName());
					}
				}
			} else if($arena->getCountdown() <= 0) {
				$sb = new Scoreboard($name);
				$sb->setTitle("§e§l$name");
				$beds = [];
				foreach ($arena->getPlayers() as $player) {
					$mplayer = Bedwars::$players[$player->getName()];
					if(!isset($beds[$mplayer->getPos()])) {
						$beds[$mplayer->getPos()] = ['c' => 1, 's' => $arena->getBeds()[$mplayer->getPos()]];
					} else {
						$beds[$mplayer->getPos()]['c']++;
					}
				}
				$teamsAlive = [];
				foreach ($arena->getPlayers() as $player) {
					$mplayer = Bedwars::$players[$player->getName()];
					if($mplayer->getPos() > 0)
					    $teamsAlive[$mplayer->getPos()] = 0;
					$sb->setLine(1, "Team: ".Utils::ColorInt2Color(Utils::teamIntToColorInt($mplayer->getPos())));
					$sb->setLine(2, "\0");
					$i = 3;
					foreach ($beds as $team => $bed) {
						$bedState = $bed['s'] ? "§a✔" : "§c✘";
						$sb->setLine($i, Utils::ColorInt2Color(Utils::teamIntToColorInt($team)).": $bedState §f{$bed['c']}§7/§f{$arena->getPlayersProTeam()}");
						$i++;
					}
					$mplayer->sendScoreboard($sb);
				}
				if(count($teamsAlive) < 2) {
					foreach ($arena->getPlayers() as $player) {
						$mplayer = Bedwars::$players[$player->getName()];
						$mplayer->getPlayer()->addTitle("§aDu hast gewonnen!");
						$mplayer->setPos(0);
                        $player->getInventory()->setContents([
                            0 => Item::get(Item::IRON_SWORD)
                        ]);
						$player->getArmorInventory()->clearAll();
						$player->setDisplayName($player->getName());
						$mplayer->saveTeleport(Bedwars::getInstance()->getServer()->getDefaultLevel()->getSafeSpawn());
						$arena->reset();
					}
				}

				foreach ($arena->getLevel()->getTiles() as $tile) {
					if($tile instanceof Sign) {
						$pos = $tile->asVector3();
                        $id = $pos->x . $pos->y . $pos->z;
                        if(strtolower($tile->getLine(0))[0] === 'b') {
                            $arena->drops_count[$id]++;
                        } else if(strtolower($tile->getLine(0))[0] === 'i' and time()%30 === 0) {
                            $arena->drops_count[$id]++;
                        } else if(strtolower($tile->getLine(0))[0] === 'b' and time()%60 === 1) {
                            $arena->drops_count[$id]++;
                        }
					}
				}

			} else {
				$sb = new Scoreboard($name);
				$sb->setTitle("§e§l$name");
				$sb->setLine(1, "Timer: §b".$arena->getCountdown());
				$sb->setLine(1, "Spieler: §a".(count($arena->getPlayers()))."§f / §c".($arena->getPlayersProTeam()+1));
				foreach ($arena->getPlayers() as $player) {
					$mplayer = Bedwars::$players[$player->getName()];
					$mplayer->sendScoreboard($sb);
				}
			}
		}
	}

}