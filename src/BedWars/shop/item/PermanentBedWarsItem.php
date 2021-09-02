<?php

namespace BedWars\shop\item;

use BedWars\player\PlayerManager;
use pocketmine\item\Item;
use pocketmine\Player;

abstract class PermanentBedWarsItem extends TieredBedWarsItem
{
	/**
	 * @var string
	 */
	private $slot;
	/**
	 * @var int[]
	 */
	private $data = [];

	/**
	 * PermanentBedWarsItem constructor.
	 * @param Item[] $tiers
	 * @param Item[] $costs
	 * @param string $slot
	 */
	public function __construct(array $tiers, array $costs, string $slot)
	{
		$this->slot = $slot;
		parent::__construct($tiers, $costs, $slot);
	}

	/**
	 * @param Player $player
	 */
	public function upgrade(Player $player): void
	{
		$player->getInventory()->removeItem($this->getItem($player));
		$this->data[$player->getName()] = min($this->getTierOf($player) + 1, count($this->tiers) - 1);
		$player->getInventory()->setItem(PlayerManager::get($player->getName())->getSlot($this->slot), $this->getItem($player));
	}

	/**
	 * @param Player $player
	 */
	public function downgrade(Player $player): void
	{
		$player->getInventory()->removeItem($this->getItem($player));
		$this->data[$player->getName()] = max($this->getTierOf($player) - 1, 0);
		$player->getInventory()->setItem(PlayerManager::get($player->getName())->getSlot($this->slot), $this->getItem($player));
	}

	/**
	 * @param Player $player
	 * @param int|null $slot
	 * @return int
	 */
	public function getTierOf(Player $player, int $slot = null): int
	{
		return $this->data[$player->getName()] ?? 0;
	}

	/**
	 * @return string
	 */
	public function getSlot(): string
	{
		return $this->slot;
	}
}