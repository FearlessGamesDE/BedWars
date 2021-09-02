<?php

namespace BedWars\shop;

use pocketmine\block\tile\Sign;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;

class ShopManager
{
	/**
	 * @var Shop[]|Upgrader[]|Utility[]
	 */
	private static array $shops = [];
	/**
	 * @var int[]
	 */
	private static array $cooldown = [];

	/**
	 * @param Sign $sign
	 */
	public static function read(Sign $sign): void
	{
		self::$shops[$sign->getPosition()->getX() . ":" . $sign->getPosition()->getY() . ":" . $sign->getPosition()->getZ()] = match (strtoupper($sign->getText()->getLine(0))) {
			"SHOP" => new Shop($sign->getPosition(), $sign->getText()->getLine(1)),
			"UPGRADER" => new Upgrader($sign->getPosition(), $sign->getText()->getLine(1)),
			"UTILITY" => new Utility($sign->getPosition()),
		};
	}

	public static function load(): void
	{
		$world = Server::getInstance()->getWorldManager()->getDefaultWorld();
		foreach (self::$shops as $shop) {
			$shop->load($world);
		}
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 * @return Shop|Utility|Upgrader|null
	 */
	public static function get(int $x, int $y, int $z): Shop|Utility|Upgrader|null
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