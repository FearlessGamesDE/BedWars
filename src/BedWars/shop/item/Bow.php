<?php

namespace BedWars\shop\item;

use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class Bow extends TieredBedWarsItem
{
	public function __construct()
	{
		$last = ItemFactory::get(ItemIds::BOW);
		$last->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::PUNCH), 2));

		$last2 = ItemFactory::get(ItemIds::BOW);
		$last2->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(Enchantment::POWER), 2));

		parent::__construct([
			ItemFactory::get(ItemIds::BOW),
			$last2,
			$last
		], [
			ItemFactory::get(ItemIds::IRON_INGOT, 0, 64),
			ItemFactory::get(ItemIds::DIAMOND, 0, 5),
			ItemFactory::get(ItemIds::DIAMOND, 0, 12)
		], "Bow", "textures/items/bow_standby");
	}
}