<?php

namespace BedWars\player;

use BedWars\HudManager;
use BedWars\Messages;
use BedWars\ScoreboardHandler;
use BedWars\shop\Armor;
use BedWars\shop\item\BedWarsItem;
use BedWars\shop\item\ItemManager;
use BedWars\Stats;
use BedWars\team\Team;
use BedWars\utils\TeamColor;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;

class BedWarsPlayer
{
	public const SPECTATOR = 0;
	public const ALIVE = 1;
	public const DEAD = 2;

	private string $name;
	private Team $team;
	private int $status = self::SPECTATOR;
	private int $deadTicks = 0;
	private string $lastDamager;
	private array $slots = [
		"Sword" => 0,
		"Pickaxe" => 1,
	];
	private int $cooldown = 0;

	/**
	 * BedWarsPlayer constructor.
	 * @param string $name
	 */
	public function __construct(string $name)
	{
		$this->name = $name;
	}

	public function load(Team $team): void
	{
		$this->team = $team;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return bool
	 */
	public function isPlayer(): bool
	{
		return isset($this->team);
	}

	/**
	 * @return Team
	 */
	public function getTeam(): Team
	{
		return $this->team;
	}

	/**
	 * @return int
	 */
	public function getStatus(): int
	{
		return $this->status;
	}

	/**
	 * @param int $status
	 * @return int
	 */
	public function setStatus(int $status): int
	{
		return $this->status = $status;
	}

	public function kill(): void
	{
		if (($player = Server::getInstance()->getPlayerExact($this->name)) instanceof Player) {
			$player->setHealth(20);
			$player->getEffects()->clear();
			Stats::$deaths->changeScore($this->name, 1);

			if ($this->team->hasBed()) {
				if (isset($this->lastDamager)) {
					Stats::$kills->changeScore($this->lastDamager, 1);
					Stats::$killCounter[$this->lastDamager] = (Stats::$killCounter[$this->lastDamager] ?? 0) + 1;
					Messages::send(Server::getInstance()->getOnlinePlayers(), "kill", ["{killer}" => TeamColor::getChatFormat(PlayerManager::get($this->lastDamager)->getTeam()) . $this->lastDamager, "{player}" => TeamColor::getChatFormat($this->getTeam()) . $this->name]);
				} else {
					Messages::send(Server::getInstance()->getOnlinePlayers(), "death", ["{player}" => TeamColor::getChatFormat($this->getTeam()) . $this->name]);
				}
			} else {
				$this->setStatus(self::SPECTATOR);
				if (isset($this->lastDamager)) {
					Stats::$kills->changeScore($this->lastDamager, 1);
					Stats::$finalKills->changeScore($this->lastDamager, 1);
					Stats::$killCounter[$this->lastDamager] = (Stats::$killCounter[$this->lastDamager] ?? 0) + 1;
					Messages::send(Server::getInstance()->getOnlinePlayers(), "final-kill", ["{killer}" => TeamColor::getChatFormat(PlayerManager::get($this->lastDamager)->getTeam()) . $this->lastDamager, "{player}" => TeamColor::getChatFormat($this->getTeam()) . $this->name]);
				} else {
					Messages::send(Server::getInstance()->getOnlinePlayers(), "final-death", ["{player}" => TeamColor::getChatFormat($this->getTeam()) . $this->name]);
				}
				ScoreboardHandler::update();
			}
			unset($this->lastDamager);
			foreach ($player->getInventory()->getContents() as $item) {
				if (!ItemManager::get($item->getName()) instanceof BedWarsItem) {
					$player->getWorld()->dropItem($player->getPosition(), $item);
				}
				$player->getInventory()->removeItem($item);
			}
			$player->getArmorInventory()->setContents([]);
			$player->setGamemode(GameMode::SPECTATOR());
		}
	}

	public function respawn(): void
	{
		if (($player = Server::getInstance()->getPlayerExact($this->name)) instanceof Player) {
			if ($this->team->hasBed()) {
				$this->deadTicks = 5;
				$this->setStatus(self::DEAD);
			} else {
				HudManager::send($player);
				$player->setGamemode(GameMode::SPECTATOR());
			}
			$player->teleport($player->getWorld()->getSpawnLocation());
		}
	}

	public function tick(): void
	{
		if ($this->status === self::DEAD) {
			if (--$this->deadTicks < 0) {
				if (($player = Server::getInstance()->getPlayerExact($this->getName())) instanceof Player) {
					$player->teleport($this->team->getSpawn());
					$player->setGamemode(GameMode::SURVIVAL());
					$player->setHealth(20);
					$player->getEffects()->clear();
					$this->status = self::ALIVE;
					foreach (ItemManager::getPermanentItems() as $item) {
						$item->downgrade($player);
					}
					$player->getArmorInventory()->setContents(Armor::getTier(Armor::getTierOf($player->getName()), $player->getName()));
				} else {
					$this->status = self::SPECTATOR;
				}
			} else {
				Messages::title($this->name, "die", ["{seconds}" => $this->deadTicks + 1], "respawn-in");
			}
		}
	}

	/**
	 * @param string $player
	 */
	public function damage(string $player): void
	{
		$this->lastDamager = $player;
	}

	/**
	 * @param string $id
	 * @param int    $slot
	 */
	public function setSlot(string $id, int $slot): void
	{
		$this->slots[$id] = $slot;
	}

	/**
	 * @param string $id
	 * @return int
	 */
	public function getSlot(string $id): int
	{
		return $this->slots[$id];
	}

	/**
	 * @return bool
	 */
	public function canUseItem(): bool
	{
		return $this->cooldown < time();
	}

	public function useItem(): void
	{
		$this->cooldown = time() + 2;
	}
}