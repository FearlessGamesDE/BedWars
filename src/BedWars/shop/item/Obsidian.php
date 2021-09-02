<?php

namespace BedWars\shop\item;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

class Obsidian extends BedWarsItem
{
	public function __construct()
	{
		parent::__construct(ItemFactory::get(ItemIds::OBSIDIAN, 0, 8), ItemFactory::get(ItemIds::EMERALD, 0, 4), "Obsidian");
	}
}