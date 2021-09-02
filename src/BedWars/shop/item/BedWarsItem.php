<?php

namespace BedWars\shop\item;

use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\player\Player;

abstract class BedWarsItem
{
	private Item $item;
	private Item $cost;
	private string $name;
	private string $image;

	/**
	 * BedWarsItem constructor.
	 * @param Item   $item
	 * @param Item   $cost
	 * @param string $name
	 * @param string $image
	 */
	public function __construct(Item $item, Item $cost, string $name = "", string $image = "")
	{
		$this->image = $image;
		if ($name === "") {
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