<?php

namespace BedWars;

use BedWars\player\BedWarsPlayer;
use BedWars\player\PlayerManager;
use BedWars\shop\item\ItemManager;
use BedWars\shop\item\PermanentBedWarsItem;
use BedWars\shop\ShopManager;
use BedWars\team\TeamManager;
use BedWars\utils\TeamColor;
use pocketmine\block\Air;
use pocketmine\block\Bed;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\inventory\ChestInventory;
use pocketmine\block\tile\Bed as BedTile;
use pocketmine\block\tile\Chest;
use pocketmine\block\TNT;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\crafting\CraftingGrid;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBucketEmptyEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\inventory\PlayerCursorInventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Armor;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\Potion;
use pocketmine\math\VoxelRayTrace;
use pocketmine\network\mcpe\protocol\TakeItemActorPacket;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use UnexpectedValueException;

class EventHandler implements Listener
{
	/**
	 * @param PlayerJoinEvent $event
	 */
	public function onLogin(PlayerJoinEvent $event): void
	{
		ShopManager::upgraderTick($event->getPlayer());
		$event->setJoinMessage("");
		ScoreboardHandler::create($event->getPlayer(), "§4Bed§fWars");
		ScoreboardHandler::update();
		switch (BedWars::getStatus()) {
			case BedWars::PRE_GAME:
				$event->getPlayer()->setGamemode(GameMode::SPECTATOR());
				$event->getPlayer()->teleport($event->getPlayer()->getWorld()->getSpawnLocation());
				Messages::send($event->getPlayer(), "wait-for-start");
				break;
			case BedWars::PLAYING:
				$player = PlayerManager::get($event->getPlayer()->getName());
				if ($player->isPlayer() && $player->getTeam()->hasBed()) {
					$player->respawn();
					$event->getPlayer()->setNameTag(TeamColor::getChatFormat($player->getTeam()) . $event->getPlayer()->getName());
					$event->setJoinMessage(Messages::translate("prefix") . Messages::replace("rejoin", TeamColor::getChatFormat($player->getTeam()) . $player->getName()));
				} else {
					$event->getPlayer()->setGamemode(GameMode::SPECTATOR());
					$event->getPlayer()->teleport($event->getPlayer()->getWorld()->getSpawnLocation());
					HudManager::send($event->getPlayer());
				}
				break;
			case BedWars::AFTER_GAME:
				$event->getPlayer()->setGamemode(GameMode::SPECTATOR());
				$event->getPlayer()->teleport($event->getPlayer()->getWorld()->getSpawnLocation());
				HudManager::send($event->getPlayer());
				break;
		}
	}

	/**
	 * @param PlayerQuitEvent $event
	 */
	public function onQuit(PlayerQuitEvent $event): void
	{
		$event->setQuitMessage("");
		$player = PlayerManager::get($event->getPlayer()->getName());
		if ($player->getStatus() !== BedWarsPlayer::SPECTATOR) {
			$event->setQuitMessage(Messages::translate("prefix") . Messages::replace("leave", TeamColor::getChatFormat($player->getTeam()) . $event->getPlayer()->getName()));
			$player->setStatus(BedWarsPlayer::SPECTATOR);
			$player->kill();
		}
	}

	/**
	 * @param EntityItemPickupEvent $event
	 */
	public function onPickup(EntityItemPickupEvent $event): void
	{
		$entity = $event->getEntity();
		if ($entity instanceof Player) {
			$inventory = $entity->getInventory();
			$players = array_filter(Server::getInstance()->getOnlinePlayers(), static function ($player) use ($inventory, $event) {
				return $inventory->getHolder()->getPosition()->distance($player->getPosition()) < 2 and ($player->isCreative() or $player->getInventory()->canAddItem($event->getItem()));
			});
			$player = $players[array_rand($players)];

			$pk = new TakeItemActorPacket();
			$pk->eid = $player->getId();
			$pk->target = $event->getItem()->getId();
			Server::getInstance()->getWorldManager()->getDefaultWorld()?->broadcastPacketToViewers($event->getOrigin()->getPosition(), $pk);

			$player->getInventory()->addItem(clone $event->getItem());
			$event->getOrigin()->flagForDespawn();
		}
		$event->cancel();
	}

