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
				    $result = $gold >= 0 ? "§aWith gold" : "§cWithout gold!";
				    $arena->broadcast("Goldvoting has ended");
				    $arena->broadcast("Result: $result");
				    $arena->setHasGold($gold >= 0 ? TRUE : FALSE);
                }
				if($arena->getCountdown() === 0) {
					$arena->setState(Arena::STATE_INUSE);
					foreach ($arena->getPlayers() as $player) {
						$mplayer = Bedwars::$players[$player->getName()];
						if(!$mplayer->isSpectator()) {
                            $mplayer->setPos($mplayer->getTeam());
                            $mplayer->setTeam(0);
                            $player->getInventory()->clearAll();
                            $player->getArmorInventory()->clearAll();
                            $mplayer->sendMsg("The Game has started!");
                            $player->teleport($arena->getSpawns()[$mplayer->getPos()]);
                            $player->setDisplayName(Utils::ColorInt2Color(Utils::teamIntToColorInt($mplayer->getPos())) . " " . $player->getName());
                        }
					}
				}
			} else if($arena->getCountdown() <= 0) {
				$sb = new Scoreboard($name);
				$sb->setTitle("§e§l$name");
				$beds = [];
				foreach ($arena->getPlayers() as $player) {
					$mplayer = Bedwars::$players[$player->getName()];
                    if(!$mplayer->isSpectator()) {
                        if (!isset($beds[$mplayer->getPos()])) {
                            $beds[$mplayer->getPos()] = ['c' => 1, 's' => $arena->getBeds()[$mplayer->getPos()]];
                        } else {
                            $beds[$mplayer->getPos()]['c']++;
                        }
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
					if($mplayer->isSpectator()) {
					    $sb->addLine("§7SPECTATOR");
                    }
					$mplayer->sendScoreboard($sb);
				}
				if(count($teamsAlive) < 2) {
					foreach ($arena->getPlayers() as $player) {
						$mplayer = Bedwars::$players[$player->getName()];
                        if(!$mplayer->isSpectator()) {
                            $mplayer->getPlayer()->addTitle("§aYou won!");
                            $mplayer->setPos(0);
                            $player->getInventory()->setContents([
                                0 => Item::get(Item::IRON_SWORD)
                            ]);
                            $player->getArmorInventory()->clearAll();
                            $player->setDisplayName($player->getName());
                            $mplayer->saveTeleport(Bedwars::getInstance()->getServer()->getDefaultLevel()->getSafeSpawn());
                        } else {
                            Bedwars::getInstance()->getServer()->dispatchCommand($mplayer->getPlayer(), "leave");
                        }
					}
                    $arena->reset();
				}

				foreach ($arena->getLevel()->getTiles() as $tile) {
					if($tile instanceof Sign) {
						$pos = $tile->asVector3();
                        if(strtolower($tile->getLine(0))[0] === 'b') {
                            $arena->getLevel()->dropItem($pos->add(0.5, 2, 0.5), Item::get(Item::BRICK), new Vector3(0, 0, 0));
                        } else if(strtolower($tile->getLine(0))[0] === 'i' and time()%30 === 0) {
                            $arena->getLevel()->dropItem($pos->add(0.5, 2, 0.5), Item::get(Item::IRON_INGOT), new Vector3(0, 0, 0));
                        } else if(strtolower($tile->getLine(0))[0] === 'g' and $arena->getTimer()%60 === 0) {
                            $arena->getLevel()->dropItem($pos->add(0.5, 2, 0.5), Item::get(Item::GOLD_INGOT), new Vector3(0, 0, 0));
                        }
					}
				}

			} else {
				$sb = new Scoreboard($name);
				$sb->setTitle("§e§l$name");
				$sb->setLine(1, "Timer: §b".$arena->getCountdown());
				$sb->setLine(1, "Players: §a".(count($arena->getPlayers()))."§f / §c".($arena->getPlayersProTeam()+1));
				foreach ($arena->getPlayers() as $player) {
					$mplayer = Bedwars::$players[$player->getName()];
					$mplayer->sendScoreboard($sb);
				}
			}
			$arena->setTimer($arena->getTimer() + 1);
		}
	}

}