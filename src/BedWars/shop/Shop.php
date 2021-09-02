<?php

namespace BedWars\shop;

use BedWars\Messages;
use BedWars\shop\item\BedWarsItem;
use BedWars\shop\item\ItemManager;
use BedWars\shop\item\PermanentBedWarsItem;
use pocketmine\block\tile\Sign;
use pocketmine\block\utils\SignText;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;
use UnexpectedValueException;

class Shop
{
	private Vector3 $position;
	private BedWarsItem $type;

	/**
	 * Shop constructor.
	 * @param Vector3 $position
	 * @param string  $type
	 */
	public function __construct(Vector3 $position, string $type)
	{
		$this->position = $position;
		$item = ItemManager::get($type);
		if (!$item instanceof BedWarsItem) {
			Server::getInstance()->getLogger()->critical("Unknown Item '" . $type . "'");
		}
		$this->type = $item;
	}

	public function load(World $level): void
	{
		$tile = $level->getTile($this->position);
		if (!$tile instanceof Sign) {
			throw new UnexpectedValueException("Invalid Shop position");
		}
		$tile->setText(new SignText(["§e§l" . $this->type->getRawItem()->getCount() . " " . $this->type->getName(), "", self::getCost($this->type->getBaseCost())]));
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
					$player->getWorld()->dropItem($player->getPosition(), $item);
				}
			} else {
				$player->getInventory()->setItem($player->getInventory()->getHeldItemIndex(), $result);
				foreach ($player->getInventory()->addItem($prev) as $item) {
					$player->getWorld()->dropItem($player->getPosition(), $item);
				}
			}
			$pk = new PlaySoundPacket();
			$pk->soundName = "note.bell";
			$pk->pitch = 1;
			$pk->volume = 100;
			$pk->x = $player->getPosition()->getX();
			$pk->y = $player->getPosition()->getY();
			$pk->z = $player->getPosition()->getZ();
			$player->getNetworkSession()->sendDataPacket($pk);
			Messages::send($player, "bought", ["{item}" => $this->type->getItem($player)->getName(), "{cost}" => self::getCost($cost)]);
		} elseif ($player->getInventory()->contains($this->type->getBaseCost())) {
			$player->getInventory()->removeItem($this->type->getBaseCost());
			$prev = $player->getInventory()->getItem($player->getInventory()->getHeldItemIndex());
			if (ItemManager::get($prev->getName()) instanceof PermanentBedWarsItem) {
				foreach ($player->getInventory()->addItem($this->type->getItem($player)) as $item) {
					$player->getWorld()->dropItem($player->getPosition(), $item);
				}
			} else {
				$player->getInventory()->setItem($player->getInventory()->getHeldItemIndex(), $this->type->getItem($player));
				foreach ($player->getInventory()->addItem($prev) as $item) {
					$player->getWorld()->dropItem($player->getPosition(), $item);
				}
			}
			$pk = new PlaySoundPacket();
			$pk->soundName = "note.bell";
			$pk->pitch = 1;
			$pk->volume = 100;
			$pk->x = $player->getPosition()->getX();
			$pk->y = $player->getPosition()->getY();
			$pk->z = $player->getPosition()->getZ();
			$player->getNetworkSession()->sendDataPacket($pk);
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