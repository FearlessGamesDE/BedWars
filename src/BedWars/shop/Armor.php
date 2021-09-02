<?php

namespace BedWars\shop;

use BedWars\player\PlayerManager;
use BedWars\utils\TeamColor;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\Player;

class Armor
{
	/**
	 * @var int[]
	 */
	private static $data;

	/**
	 * @return \pocketmine\item\Armor[][]
	 */
	public static function getTiers(): array
	{
		return [
			[
				ItemFactory::get(ItemIds::LEATHER_CAP),
				ItemFactory::get(ItemIds::LEATHER_CHESTPLATE),
				ItemFactory::get(ItemIds::LEATHER_PANTS),
				ItemFactory::get(ItemIds::LEATHER_BOOTS)
			],
			[
				ItemFactory::get(ItemIds::LEATHER_CAP),
				ItemFactory::get(ItemIds::LEATHER_CHESTPLATE),
				ItemFactory::get(ItemIds::CHAIN_LEGGINGS),
				ItemFactory::get(ItemIds::LEATHER_BOOTS)
			],
			[
				ItemFactory::get(ItemIds::IRON_HELMET),
				ItemFactory::get(ItemIds::LEATHER_CHESTPLATE),
				ItemFactory::get(ItemIds::CHAIN_LEGGINGS),
				ItemFactory::get(ItemIds::IRON_BOOTS)
			],
			[
				ItemFactory::get(ItemIds::IRON_HELMET),
				ItemFactory::get(ItemIds::LEATHER_CHESTPLATE),
				ItemFactory::get(ItemIds::DIAMOND_LEGGINGS),
				ItemFactory::get(ItemIds::IRON_BOOTS)
			],
			[
				ItemFactory::get(ItemIds::DIAMOND_HELMET),
				ItemFactory::get(ItemIds::LEATHER_CHESTPLATE),
				ItemFactory::get(ItemIds::DIAMOND_LEGGINGS),
				ItemFactory::get(ItemIds::DIAMOND_BOOTS)
			]
		];
	}

	/**
	 * @return Item[]
	 */
	public static function getCosts(): array
	{
		return [
			ItemFactory::get(ItemIds::AIR), //7 Bars
			ItemFactory::get(ItemIds::EMERALD), //9 Bars (+2)
			ItemFactory::get(ItemIds::EMERALD, 0, 2), //11 Bars (+2)
			ItemFactory::get(ItemIds::EMERALD, 0, 3), //13 Bars (+2)
			ItemFactory::get(ItemIds::EMERALD, 0, 4), //15 Bars (+2)
		];
	}

	/**
	 * @param int $tier
	 * @param string $player
	 * @return \pocketmine\item\Armor[]
	 */
	public static function getTier(int $tier, string $player): array
	{
		$color = TeamColor::getDyeColor(PlayerManager::get($player)->getTeam());
		return array_map(static function ($armor) use ($color) {
			$armor->setCustomColor($color);
			$armor->setUnbreakable();
			return $armor;
		}, self::getTiers()[$tier]);
	}

	/**
	 * @param Player $player
	 */
	public static function upgrade(Player $player): void
	{
		$player->getArmorInventory()->setContents([]);
		self::$data[$player->getName()] = min(self::getTierOf($player->getName()) + 1, count(self::getTiers()) - 1);
		$player->getArmorInventory()->setContents(self::getTier(self::getTierOf($player->getName()), $player->getName()));
	}

	/**
	 * @param string $player
	 * @return int
	 */
	public static function getTierOf(string $player): int
	{
		return self::$data[$player] ?? 0;
	}

	/**
	 * @param string $player
	 * @return Item
	 */
	public static function getCost(string $player): Item
	{
		return self::getCosts()[self::getTierOf($player) + 1] ?? ItemFactory::get(ItemIds::EMERALD, 0, 64);
	}
}