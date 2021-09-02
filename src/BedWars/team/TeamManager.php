<?php

namespace BedWars\team;

use BedWars\BedWars;
use BedWars\player\BedWarsPlayer;
use BedWars\player\PlayerManager;
use BedWars\utils\NumberGenerator;
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
	 * @var int
	 */
	public static $teamCount;
	/**
	 * @var int
	 */
	public static $teamSize;
	/**
	 * @var array
	 */
	private static $mapCache = [];

	public static function start(): void
	{
		$colors = TeamColor::getColors(self::$teamCount);
		foreach (self::$cache as $i => $team) {
			$players = [];
			foreach ($team as $player){
				PlayerManager::add($player = new BedWarsPlayer($player));
				$players[] = $player;
			}
			self::$teams[$colors[$i]] = new Team($players, $colors[$i], self::$mapCache["team " . $colors[$i]]);
		}
	}

	/**
	 * @param array $team
	 */
	public static function register(array $team): void
	{
		self::$cache[] = $team;
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