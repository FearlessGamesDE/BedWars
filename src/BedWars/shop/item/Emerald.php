<?php

namespace BedWars\shop\item;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

class Emerald extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(VanillaBlocks::EMERALD()->asItem()->setCount(8), VanillaItems::IRON_INGOT()->setCount(12), "Emerald");
	}
}