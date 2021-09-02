<?php

namespace BedWars\team;

use BedWars\BedWars;
use BedWars\player\BedWarsPlayer;
use BedWars\player\PlayerManager;
use BedWars\utils\TeamColor;

class TeamManager
{
	/**
	 * @var Team[]
	 */
	private static $teams = [];
	/**
	 * @var array
	 */
	private static $cache = [];
	/**
	 * @var array
	 */
	private static $mapCache = [];

	public static function start(): void
	{
		$colors = TeamColor::getColors(BedWars::getTeamCount());
		foreach (BedWars::getTeams() as $i => $team) {
			$players = [];
			foreach ($team as $player) {
				PlayerManager::add($player = new BedWarsPlayer($player));
				$players[] = $player;
			}
			self::$teams[$colors[$i]] = new Team($players, $colors[$i], self::$mapCache["team " . $colors[$i]]);
		}
	}

	/**
	 * @param array $cache
	 */
	public static function cache(array $cache): void
	{
		self::$mapCache = array_merge_recursive(self::$mapCache, $cache);
	}

	/**
	 * @param int $color
	 * @return Team
	 */
	public static function get(int $color): Team
	{
		return self::$teams[$color];
	}

	/**
	 * @return Team[]
	 */
	public static function getTeams(): array
	{
		return self::$teams;
	}
}