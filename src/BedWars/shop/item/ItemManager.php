<?php

namespace BedWars\shop\item;

use BedWars\shop\Utility;
use Composer\Script\CommandEvent;

class ItemManager
{
	/**
	 * @var BedWarsItem[]
	 */
	private static $items = [];
	/**
	 * @var PermanentBedWarsItem[]
	 */
	private static $permanentItems = [];

	public static function load(): void
	{
		self::register(new Concrete());
		self::register(new Wood());
		self::register(new Gold());
		self::register(new Emerald());
		self::register(new Obsidian());
		self::register(new Pickaxe());
		self::register(new Sword());
		Utility::addItem(new TNT());
		Utility::addItem(new HomePowder());
		Utility::addItem(new GoldenApple());
		Utility::addItem(new Builder());
		Utility::addItem(new MLGWaterBucket());
		Utility::addItem(new Ladder());
		Utility::addItem(new Bow());
		Utility::addItem(new Arrow());
		Utility::addItem(new EnderPearl());
		Utility::addItem(new JumpBoostPotion());
		Utility::addItem(new SpeedPotion());
		Utility::addItem(new Compass());
	}

	/**
	 * @param BedWarsItem $item
	 */
	public static function register(BedWarsItem $item): void
	{
		if ($item instanceof TieredBedWarsItem) {
			if($item instanceof PermanentBedWarsItem){
				self::$permanentItems[] = $item;
			}
			foreach ($item->getTiers() as $tier) {
				self::$items[strtolower($tier->getName())] = $item;
			}
		} elseif ($item instanceof TeamItem) {
			foreach ($item->getTeams() as $team) {
				self::$items[strtolower($team->getName())] = $item;
			}
			self::$items[strtolower($item->getName())] = $item;
		} else {
			self::$items[strtolower($item->getName())] = $item;
			self::$items[strtolower($item->getRawItem()->getName())] = $item;
		}
	}

	/**
	 * @param string $item
	 * @return BedWarsItem|null
	 */
	public static function get(string $item): ?BedWarsItem
	{
		return self::$items[strtolower($item)] ?? null;
	}

	/**
	 * @return PermanentBedWarsItem[]
	 */
	public static function getPermanentItems(): array
	{
		return self::$permanentItems;
	}
}