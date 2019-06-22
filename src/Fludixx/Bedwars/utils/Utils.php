<?php

/**
 * Bedwars - Utils.php
 * @author Fludixx
 * @license MIT
 */

declare(strict_types=1);

namespace Fludixx\Bedwars\utils;

use pocketmine\utils\TextFormat as f;

class Utils {

	public static function teamIntToColorInt(int $int): int
	{
		if ($int == 1) {return 14;}
		if ($int == 2) {return 11;}
		if ($int == 3) {return 5;}
		if ($int == 4) {return 4;}
		if ($int == 5) {return 6;}
		if ($int == 6) {return 1;}
		if ($int == 7) {return 10;}
		if ($int == 8) {return 0;} else {return -1;}
	}

	public static function ColorIntToTeamInt(int $int): int
	{
		if ($int == 14) {return 1;}
		if ($int == 11) {return 2;}
		if ($int == 5) {return 3;}
		if ($int == 4) {return 4;}
		if ($int == 6) {return 5;}
		if ($int == 1) {return 6;}
		if ($int == 10) {return 7;}
		if ($int == 0) {return 8;} else {return -1;}
	}

	public static function ColorInt2Color(int $int): string
	{
		if ($int == 14) {return f::RED . "Red" . f::WHITE;}
		if ($int == 11) {return f::BLUE . "Blue" . f::WHITE;}
		if ($int == 5) {return f::GREEN . "Green" . f::WHITE;}
		if ($int == 4) {return f::YELLOW . "Yellow" . f::WHITE;}
		if ($int == 6) {return f::LIGHT_PURPLE . "Pink" . f::WHITE;}
		if ($int == 1) {return f::GOLD . "Orange" . f::WHITE;}
		if ($int == 10) {return f::DARK_PURPLE . "Purple" . f::WHITE;}
		if ($int == 0) {return f::WHITE . "White";} else {return "???";}
	}

	public static function colorIntToPicture(int $int) {#
        if($int == 14) {$url = "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/7/70/Red_Wool.png?version=c738bbbc3daae06f5a93837841b26d47";}
        if($int == 11) {$url = "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/c/ce/Blue_Wool.png?version=2a1b4b021e10cad13cd75fa1f3adea7a";}
        if($int == 5) {$url = "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/3/30/Lime_Wool.png?version=12f2e5f265ac507203b9eda28f478cd6";}
        if($int == 4) {$url = "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/1/18/Yellow_Wool.png?version=12297d82f713c2b9ab28d8945e1fb43c";}
        if($int == 6) {$url = "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/b/b6/Pink_Wool.png?version=db5318b984f7197f159d2311adf0833d";}
        if($int == 1) {$url = "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/9/9b/Orange_Wool.png?version=95d5c9e5851ed337cdc73d8fe8e5c29f";}
        if($int == 10) {$url = "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/8/83/Purple_Wool.png?version=f0f01c4199f03f3801f2044c8efffd0b";}
        if($int == 0) {$url = "https://d1u5p3l4wpay3k.cloudfront.net/minecraft_gamepedia/0/07/White_Wool.png?version=6ef682d562c93d8d22b311e3af579286";}
        else {$url = "null";}
        return $url;
    }
}