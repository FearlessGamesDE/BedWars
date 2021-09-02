<?php

namespace BedWars\shop\item;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

class Gold extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(VanillaBlocks::GOLD()->asItem()->setCount(8), VanillaItems::IRON_INGOT()->setCount(6), "Gold");
	}
}