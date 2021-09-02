<?php

namespace BedWars\shop\item;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class Sword extends PermanentBedWarsItem
{
	public function __construct()
	{
		$last = ItemFactory::get(ItemIds::DIAMOND_SWORD);
		$last->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::SHARPNESS), 2));
		parent::__construct([
			ItemFactory::get(ItemIds::WOODEN_SWORD),
			ItemFactory::get(ItemIds::STONE_SWORD),
			ItemFactory::get(ItemIds::IRON_SWORD),
			ItemFactory::get(ItemIds::DIAMOND_SWORD),
			$last
		], [
			ItemFactory::get(ItemIds::AIR),
			ItemFactory::get(ItemIds::DIAMOND, 0, 2),
			ItemFactory::get(ItemIds::DIAMOND, 0, 4),
			ItemFactory::get(ItemIds::DIAMOND, 0, 8),
			ItemFactory::get(ItemIds::DIAMOND, 0, 16)
		], "Sword");
	}
}