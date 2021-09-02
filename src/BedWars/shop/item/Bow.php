<?php

namespace BedWars\shop\item;

use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;

class Bow extends TieredBedWarsItem
{
	public function __construct()
	{
		parent::__construct([
			VanillaItems::BOW(),
			VanillaItems::BOW()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 2)),
			VanillaItems::BOW()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::PUNCH(), 2))
		], [
			VanillaItems::IRON_INGOT()->setCount(64),
			VanillaItems::DIAMOND()->setCount(5),
			VanillaItems::DIAMOND()->setCount(12)
		], "Bow", "textures/items/bow_standby");
	}
}