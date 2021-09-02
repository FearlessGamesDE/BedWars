<?php

namespace BedWars\shop\item;

use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

abstract class TieredBedWarsItem extends BedWarsItem
{
	/**
	 * @var Item[]
	 */
	protected array $tiers;
	/**
	 * @var Item[]
	 */
	protected array $costs;

	/**
	 * TieredBedWarsItem constructor.
	 * @param Item[] $tiers
	 * @param Item[] $costs
	 * @param string $name
	 * @param string $image
	 */
	public function __construct(array $tiers, array $costs, string $name, string $image = "")
	{
		$this->tiers = array_map(static function ($item) {
			if ($item instanceof Durable) {
				$item->setUnbreakable();
			}
			return $item;
		}, $tiers);
		$this->costs = $costs;
		parent::__construct($tiers[0], $costs[0], $name, $image);
	}

	/**
	 * @return Item[]
	 */
	public function getTiers(): array
	{
		return $this->tiers;
	}

	/**
	 * @param Player $player
	 */
	public function upgrade(Player $player): void
	{
		$old = $this->getTierOf($player);
		$this->setTierOf($player, min($old + 1, count($this->tiers) - 1));
		$player->getInventory()->setItem($player->getInventory()->getHeldItemIndex(), $this->getItem($player));
		$this->setTierOf($player, min($old + 1, count($this->tiers) - 1));
	}

	/**
	 * @param Player   $player
	 * @param int|null $slot
	 * @return int
	 */
	public function getTierOf(Player $player, int $slot = null): int
	{
		if ($slot === null) {
			$slot = $player->getInventory()->getHeldItemIndex();
		}
		return $player->getInventory()->getItem($slot)->getNamedTag()->getInt("level", 0);
	}

	/**
	 * @param Player $player
	 * @param int    $tier
	 * @return void
	 */
	public function setTierOf(Player $player, int $tier): void
	{
		$new = $player->getInventory()->getItemInHand()->getNamedTag();
		$new->setInt("level", $tier);
		$player->getInventory()->setItemInHand($player->getInventory()->getItemInHand()->setNamedTag($new));
	}

	/**
	 * @param Player $player
	 * @return Item
	 */
	public function getItem(Player $player): Item
	{
		return $this->getTiers()[$this->getTierOf($player)];
	}

	/**
	 * @param Player   $player
	 * @param int|null $slot
	 * @return Item
	 */
	public function getCost(Player $player, int $slot = null): Item
	{
		return $this->costs[$this->getTierOf($player, $slot) + 1] ?? VanillaItems::EMERALD()->setCount(64);
	}

	/**
	 * @return Item
	 */
	public function getBaseCost(): Item
	{
		return $this->costs[0];
	}
}