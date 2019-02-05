<?php

declare(strict_types=1);

/**
 * Bedwars - ChatListener.php
 * @author Fludixx
 * @license MIT
 */

namespace Fludixx\Bedwars\event;

use Fludixx\Bedwars\Bedwars;
use Fludixx\Bedwars\utils\Utils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

class ChatListener implements Listener {

    public function onChat(PlayerChatEvent $event) {
        $player = Bedwars::$players[$event->getPlayer()->getName()];
        if($player->getPos() !== 0) {
            $arena = Bedwars::$arenas[$event->getPlayer()->getLevel()->getFolderName()];
            $messageArray = explode(" ", $event->getMessage());
            if(in_array("@all", $messageArray) or $adda = in_array("@a", $messageArray)) {
                if($adda) {
                    $index = array_search("@a", $messageArray);
                } else {
                    $index = array_search("@all", $messageArray);
                }
                unset($messageArray[$index]);
                $message = implode(" ", $messageArray);
                $arena->broadcast(Utils::ColorInt2Color(Utils::teamIntToColorInt($player->getPos()))."§7 - {$player->getName()}"."§f -> §aEveryone§f: ".$message);
            } else {
                foreach ($arena->getPlayers() as $splayer) {
                    $bwplayer = Bedwars::$players[$splayer->getName()];
                    if ($bwplayer->getPos() === $player->getPos()) {
                        $splayer->sendMessage("§e{$splayer->getName()} §f-> @" . Utils::ColorInt2Color(Utils::teamIntToColorInt($bwplayer->getPos())) . "§f: " . $event->getMessage());
                    }
                }
            }
            $event->setCancelled(TRUE);
        }
    }

}