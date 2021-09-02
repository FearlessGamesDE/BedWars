<?php

namespace BedWars\shop\item;

use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\Player;

abstract class BedWarsItem
{
	/**
	 * @var Item
	 */
	private $item;
	/**
	 * @var Item
	 */
	private $cost;
	/**
	 * @var string
	 */
	private $name;
	/**
	 * @var string
	 */
	private $image;

	/**
	 * BedWarsItem constructor.
	 * @param Item $item
	 * @param Item $cost
	 * @param string|null $name
	 * @param string $image
	 */
	public function __construct(Item $item, Item $cost, string $name = null, string $image = "")
	{
		$this->image = $image;
		if ($name === null) {
			$name = $item->getName();
		}
		$this->name = $name;
		$this->item = $item;
		if ($this->item instanceof Durable) {
			$this->item->setUnbreakable();
		}
		$this->cost = $cost;
	}

	/**
	 * @return Item
	 */
	public function getRawItem(): Item
	{
		return $this->item;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getImage(): string
	{
		return $this->image;
	}

	/**
	 * @param Player $player
	 * @return Item
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function getItem(Player $player): Item
	{
		return $this->item;
	}

	/**
	 * @return Item
	 */
	public function getBaseCost(): Item
	{
		return $this->cost;
	}
}