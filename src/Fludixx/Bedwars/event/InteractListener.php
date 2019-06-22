<?php

/**
 * Bedwars - InteractListener.php
 * @author Fludixx
 * @license MIT
 */

declare(strict_types=1);

namespace Fludixx\Bedwars\event;

use Fludixx\Bedwars\Arena;
use Fludixx\Bedwars\Bedwars;
use Fludixx\Bedwars\utils\Utils;
use muqsit\invmenu\InvMenu;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\tile\Sign;

class InteractListener implements Listener {

	public function onInteract(PlayerInteractEvent $event) {
		$mplayer = Bedwars::$players[$event->getPlayer()->getName()];
		$tile = $event->getBlock()->getLevel()->getTile($event->getBlock()->asVector3());
		if($mplayer->getPos() === 0 and $tile instanceof Sign and $tile->getLine(0) === Bedwars::NAME) {
			if($tile->getLine(3) === Bedwars::JOIN) {
				$mplayer->sendMsg("Teleporting...");
				$arena = Bedwars::$arenas[$tile->getLine(1)];
				if(count($arena->getPlayers()) < ($arena->getTeams() * $arena->getPlayersProTeam())) {
                    $randomteam = $mplayer->getRandomTeam($arena);
                    $mplayer->setTeam($randomteam);
                    $mplayer->saveTeleport($arena->getLevel()->getSafeSpawn());
                    $inv = $mplayer->getPlayer()->getInventory();
                    $inv->clearAll();
                    $inv->setItem(8, Item::get(Item::CHEST)->setCustomName("§eTeams"));
                    $inv->setItem(7, Item::get(Item::SLIME_BALL)->setCustomName("§cLeave"));
                    $inv->setItem(0, Item::get(Item::REDSTONE)->setCustomName("§6Goldvote"));
                    $arena->broadcast("{$mplayer->getName()} joined!");
                    return;
                }
			} else if($tile->getLine(3) === Bedwars::RUNNING or $tile->getLine(3) === Bedwars::FULL) {
                $arena = Bedwars::$arenas[$tile->getLine(1)];
                if($arena->getState() === Arena::STATE_INUSE) {
                    $mplayer->sendMsg("Teleporting...");
                    $inv = $mplayer->getPlayer()->getInventory();
                    $inv->setItem(0, Item::get(Item::SLIME_BALL)->setCustomName("§cLeave"));
                    $mplayer->setSpectator();
                    $mplayer->getPlayer()->setGamemode(3);
                    $mplayer->saveTeleport($arena->getLevel()->getSafeSpawn());
                }
            }
			$mplayer->sendMsg("You can't join this Round!");
		}
		switch ($event->getItem()->getCustomName()) {
			case "§eTeams":
				$menu = InvMenu::create(InvMenu::TYPE_CHEST);
				$menu->readonly();
				$menu->setName("Select your Team!");
				$minv = $menu->getInventory();
				$levelname = $mplayer->getPlayer()->getLevel()->getFolderName();
				$arena = Bedwars::$arenas[$levelname];
				$teams = [];
				for($i = 1;$i <= $arena->getTeams();$i++) {
				    $teams[$i] = [];
                }
				foreach ($arena->getLevel()->getPlayers() as $player) {
					$mplayer = Bedwars::$players[$player->getName()];
					$teams[$mplayer->getTeam()][] = $mplayer->getName();
				}
				foreach ($arena->getBeds() as $team => $bed) {
					$playerss = "";
					foreach ($teams[$team] as $name) {
						$playerss .= "\n§7 - §f$name";
					}
					$minv->addItem(Item::get(Item::WOOL, Utils::teamIntToColorInt($team), count($teams[$team])+1)->setCustomName
					(Utils::ColorInt2Color(Utils::teamIntToColorInt($team))."§7 - §f".count($teams[$team]).$playerss));
				}
				$menu->send($event->getPlayer());
				$menu->setListener([new ChestListener(), "onSelect"]);
				break;
            case "§6Goldvote":
                $form = new ModalFormRequestPacket();
                $form->formId = 156;
                $form->formData = json_encode([
                    'title' => "Play with or without Gold?",
                    'type' => "form",
                    'content' => "",
                    'buttons' => [
                        0 => ['text' => "§aWith Gold!"],
                        1 => ['text' => "§cWithout Gold!"]
                    ]
                ]);
                $event->getPlayer()->sendDataPacket($form);
                break;
            case "§cLeave":
                Bedwars::getInstance()->getServer()->dispatchCommand($event->getPlayer(), "leave");
                break;
		}
	}

	public function ondataPacketRecive(DataPacketReceiveEvent $event) {
	    $p = $event->getPacket();
	    if($p instanceof ModalFormResponsePacket and $p->formId === 156) {
	        $action = json_decode($p->formData);
	        $mplayer = Bedwars::$players[$event->getPlayer()->getName()];
            if(!is_null($action)) {
                if ($action === 1) {
                    $mplayer->setForGold(FALSE);
                    $mplayer->sendMsg("You wan't to play §cWITHOUT§f gold");
                } else {
                    $mplayer->setForGold(TRUE);
                    $mplayer->sendMsg("You wan't to play §aWITH§f gold");
                }
            }
        }
    }

}
