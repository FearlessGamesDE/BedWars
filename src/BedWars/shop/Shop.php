<?php

namespace BedWars\shop;

use BedWars\Messages;
use BedWars\shop\item\BedWarsItem;
use BedWars\shop\item\ItemManager;
use BedWars\shop\item\PermanentBedWarsItem;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\Player;
use pocketmine\tile\Sign;
use pocketmine\math\Vector3;
use pocketmine\Server;
use UnexpectedValueException;

class Shop
{
	/**
	 * @var Vector3
	 */
	private $position;
	/**
	 * @var BedWarsItem
	 */
	private $type;

	/**
	 * Shop constructor.
	 * @param Vector3 $position
	 * @param string $type
	 */
	public function __construct(Vector3 $position, string $type)
	{
		$this->position = $position;
		$this->type = ItemManager::get($type);
		if (!$this->type instanceof BedWarsItem) {
			Server::getInstance()->getLogger()->critical("Unknown Item '" . $type . "'");
		}
	}

	public function load(Level $level): void
	{
		$tile = $level->getTile($this->position);
		if (!$tile instanceof Sign) {
			throw new UnexpectedValueException("Invalid Shop position");
		}
		$tile->setText("§e§l" . $this->type->getRawItem()->getCount() . " " . $this->type->getName(), "", self::getCost($this->type->getBaseCost()));
	}

	/**
	 * @param Player $player
	 */
	public function use(Player $player): void
	{
		if ($player->isSneaking()) {
			$recources = 0;
			foreach ($player->getInventory()->getContents() as $item) {
				if ($item->getId() === $this->type->getBaseCost()->getId()) {
					$recources += $item->getCount();
				}
			}
			$count = min(floor($this->type->getRawItem()->getMaxStackSize() / $this->type->getItem($player)->getCount()), floor($recources / $this->type->getBaseCost()->getCount()));
			if ($count <= 0) {
				Messages::send($player, "not-enough");
				return;
			}
			$cost = clone $this->type->getBaseCost();
			$cost->setCount($cost->getCount() * $count);
			$result = clone $this->type->getItem($player);
			$result->setCount($result->getCount() * $count);
			$player->getInventory()->removeItem($cost);
			$prev = $player->getInventory()->getItem($player->getInventory()->getHeldItemIndex());
			if (ItemManager::get($prev->getName()) instanceof PermanentBedWarsItem) {
				foreach ($player->getInventory()->addItem($result) as $item) {
					$player->getLevel()->dropItem($player, $item);
				}
			} else {
				$player->getInventory()->setItem($player->getInventory()->getHeldItemIndex(), $result);
				foreach ($player->getInventory()->addItem($prev) as $item) {
					$player->getLevel()->dropItem($player, $item);
				}
			}
			$pk = new PlaySoundPacket();
			$pk->soundName = "note.bell";
			$pk->pitch = 1;
			$pk->volume = 100;
			$pk->x = $player->getX();
			$pk->y = $player->getY();
			$pk->z = $player->getZ();
			$player->dataPacket($pk);
			Messages::send($player, "bought", ["{item}" => $this->type->getItem($player)->getName(), "{cost}" => self::getCost($cost)]);
		} elseif ($player->getInventory()->contains($this->type->getBaseCost())) {
			$player->getInventory()->removeItem($this->type->getBaseCost());
			$prev = $player->getInventory()->getItem($player->getInventory()->getHeldItemIndex());
			if (ItemManager::get($prev->getName()) instanceof PermanentBedWarsItem) {
				foreach ($player->getInventory()->addItem($this->type->getItem($player)) as $item) {
					$player->getLevel()->dropItem($player, $item);
				}
			} else {
				$player->getInventory()->setItem($player->getInventory()->getHeldItemIndex(), $this->type->getItem($player));
				foreach ($player->getInventory()->addItem($prev) as $item) {
					$player->getLevel()->dropItem($player, $item);
				}
			}
			$pk = new PlaySoundPacket();
			$pk->soundName = "note.bell";
			$pk->pitch = 1;
			$pk->volume = 100;
			$pk->x = $player->getX();
			$pk->y = $player->getY();
			$pk->z = $player->getZ();
			$player->dataPacket($pk);
			Messages::send($player, "bought", ["{item}" => $this->type->getItem($player)->getName(), "{cost}" => self::getCost($this->type->getBaseCost())]);
		} else {
			Messages::send($player, "not-enough");
		}
	}

	/**
	 * @param Item $cost
	 * @return string
	 */
	public static function getCost(Item $cost): string
	{
		switch ($cost->getId()) {
			case ItemIds::IRON_INGOT:
				return "§7" . $cost->getCount() . " Iron";
			case ItemIds::EMERALD:
				return "§2" . $cost->getCount() . " Emerald" . ($cost->getCount() > 1 ? "s" : "");
			case ItemIds::DIAMOND:
				return "§b" . $cost->getCount() . " Diamond" . ($cost->getCount() > 1 ? "s" : "");
		}
		throw new UnexpectedValueException("Invalid cost " . $cost->getName());
	}
}