<?php

namespace BedWars;

use platz1de\StatAPI\Module;
use platz1de\StatAPI\Stat;

class Stats
{
	/**
	 * @var Module
	 */
	private static $module;

	/**
	 * @var Stat
	 */
	public static $kills;
	/**
	 * @var Stat
	 */
	public static $deaths;

	/**
	 * @var Stat
	 */
	public static $finalKills;
	/**
	 * @var Stat
	 */
	public static $finalDeaths;

	/**
	 * @var Stat
	 */
	public static $beds;

	/**
	 * @var Stat
	 */
	public static $wins;
	/**
	 * @var Stat
	 */
	public static $losses;

	/**
	 * @var array
	 */
	public static $killCounter = [];

	public static function load(): void
	{
		self::$module = Module::get(BedWars::getId());

		self::$kills = Stat::get("kills", self::$module);
		self::$kills->setDisplayName("Kills");
		self::$kills->setType(Stat::TYPE_INCREASE);
		self::$kills->setDisplayType(Stat::DISPLAY_RAW);

		self::$deaths = Stat::get("deaths", self::$module);
		self::$deaths->setDisplayName("Deaths");
		self::$deaths->setType(Stat::TYPE_INCREASE);
		self::$deaths->setDisplayType(Stat::DISPLAY_RAW);

		self::$finalKills = Stat::get("final_kills", self::$module);
		self::$finalKills->setDisplayName("Final Kills");
		self::$finalKills->setType(Stat::TYPE_INCREASE);
		self::$finalKills->setDisplayType(Stat::DISPLAY_RAW);

		self::$finalDeaths = Stat::get("final_deaths", self::$module);
		self::$finalDeaths->setDisplayName("Final Deaths");
		self::$finalDeaths->setType(Stat::TYPE_INCREASE);
		self::$finalDeaths->setDisplayType(Stat::DISPLAY_RAW);

		self::$beds = Stat::get("beds", self::$module);
		self::$beds->setDisplayName("Broken Beds");
		self::$beds->setType(Stat::TYPE_INCREASE);
		self::$beds->setDisplayType(Stat::DISPLAY_RAW);

		self::$wins = Stat::get("wins", self::$module);
		self::$wins->setDisplayName("Wins");
		self::$wins->setType(Stat::TYPE_INCREASE);
		self::$wins->setDisplayType(Stat::DISPLAY_RAW);

		self::$losses = Stat::get("losses", self::$module);
		self::$losses->setDisplayName("Losses");
		self::$losses->setType(Stat::TYPE_INCREASE);
		self::$losses->setDisplayType(Stat::DISPLAY_RAW);
	}
}