<?php

namespace BedWars;

use alemiz\sga\events\CustomPacketEvent;
use BedWars\player\BedWarsPlayer;
use BedWars\player\PlayerManager;
use BedWars\shop\item\ItemManager;
use BedWars\shop\item\PermanentBedWarsItem;
use BedWars\shop\ShopManager;
use BedWars\team\TeamManager;
use BedWars\utils\TeamColor;
use LobbySystem\packets\server\TeamPacket;
use pocketmine\block\Bed;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\block\TNT;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
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
use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\CraftingGrid;
use pocketmine\inventory\PlayerCursorInventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Armor;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\Potion;
use pocketmine\math\VoxelRayTrace;
use pocketmine\network\mcpe\protocol\TakeItemActorPacket;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\tile\Bed as BedTile;
use UnexpectedValueException;

class EventHandler implements Listener
{
	/**
	 * @param CustomPacketEvent $event
	 */
	public function onPacket(CustomPacketEvent $event): void
	{
		$packet = $event->getPacket();
		if ($packet instanceof TeamPacket) {
			TeamManager::register($packet->team);
		}
	}

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
				$event->getPlayer()->setGamemode(3);
				$event->getPlayer()->teleport($event->getPlayer()->getLevel()->getSpawnLocation());
				Messages::send($event->getPlayer(), "wait-for-start");
				break;
			case BedWars::PLAYING:
				$player = PlayerManager::get($event->getPlayer()->getName());
				if ($player->isPlayer() && $player->getTeam()->hasBed()) {
					$player->respawn();
					$event->getPlayer()->setNameTag(TeamColor::getChatFormat($player->getTeam()) . $event->getPlayer()->getName());
					$event->setJoinMessage(Messages::translate("prefix") . Messages::replace("rejoin", TeamColor::getChatFormat($player->getTeam()) . $player->getName()));
				} else {
					$event->getPlayer()->setGamemode(3);
					$event->getPlayer()->teleport($event->getPlayer()->getLevel()->getSpawnLocation());
					HudManager::send($event->getPlayer());
				}
				break;
			case BedWars::AFTER_GAME:
				$event->getPlayer()->setGamemode(3);
				$event->getPlayer()->teleport($event->getPlayer()->getLevel()->getSpawnLocation());
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
	 * @param InventoryPickupItemEvent $event
	 */
	public function onPickup(InventoryPickupItemEvent $event): void
	{
		$inventory = $event->getInventory();
		if ($inventory instanceof PlayerInventory) {
			$players = array_filter(Server::getInstance()->getOnlinePlayers(), static function ($player) use ($inventory, $event) {
				return $inventory->getHolder()->distance($player) < 2 and ($player->isCreative() or $player->getInventory()->canAddItem($event->getItem()->getItem()));
			});
			$player = $players[array_rand($players)];

			$pk = new TakeItemActorPacket();
			$pk->eid = $player->getId();
			$pk->target = $event->getItem()->getId();
			Server::getInstance()->broadcastPacket($event->getItem()->getViewers(), $pk);

			$player->getInventory()->addItem(clone $event->getItem()->getItem());
			$event->getItem()->flagForDespawn();
		}
		$event->setCancelled();
	}

	/**
	 * @param PlayerDropItemEvent $event
	 */
	public function onDrop(PlayerDropItemEvent $event): void
	{
		if (($player = PlayerManager::get($event->getPlayer()->getName()))->getStatus() !== BedWarsPlayer::ALIVE) {
			$event->setCancelled();
		} else {
			$bedWarsItem = ItemManager::get($event->getItem()->getName());
			if ($bedWarsItem instanceof PermanentBedWarsItem) {
				$event->setCancelled();
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
				$event->setCancelled();
				return;
			}
			if ($event instanceof EntityDamageByEntityEvent) {
				$damager = $event->getDamager();
				if ($damager instanceof Player) {
					if (PlayerManager::get($damager->getName())->getTeam() === $bedWarsPlayer->getTeam()) {
						$event->setCancelled();
						return;
					}
					$bedWarsPlayer->damage($damager->getName());
				}
			}
			if ($entity->getHealth() <= $event->getFinalDamage()) {
				$bedWarsPlayer->kill();
				$bedWarsPlayer->respawn();
				$event->setCancelled();
			}
		}
	}