	/**
	 * @param PlayerDropItemEvent $event
	 */
	public function onDrop(PlayerDropItemEvent $event): void
	{
		if (($player = PlayerManager::get($event->getPlayer()->getName()))->getStatus() !== BedWarsPlayer::ALIVE) {
			$event->cancel();
		} else {
			$bedWarsItem = ItemManager::get($event->getItem()->getName());
			if ($bedWarsItem instanceof PermanentBedWarsItem) {
				$event->cancel();
				if ($event->getPlayer()->getCursorInventory()->contains($event->getItem()) || $event->getPlayer()->getCraftingGrid()->contains($event->getItem())) {
					$old = $event->getPlayer()->getInventory()->getItem($player->getSlot($bedWarsItem->getSlot()));
					if (ItemManager::get($old->getName()) instanceof PermanentBedWarsItem) {
						$slot = 0;
						foreach ($event->getPlayer()->getInventory()->getContents(true) as $i => $item) {
							if (!ItemManager::get($item->getName()) instanceof PermanentBedWarsItem) {
								$slot = $i;
								break;
							}
						}
						$old = $event->getPlayer()->getInventory()->getItem($slot);
						$event->getPlayer()->getInventory()->setItem($slot, $event->getItem());
						$player->setSlot($bedWarsItem->getSlot(), $slot);
						foreach ($event->getPlayer()->getInventory()->addItem($old) as $item) {
							$event->getPlayer()->dropItem($item);
						}
					} else {
						$event->getPlayer()->getInventory()->setItem($player->getSlot($bedWarsItem->getSlot()), $event->getItem());
						foreach ($event->getPlayer()->getInventory()->addItem($old) as $item) {
							$event->getPlayer()->dropItem($item);
						}
					}
					foreach ($event->getPlayer()->getCursorInventory()->removeItem($event->getItem()) as $item) {
						$event->getPlayer()->getCraftingGrid()->removeItem($item);
					}
				}
			}
		}
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function onDamage(EntityDamageEvent $event): void
	{
		$entity = $event->getEntity();
		if ($entity instanceof Player) {
			$bedWarsPlayer = PlayerManager::get($entity->getName());
			if ($bedWarsPlayer->getStatus() !== BedWarsPlayer::ALIVE) {
				$event->cancel();
				return;
			}
			if ($event instanceof EntityDamageByEntityEvent) {
				$damager = $event->getDamager();
				if ($damager instanceof Player) {
					if (PlayerManager::get($damager->getName())->getTeam() === $bedWarsPlayer->getTeam()) {
						$event->cancel();
						return;
					}
					$bedWarsPlayer->damage($damager->getName());
				}
			}
			if ($entity->getHealth() <= $event->getFinalDamage()) {
				$bedWarsPlayer->kill();
				$bedWarsPlayer->respawn();
				$event->cancel();
			}
		}
	}

	/**
	 * @param InventoryTransactionEvent $event
	 */
	public function onTransaction(InventoryTransactionEvent $event): void
	{
		if (($player = PlayerManager::get($event->getTransaction()->getSource()->getName()))->getStatus() !== BedWarsPlayer::ALIVE) {
			$event->cancel();
		} else {
			foreach ($event->getTransaction()->getActions() as $action) {
				if ($action instanceof SlotChangeAction) {
					$item = ItemManager::get($action->getTargetItem()->getName());
					if ($action->getInventory() instanceof CraftingGrid) {
						break;
					}

					if ($item instanceof PermanentBedWarsItem) {
						if ($action->getInventory() instanceof PlayerInventory) {
							$player->setSlot($item->getSlot(), $action->getSlot());
						} elseif (!$action->getInventory() instanceof PlayerCursorInventory) {
							$event->cancel();
							break;
						}
					} elseif ($action->getTargetItem() instanceof Armor) {
						$event->cancel();
						break;
					}
				}
			}
		}
	}

	/**
	 * @param PlayerItemHeldEvent $event
	 */
	public function onHeld(PlayerItemHeldEvent $event): void
	{
		if ((PlayerManager::get($event->getPlayer()->getName()))->getStatus() === BedWarsPlayer::ALIVE) {
			ShopManager::upgraderTick($event->getPlayer(), $event->getSlot());
		}
	}

	/**
	 * @param PlayerInteractEvent $event
	 */
	public function onInteract(PlayerInteractEvent $event): void
	{
		if (($p = PlayerManager::get($event->getPlayer()->getName()))->getStatus() === BedWarsPlayer::ALIVE) {
			if ($event->getBlock() instanceof Bed) {
				/** @var Bed $bed */
				$bed = $event->getBlock();
				if ($bed->getOtherHalf() instanceof Bed && $event->getItem()->getId() === ItemIds::BRICK_BLOCK && $bed->getColor() === TeamColor::getDyeColor($p->getTeam())) {
					$first = array_merge($bed->getAllSides(), $bed->getOtherHalf()->getAllSides());
					foreach ($first as $block) {
						if (($block instanceof Air) && BlockManager::isAllowedToPlace($block->getPosition())) {
							$event->getPlayer()->getWorld()->setBlock($block->getPosition(), VanillaBlocks::GOLD());
						}
						foreach ($block->getAllSides() as $b) {
							if (($b instanceof Air) && !in_array($b, $first, true) && BlockManager::isAllowedToPlace($b->getPosition())) {
								$event->getPlayer()->getWorld()->setBlock($b->getPosition(), VanillaBlocks::CONCRETE()->setColor(TeamColor::getDyeColor($p->getTeam())));
							}
						}
					}

					$new = $event->getPlayer()->getInventory()->getItemInHand();
					$new->pop();
					$event->getPlayer()->getInventory()->setItemInHand($new);

					$event->cancel();
				}
				return;
			}
			if (ShopManager::use($event->getPlayer(), $event->getBlock()->getPosition())) {
				$event->cancel();
			} elseif ($p->canUseItem()) {
				if ($event->getItem()->getId() === ItemIds::GLOWSTONE_DUST) {
					$event->getPlayer()->teleport(PlayerManager::get($event->getPlayer()->getName())->getTeam()->getSpawn());
					$event->cancel();

					$new = $event->getPlayer()->getInventory()->getItemInHand();
					$new->pop();
					$event->getPlayer()->getInventory()->setItemInHand($new);
					$p->useItem();
				} elseif ($event->getItem()->getId() === ItemIds::BRICK_BLOCK) {
					foreach (VoxelRayTrace::betweenPoints($event->getPlayer()->getPosition()->subtract(0, 1, 0), $event->getPlayer()->getPosition()->addVector($event->getPlayer()->getDirectionVector()->multiply(20))->add(0, 1, 0)) as $block) {
						if (($event->getBlock()->getPosition()->getWorld()->getBlock($block) instanceof Air) && BlockManager::isAllowedToPlace($block)) {
							$event->getPlayer()->getPosition()->getWorld()->setBlock($block, VanillaBlocks::CONCRETE()->setColor(TeamColor::getDyeColor($p->getTeam())));
						}
					}
					$event->cancel();

					$new = $event->getPlayer()->getInventory()->getItemInHand();
					$new->pop();
					$event->getPlayer()->getInventory()->setItemInHand($new);
					$p->useItem();
				}
			}
		} elseif (HudManager::handle($event->getPlayer())) {
			$event->cancel();
		}
	}

	/**
	 * @param BlockPlaceEvent $event
	 */
	public function onPlace(BlockPlaceEvent $event): void
	{
		$block = $event->getBlock();
		if (!BlockManager::isAllowedToPlace($block->getPosition())) {
			$event->cancel();
		} elseif ($block instanceof TNT) {
			$block->ignite();
			$event->cancel();

			$new = $event->getPlayer()->getInventory()->getItemInHand();
			$new->pop();
			$event->getPlayer()->getInventory()->setItemInHand($new);
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 */
	public function onBreak(BlockBreakEvent $event): void
	{
		if ($event->getBlock() instanceof Bed) {
			$bed = $event->getBlock()->getPosition()->getWorld()->getTile($event->getBlock()->getPosition());
			if ($bed instanceof BedTile && !TeamManager::get(TeamColor::fromDyeColor($bed->getColor()))->destroyBed($event->getPlayer()->getName())) {
				$event->cancel();
			}
			$event->setDrops([]);
			return;
		}

		if (!BlockManager::isAllowedToBreak($event->getBlock()->getPosition())) {
			$event->cancel();
		}
	}

	/**
	 * @param CraftItemEvent $event
	 */
	public function onCraft(CraftItemEvent $event): void
	{
		$event->cancel();
	}

	/**
	 * @param PlayerExhaustEvent $event
	 */
	public function onExhaust(PlayerExhaustEvent $event): void
	{
		$event->cancel();
	}

	/**
	 * @param EntityExplodeEvent $event
	 */
	public function onExplode(EntityExplodeEvent $event): void
	{
		$event->setBlockList(array_filter($event->getBlockList(), static function ($block) {
			return BlockManager::isAllowedToBreak($block->getPosition()) and !$block instanceof Bed;
		}));
	}

	/**
	 * @param PlayerItemConsumeEvent $event
	 */
	public function onConsume(PlayerItemConsumeEvent $event): void
	{
		$item = $event->getItem();
		if ($item instanceof Potion) {
			$event->cancel();
			$event->getPlayer()->getInventory()->removeItem($item);
			foreach ($item->getAdditionalEffects() as $effect) {
				$event->getPlayer()->getEffects()->add($effect);
			}
		}
	}


	/**
	 * @param PlayerBucketEmptyEvent $event
	 */
	public function onBucketEmpty(PlayerBucketEmptyEvent $event): void
	{
		$event->cancel();
		$event->getPlayer()->getInventory()->setItemInHand(ItemFactory::air());
		$event->getBlockClicked()->getPosition()->getWorld()->setBlock($event->getBlockClicked()->getPosition(), VanillaBlocks::WATER());
		Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(static function () use ($event): void {
			if ($event->getBlockClicked()->getPosition()->getWorld()->getBlock($event->getBlockClicked()->getPosition()) instanceof Water) {
				$event->getBlockClicked()->getPosition()->getWorld()->setBlock($event->getBlockClicked()->getPosition(), VanillaBlocks::AIR());
			}
		}), 20);
	}

	/**
	 * @param BlockSpreadEvent $event
	 */
	public function onSpread(BlockSpreadEvent $event): void
	{
		$event->cancel();
	}

	/**
	 * @param PlayerBedEnterEvent $event
	 */
	public function onSleep(PlayerBedEnterEvent $event): void
	{
		$event->cancel();
	}

	/**
	 * @param InventoryOpenEvent $event
	 */
	public function onOpen(InventoryOpenEvent $event): void
	{
		$inventory = $event->getInventory();
		if ($inventory instanceof ChestInventory && (($p = PlayerManager::get($event->getPlayer()->getName()))->isPlayer())) {
			$tile = $event->getPlayer()->getWorld()->getTile($inventory->getHolder());
			if ($tile instanceof Chest) {
				$name = explode(" ", $tile->getName())[0];
				try {
					$team = TeamManager::get(TeamColor::fromName($name));
					if ($p->getTeam() !== $team && ($team->hasBed() || $team->getAlivePlayers() > 0)) {
						$event->cancel();
					}
				} catch (UnexpectedValueException) {
				}
			}
		}
	}

	/**
	 * @param PlayerChatEvent $event
	 * @priority HIGHEST
	 */
	public function onChat(PlayerChatEvent $event): void
	{
		$event->cancel();

		$all = 0;
		$message = str_ireplace(["@everyone ", "@everyone", "@all ", "@all", "@a ", "@a"], "", $event->getMessage(), $all);

		if (($p = PlayerManager::get($event->getPlayer()->getName()))->getStatus() === BedWarsPlayer::SPECTATOR) {
			Messages::send(array_filter(Server::getInstance()->getOnlinePlayers(), static function ($player) {
				return PlayerManager::get($player->getName())->getStatus() === BedWarsPlayer::SPECTATOR;
			}), "chat-spectator", ["{player}" => $event->getPlayer()->getName(), "{message}" => $message]);
		} elseif ($p->isPlayer()) {
			if ($all > 0) {
				Messages::send(Server::getInstance()->getOnlinePlayers(), "chat-all", ["{player}" => TeamColor::getChatFormat($p->getTeam()) . $event->getPlayer()->getName(), "{message}" => $message]);
			} else {
				Messages::send(array_filter(Server::getInstance()->getOnlinePlayers(), static function ($player) use ($p) {
					return ($pl = PlayerManager::get($player->getName()))->isPlayer() && $pl->getTeam() === $p->getTeam();
				}), "chat-team", ["{player}" => TeamColor::getChatFormat($p->getTeam()) . $event->getPlayer()->getName(), "{message}" => $message]);
			}
		}
	}
}