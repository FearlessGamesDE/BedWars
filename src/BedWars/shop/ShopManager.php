<?php

namespace BedWars\shop;

use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\tile\Sign;

class ShopManager
{
	/**
	 * @var Shop[]|Upgrader[]|Utility[]
	 */
	private static $shops = [];
	/**
	 * @var int[]
	 */
	private static $cooldown = [];

	/**
	 * @param Sign $sign
	 */
	public static function read(Sign $sign): void
	{
		switch(strtoupper($sign->getLine(0))){
			case "SHOP":
				self::$shops[$sign->getX() . ":" . $sign->getY() . ":" . $sign->getZ()] = new Shop($sign, $sign->getLine(1));
				break;
			case "UPGRADER":
				self::$shops[$sign->getX() . ":" . $sign->getY() . ":" . $sign->getZ()] = new Upgrader($sign, $sign->getLine(1));
				break;
			case "UTILITY":
				self::$shops[$sign->getX() . ":" . $sign->getY() . ":" . $sign->getZ()] = new Utility($sign);
		}
	}

	public static function load(): void
	{
		$level = Server::getInstance()->getDefaultLevel();
		foreach (self::$shops as $shop) {
			$shop->load($level);
		}
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @return Shop|null
	 */
	public static function get(int $x, int $y, int $z)
	{
		return self::$shops[$x . ":" . $y . ":" . $z] ?? null;
	}

	/**
	 * @param Player $player
	 * @param Vector3 $position
	 * @return bool
	 */
	public static function use(Player $player, Vector3 $position): bool
	{
		if (($shop = self::get($position->getX(), $position->getY(), $position->getZ())) !== null) {
			if (isset(self::$cooldown[$player->getName()]) && self::$cooldown[$player->getName()] > microtime(true)) {
				return true;
			}
			self::$cooldown[$player->getName()] = microtime(true) + 0.5;
			$shop->use($player);
			return true;
		}
		return false;
	}

	public static function upgraderTick(Player $player, int $slot = -1): void
	{
		if($slot === -1){
			$slot = $player->getInventory()->getHeldItemIndex();
		}
		foreach (self::$shops as $shop) {
			if($shop instanceof Upgrader){
				$shop->tick($player, $slot);
			}
		}
	}
}