	/**
	 * @param InventoryTransactionEvent $event
	 */
	public function onTransaction(InventoryTransactionEvent $event): void
	{
		if (($player = PlayerManager::get($event->getTransaction()->getSource()->getName()))->getStatus() !== BedWarsPlayer::ALIVE) {
			$event->setCancelled();
		} else {
			foreach ($event->getTransaction()->getActions() as $action) {
				Server::getInstance()->getLogger()->notice(get_class($action));
				if ($action instanceof SlotChangeAction) {
					$item = ItemManager::get($action->getTargetItem()->getName());
					if ($action->getInventory() instanceof CraftingGrid) {
						break;
					}

					if ($item instanceof PermanentBedWarsItem) {
						if ($action->getInventory() instanceof PlayerInventory) {
							$player->setSlot($item->getSlot(), $action->getSlot());
						} elseif (!$action->getInventory() instanceof PlayerCursorInventory) {
							$event->setCancelled();
							break;
						}
					} elseif ($action->getTargetItem() instanceof Armor) {
						$event->setCancelled();
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
				if ($bed->getOtherHalf() instanceof Bed && $event->getItem()->getId() === ItemIds::BRICK_BLOCK) {
					$tile = $bed->getLevelNonNull()->getTile($bed);
					if ($tile instanceof BedTile && $tile->getColor() === $p->getTeam()->getColor()) {
						$first = array_merge($bed->getAllSides(), $bed->getOtherHalf()->getAllSides());
						/** @var Block $block */
						foreach ($first as $block) {
							if (($block->getId() === BlockIds::AIR) && BlockManager::isAllowedToPlace($block)) {
								$event->getPlayer()->getLevelNonNull()->setBlock($block, BlockFactory::get(BlockIds::GOLD_BLOCK));
							}
							foreach ($block->getAllSides() as $b) {
								if (($b->getId() === BlockIds::AIR) && !in_array($b, $first, true) && BlockManager::isAllowedToPlace($b)) {
									$event->getPlayer()->getLevelNonNull()->setBlock($b, BlockFactory::get(BlockIds::CONCRETE, $p->getTeam()->getColor()));
								}
							}
						}

						$new = $event->getPlayer()->getInventory()->getItemInHand();
						$new->pop();
						$event->getPlayer()->getInventory()->setItemInHand($new);

						$event->setCancelled();
					}
				}
				return;
			}
			if (ShopManager::use($event->getPlayer(), $event->getBlock())) {
				$event->setCancelled();
			} elseif($p->canUseItem()){
				if ($event->getItem()->getId() === ItemIds::GLOWSTONE_DUST) {
					$event->getPlayer()->teleport(PlayerManager::get($event->getPlayer()->getName())->getTeam()->getSpawn());
					$event->setCancelled();

					$new = $event->getPlayer()->getInventory()->getItemInHand();
					$new->pop();
					$event->getPlayer()->getInventory()->setItemInHand($new);
					$p->useItem();
				} elseif ($event->getItem()->getId() === ItemIds::BRICK_BLOCK) {
					foreach (VoxelRayTrace::betweenPoints($event->getPlayer()->subtract(0, 1, 0), $event->getPlayer()->add($event->getPlayer()->getDirectionVector()->multiply(20))->add(0, 1)) as $block) {
						if (($event->getBlock()->getLevelNonNull()->getBlock($block)->getId() === BlockIds::AIR) && BlockManager::isAllowedToPlace($block)) {
							$event->getPlayer()->getLevelNonNull()->setBlock($block, BlockFactory::get(BlockIds::CONCRETE, $p->getTeam()->getColor()));
						}
					}
					$event->setCancelled();

					$new = $event->getPlayer()->getInventory()->getItemInHand();
					$new->pop();
					$event->getPlayer()->getInventory()->setItemInHand($new);
					$p->useItem();
				}
			}
		} elseif (HudManager::handle($event->getPlayer())) {
			$event->setCancelled();
		}
	}

	/**
	 * @param BlockPlaceEvent $event
	 */
	public function onPlace(BlockPlaceEvent $event): void
	{
		$block = $event->getBlock();
		if (!BlockManager::isAllowedToPlace($block)) {
			$event->setCancelled();
		} elseif ($block instanceof TNT) {
			$block->ignite();
			$event->setCancelled();

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
			$bed = $event->getBlock()->getLevelNonNull()->getTile($event->getBlock());
			if ($bed instanceof BedTile && !TeamManager::get($bed->getColor())->destroyBed($event->getPlayer()->getName())) {
				$event->setCancelled();
			}
			$event->setDrops([]);
			return;
		}

		if (!BlockManager::isAllowedToBreak($event->getBlock())) {
			$event->setCancelled();
		}
	}

	/**
	 * @param CraftItemEvent $event
	 */
	public function onCraft(CraftItemEvent $event): void
	{
		$event->setCancelled();
	}

	/**
	 * @param PlayerExhaustEvent $event
	 */
	public function onExhaust(PlayerExhaustEvent $event): void
	{
		$event->setCancelled();
	}

	/**
	 * @param EntityExplodeEvent $event
	 */
	public function onExplode(EntityExplodeEvent $event): void
	{
		$event->setBlockList(array_filter($event->getBlockList(), static function ($block) {
			return BlockManager::isAllowedToBreak($block) and !$block instanceof Bed;
		}));
	}

	/**
	 * @param PlayerItemConsumeEvent $event
	 */
	public function onConsume(PlayerItemConsumeEvent $event): void
	{
		$item = $event->getItem();
		if ($item instanceof Potion) {
			$event->setCancelled();
			$event->getPlayer()->getInventory()->removeItem($item);
			foreach ($item->getAdditionalEffects() as $effect) {
				$event->getPlayer()->addEffect($effect);
			}
		}
	}


	/**
	 * @param PlayerBucketEmptyEvent $event
	 */
	public function onBucketEmpty(PlayerBucketEmptyEvent $event): void
	{
		$event->setCancelled();
		$event->getPlayer()->getInventory()->setItemInHand(ItemFactory::get(ItemIds::AIR, 0, 0));
		$event->getBlockClicked()->getLevelNonNull()->setBlock($event->getBlockClicked(), BlockFactory::get(BlockIds::WATER));
		BedWars::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(static function (int $currentTick) use ($event) : void {
			$event->getBlockClicked()->getLevelNonNull()->setBlock($event->getBlockClicked(), BlockFactory::get(BlockIds::AIR));
		}), 20);
	}

	/**
	 * @param BlockSpreadEvent $event
	 */
	public function onSpread(BlockSpreadEvent $event): void
	{
		$event->setCancelled();
	}

	/**
	 * @param PlayerBedEnterEvent $event
	 */
	public function onSleep(PlayerBedEnterEvent $event): void
	{
		$event->setCancelled();
	}

	/**
	 * @param InventoryOpenEvent $event
	 */
	public function onOpen(InventoryOpenEvent $event): void
	{
		$inventory = $event->getInventory();
		if ($inventory instanceof ChestInventory && (($p = PlayerManager::get($event->getPlayer()->getName()))->isPlayer())) {
			$name = explode(" ", $inventory->getHolder()->getName())[0];
			try {
				$team = TeamManager::get(TeamColor::fromName($name));
				if ($p->getTeam() !== $team && ($team->hasBed() || $team->getAlivePlayers() > 0)) {
					$event->setCancelled();
				}
			} catch (UnexpectedValueException $exception) {
			}
		}
	}

	/**
	 * @param PlayerChatEvent $event
	 * @priority HIGHEST
	 */
	public function onChat(PlayerChatEvent $event): void
	{
		$event->setCancelled();

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