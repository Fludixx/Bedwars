<?php

/**
 * Bedwars - Scoreboard.php
 * @author Fludixx
 * @license MIT
 */

declare(strict_types=1);

namespace Fludixx\Bedwars\utils;

/**
 * Class Scoreboard
 * @package Fludixx\Bedwars\utils
 * This class contains information about an scoreboard, BWPlayer::sendScoreboard will convert it into an Scoreboard
 * @see BWPlayer::sendScoreboard()
 */
class Scoreboard {

	/** @var string */
	public $objName;
	/** @var string */
	public $title;
	/** @var string[] */
	public $lines = [];

	/**
	 * Scoreboard constructor.
	 * @param string $objName
	 */
	public function __construct(string $objName)
	{
		$this->objName = $objName;
	}

	/**
	 * @param string $title
	 */
	public function setTitle(string $title) : void
	{
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getTitle() : string
	{
		return $this->title;
	}

	/**
	 * @param int    $n
	 * @param string $line
	 */
	public function setLine(int $n, string $line) : void
	{
		$this->lines[$n] = $line;
	}

	/**
	 * @param string $line
	 */
	public function addLine(string $line) : void
	{
		$this->lines[] = $line;
	}

}