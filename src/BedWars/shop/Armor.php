<?php

namespace BedWars\shop;

use BedWars\player\PlayerManager;
use BedWars\utils\TeamColor;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

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
				VanillaItems::LEATHER_CAP(),
				VanillaItems::LEATHER_TUNIC(),
				VanillaItems::LEATHER_PANTS(),
				VanillaItems::LEATHER_BOOTS()
			],
			[
				VanillaItems::LEATHER_CAP(),
				VanillaItems::LEATHER_TUNIC(),
				VanillaItems::CHAINMAIL_LEGGINGS(),
				VanillaItems::LEATHER_BOOTS()
			],
			[
				VanillaItems::IRON_HELMET(),
				VanillaItems::LEATHER_TUNIC(),
				VanillaItems::CHAINMAIL_LEGGINGS(),
				VanillaItems::IRON_BOOTS()
			],
			[
				VanillaItems::IRON_HELMET(),
				VanillaItems::LEATHER_TUNIC(),
				VanillaItems::DIAMOND_LEGGINGS(),
				VanillaItems::IRON_BOOTS()
			],
			[
				VanillaItems::DIAMOND_HELMET(),
				VanillaItems::LEATHER_TUNIC(),
				VanillaItems::DIAMOND_LEGGINGS(),
				VanillaItems::DIAMOND_BOOTS()
			]
		];
	}

	/**
	 * @return Item[]
	 */
	public static function getCosts(): array
	{
		return [
			ItemFactory::air(), //7 Bars
			VanillaItems::EMERALD(), //9 Bars (+2)
			VanillaItems::EMERALD()->setCount(2), //11 Bars (+2)
			VanillaItems::EMERALD()->setCount(3), //13 Bars (+2)
			VanillaItems::EMERALD()->setCount(4), //15 Bars (+2)
		];
	}

	/**
	 * @param int    $tier
	 * @param string $player
	 * @return \pocketmine\item\Armor[]
	 */
	public static function getTier(int $tier, string $player): array
	{
		$color = TeamColor::getDyeColor(PlayerManager::get($player)->getTeam());
		return array_map(static function ($armor) use ($color) {
			$armor->setCustomColor($color->getRgbValue());
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
		return self::getCosts()[self::getTierOf($player) + 1] ?? VanillaItems::EMERALD()->setCount(64);
	}
}