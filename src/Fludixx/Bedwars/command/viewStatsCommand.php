<?php

declare(strict_types=1);

/**
 * Bedwars] - viewStatsCommand.php
 * @author Fludixx
 * @license MIT
 */

namespace Fludixx\Bedwars\command;

use Fludixx\Bedwars\Bedwars;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class viewStatsCommand extends Command {

    public function __construct()
    {
        parent::__construct("stats",
            "Sieh dir deine Stats an",
            "stats", []);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if($sender instanceof Player) {
            $stats = Bedwars::$statsSystem->getAll($sender);
            foreach ($stats as $name => $vaule) {
                if($vaule[0] !== '.') {
                    $sender->sendMessage("§a$name: §f$vaule");
                }
            }
        }
    }

}