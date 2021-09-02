<?php

namespace BedWars\shop\item;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;

class Pickaxe extends PermanentBedWarsItem
{
	public function __construct()
	{
		parent::__construct([
			VanillaItems::WOODEN_PICKAXE(),
			VanillaItems::STONE_PICKAXE(),
			VanillaItems::IRON_PICKAXE(),
			VanillaItems::DIAMOND_PICKAXE(),
			VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 2)),
			VanillaItems::DIAMOND_PICKAXE()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::EFFICIENCY(), 5))
		], [
			ItemFactory::air(),
			VanillaItems::DIAMOND(),
			VanillaItems::DIAMOND()->setCount(2),
			VanillaItems::DIAMOND()->setCount(4),
			VanillaItems::DIAMOND()->setCount(8),
			VanillaItems::DIAMOND()->setCount(16)
		], "Pickaxe");
	}
}