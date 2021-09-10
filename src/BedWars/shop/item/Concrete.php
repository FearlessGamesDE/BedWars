<?php

namespace BedWars\shop\item;

use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

class Concrete extends TeamItem
{
	public function __construct()
	{
		parent::__construct(VanillaItems::IRON_INGOT(), array_map(static function ($team) {
			return VanillaBlocks::CONCRETE()->setColor(array_values(DyeColor::getAll())[$team])->asItem()->setCount(8);
		}, range(0, 15)), "Concrete");
	}
}