<?php

namespace BedWars\utils;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier as BID;
use pocketmine\block\BlockLegacyIds as Ids;
use pocketmine\block\tile\Bed as TileBed;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class Bed extends \pocketmine\block\Bed
{
	public function __construct()
	{
		parent::__construct(new BID(Ids::BED_BLOCK, 0, ItemIds::BED, TileBed::class), "Bed Block", new BlockBreakInfo(0.2));
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool
	{
		return false;
	}
}