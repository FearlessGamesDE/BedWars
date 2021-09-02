<?php

namespace BedWars\shop\item;

use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;

class Obsidian extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(VanillaBlocks::OBSIDIAN()->asItem()->setCount(8), VanillaItems::EMERALD()->setCount(4), "Obsidian");
	}
}