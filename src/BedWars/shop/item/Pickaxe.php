<?php

namespace BedWars\shop\item;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class Pickaxe extends PermanentBedWarsItem
{
	public function __construct()
	{
		$last = ItemFactory::get(ItemIds::DIAMOND_PICKAXE);
		$last->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EFFICIENCY), 5));

		$last2 = ItemFactory::get(ItemIds::DIAMOND_PICKAXE);
		$last2->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::EFFICIENCY), 2));

		parent::__construct([
			ItemFactory::get(ItemIds::WOODEN_PICKAXE),
			ItemFactory::get(ItemIds::STONE_PICKAXE),
			ItemFactory::get(ItemIds::IRON_PICKAXE),
			ItemFactory::get(ItemIds::DIAMOND_PICKAXE),
			$last2,
			$last
		], [
			ItemFactory::get(ItemIds::AIR),
			ItemFactory::get(ItemIds::DIAMOND),
			ItemFactory::get(ItemIds::DIAMOND, 0, 2),
			ItemFactory::get(ItemIds::DIAMOND, 0, 4),
			ItemFactory::get(ItemIds::DIAMOND, 0, 8),
			ItemFactory::get(ItemIds::DIAMOND, 0, 16)
		], "Pickaxe");
	}
}