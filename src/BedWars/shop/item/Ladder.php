<?php

namespace BedWars\shop\item;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

class Ladder extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(VanillaBlocks::LADDER()->asItem()->setCount(8), VanillaItems::IRON_INGOT()->setCount(4), "Ladder", "textures/blocks/ladder");
	}
}