<?php

/**
 * Bedwars - PlayerId.php
 * @author Fludixx
 * @license MIT
 */

declare(strict_types=1);

namespace Fludixx\Bedwars\utils;

class PlayerId {

	// Compression Codeblock
	const id_cb = [
		'61' => '!',		'62' => '"',
		'63' => '§',		'64' => '$',
		'65' => '%',		'66' => '&',
		'67' => '/',		'68' => '(',
		'69' => ')',		'6a' => '=',
		'6b' => '?',		'6c' => '{',
		'6d' => '[',		'6e' => ']',
		'6f' => '}',		'70' => '\\',
		'71' => '+',		'72' => '*',
		'73' => '\'',		'74' => '#',
		'75' => ',',		'76' => '.',
		'77' => '-',		'78' => '_',
		'79' => ';',		'7a' => ':'
	];
	// Reversed Compression Codeblock
	const id_rcb = [
		'33' => '61',		'34' => '62',
		'361' => '63',		'36' => '64',
		'37' => '65',		'38' => '66',
		'47' => '67',		'40' => '68',
		'41' => '69',		'61' => '6a',
		'63' => '6b',		'123' => '6c',
		'91' => '6d',		'93' => '6e',
		'125' => '6f',		'92' => '70',
		'43' => '71',		'42' => '72',
		'39' => '73',		'35' => '74',
		'44' => '75',		'46' => '76',
		'45' => '77',		'95' => '78',
		'59' => '79',		'58' => '7a',
	];

	/** @var string */
	protected $playername;

	public function __construct(string $playername)
	{
		$this->playername = strtolower($playername);
	}

	public function __toString()
	{
		$string = $this->playername;
		$charArray = str_split($this->playername);
		$hex = "";
		for ($n = 0; isset($charArray[$n]); $n++) {
			$ord = ord($string[$n]);
			$hexCode = dechex($ord);
			$hex .= substr('0'.$hexCode, -2);
		}
		while (strlen($hex) < 30) {
			$hex.="0";
		}
		return $hex;
	}

	public static function stringToId(string $string) {
		$charArray = str_split($string);
		$hex = "";
		for ($n = 0; isset($charArray[$n]); $n++) {
			$ord = ord($string[$n]);
			$hexCode = dechex($ord);
			$hex .= substr('0'.$hexCode, -2);
		}
		while (strlen($hex) < 30) {
			$hex.="0";
		}
		return $hex;
	}

	public static function idToString(string $id) {
		$string = $id;
		$charArray = str_split($string, 1);
		$hex = "";
		for ($i=0; isset($charArray[$i+1]); $i+=2){
			$hex .= chr(hexdec($string[$i].$string[$i+1]));
		}
		return $hex;
	}

	public static function compress(string $string) {
		$compressed = "";
		foreach (str_split($string, 2) as $pack) {
			$compressed .= isset(PlayerId::id_cb[$pack]) ?
				PlayerId::id_cb[$pack] : $pack;
		}
		$compressed = str_replace("20", "``", $compressed); // When Players have a 0 in their hexed name the value gets unreadable
		$compressed = str_replace("0", "", $compressed);
		$compressed = str_replace("``", "20", $compressed); // replacing the __ with 20 again
		return $compressed;
	}

	public static function uncompress(string $string) {
		$uncompressed = "";
		foreach (str_split($string, 1) as $char) {
			$uncompressed .= isset(PlayerId::id_rcb[PlayerId::getCharId($char)]) ?
				PlayerId::id_rcb[PlayerId::getCharId($char)] : $char;
		}
		return $uncompressed;
	}

	public static function getCharId(string $char) {
		$id = 0;
		for ($pos = 0; $pos < strlen($char); $pos++) {
			$byte = substr($char, $pos);
			$id += ord($byte);
		}
		return $id;
	}
}