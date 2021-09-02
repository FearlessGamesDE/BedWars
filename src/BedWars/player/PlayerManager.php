<?php

namespace BedWars\player;

class PlayerManager
{
	/**
	 * @var BedWarsPlayer[]
	 */
	private static $players = [];

	/**
	 * @param BedWarsPlayer $player
	 */
	public static function add(BedWarsPlayer $player): void
	{
		self::$players[$player->getName()] = $player;
	}

	/**
	 * @param string $player
	 * @return BedWarsPlayer
	 */
	public static function get(string $player): BedWarsPlayer
	{
		if (!isset(self::$players[$player])) {
			self::add(new BedWarsPlayer($player));
		}
		return self::$players[$player];
	}

	public static function tick(): void
	{
		foreach (self::$players as $player){
			$player->tick();
		}
	}
}