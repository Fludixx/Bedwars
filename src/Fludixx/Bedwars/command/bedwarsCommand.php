<?php

/**
 * @author Fludixx
 * @version 2.1
 * @license MIT
 */

declare(strict_types=1);

namespace Fludixx\Bedwars\command;

use Fludixx\Bedwars\Arena;
use Fludixx\Bedwars\Bedwars;
use Fludixx\Bedwars\utils\Utils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\Item;
use pocketmine\Player;

class bedwarsCommand extends Command {

	public $bedwars;

	public function __construct()
	{
		parent::__construct("bw",
			"Bedwars Command",
			"/bw [ARENANAME] [MODE 8*1...]", ["bedwars"]);
		$this->bedwars = Bedwars::getInstance();
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if(($sender->hasPermission("bw.admin") or $sender->isOp()) and $sender instanceof Player) {
			$player = Bedwars::$players[$sender->getName()];
			if(!isset($args[0]) or !isset($args[1]) or $args[0] == "help") {
				$sender->sendMessage(Bedwars::PREFIX."/bw [ARENANAME] [MODE 8*1...]");
				return TRUE;
			} else {
				$levelname = $args[0];
				$mode = $args[1];
				$mode = str_replace("x", "*", $mode);
				$maxplayers = eval("return ".$args[1].";");
				if((int)$args[1][0] > 8 or (int)$args[1][2] > 8) {
					$player->sendMsg("Teams oder Spieler sind über 8! (MAX: 8*8)");
					return FALSE;
				} else {
					if($this->bedwars->getServer()->loadLevel($levelname)) {
						$level = $this->bedwars->getServer()->getLevelByName($levelname);
						$arenadata = [
							'teams' => $mode[0],
							'ppt' => $mode[2],
							'mapname' => $levelname,
							'maxplayers' => $maxplayers,
							'spawns' => []
						];
						Bedwars::$provider->addArena($levelname, $arenadata);
						$player->setPos(-1);
						$sender->getInventory()->setItem(0, Item::get(35, Utils::teamIntToColorInt(1)));
						$sender->teleport($level->getSafeSpawn());
						$player->sendMsg("Bitte berühre die Spawns der Spieler!");
						$player->sendMsg("verwende /leave um zum Spawn zu kommen");
						Bedwars::$arenas[$player->getPlayer()->getLevel()->getFolderName()] =
							new Arena($player->getPlayer()->getLevel()->getFolderName(),
								(int)$mode[2], (int)$mode[0], $sender->getLevel(), []);
						return TRUE;
					} else {
						$player->sendMsg("Fehler: 1 Parameter muss ein Levelname sein!");
						return FALSE;
					}
				}
			}
		} else {
			$sender->sendMessage(Bedwars::PREFIX."/bw kann nur verwendet werden wenn du über die bw.admin berechtigung verfügst!");
			return FALSE;
		}
	}
}