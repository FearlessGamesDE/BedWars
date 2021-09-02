<?php

namespace BedWars\shop\item;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;

class Sword extends PermanentBedWarsItem
{
	public function __construct()
	{
		parent::__construct([
			VanillaItems::WOODEN_SWORD(),
			VanillaItems::STONE_SWORD(),
			VanillaItems::IRON_SWORD(),
			VanillaItems::DIAMOND_SWORD(),
			VanillaItems::DIAMOND_SWORD()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::SHARPNESS(), 2))
		], [
			ItemFactory::air(),
			VanillaItems::DIAMOND()->setCount(2),
			VanillaItems::DIAMOND()->setCount(4),
			VanillaItems::DIAMOND()->setCount(8),
			VanillaItems::DIAMOND()->setCount(16)
		], "Sword");
	}
}