<?php

namespace BedWars\team;

use BedWars\generator\Generator;
use BedWars\Messages;
use BedWars\player\BedWarsPlayer;
use BedWars\player\PlayerManager;
use BedWars\ScoreboardHandler;
use BedWars\shop\Armor;
use BedWars\shop\item\ItemManager;
use BedWars\Stats;
use BedWars\utils\TeamColor;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;

class Team
{
	/**
	 * @var BedWarsPlayer[]
	 */
	private array $players;
	private int $color;
	private bool $bed = true;
	private Vector3 $spawn;

	/**
	 * Team constructor.
	 * @param BedWarsPlayer[] $players
	 * @param int             $color
	 * @param array           $data
	 */
	public function __construct(array $players, int $color, array $data)
	{
		if (count($players) <= 0) {
			$this->bed = false;
		}
		$this->players = $players;
		$this->color = $color;
		$this->spawn = $data["spawn"]->add(0.5, 0, 0.5);
		new Generator(Generator::TYPE_IRON, $data["spawner"]);
		foreach ($players as $player) {
			$player->load($this);
			if (($p = Server::getInstance()->getPlayerExact($player->getName())) instanceof Player) {
				$p->setNameTag(TeamColor::getChatFormat($this) . $p->getName());
				$p->teleport($this->spawn);
				$p->setGamemode(GameMode::SURVIVAL());
				$player->setStatus(BedWarsPlayer::ALIVE);
				foreach (ItemManager::getPermanentItems() as $item) {
					$item->downgrade($p);
				}
				$p->getArmorInventory()->setContents(Armor::getTier(Armor::getTierOf($player->getName()), $player->getName()));
			}
		}
	}

	/**
	 * @return int
	 */
	public function getColor(): int
	{
		return $this->color;
	}

	/**
	 * @return bool
	 */
	public function hasBed(): bool
	{
		return $this->bed;
	}

	/**
	 * @return BedWarsPlayer[]
	 */
	public function getPlayers(): array
	{
		return $this->players;
	}

	/**
	 * @return int
	 */
	public function getAlivePlayers(): int
	{
		return count(array_filter($this->players, static function ($player) {
			return $player->getStatus() !== BedWarsPlayer::SPECTATOR;
		}));
	}

	/**
	 * @return Vector3
	 */
	public function getSpawn(): Vector3
	{
		return $this->spawn;
	}

	/**
	 * @param string $player
	 * @return bool
	 */
	public function destroyBed(string $player): bool
	{
		if (!$this->hasBed() || PlayerManager::get($player)->getTeam() === $this) {
			return false;
		}
		Stats::$beds->changeScore($player, 1);
		$this->bed = false;
		$pk = new PlaySoundPacket();
		$pk->soundName = "mob.wither.death";
		$pk->pitch = 1;
		$pk->volume = 1000;
		foreach (Server::getInstance()->getOnlinePlayers() as $p) {
			$pk->x = $p->getPosition()->getX();
			$pk->y = $p->getPosition()->getY();
			$pk->z = $p->getPosition()->getZ();
			$p->getNetworkSession()->sendDataPacket($pk);
		}
		foreach ($this->getPlayers() as $p) {
			Messages::send($p->getName(), "bed-destroyed", ["{player}" => TeamColor::getChatFormat(PlayerManager::get($player)->getTeam()) . $player], false);
			Messages::title($p->getName(), "destroyed");
		}
		Messages::send(array_filter(Server::getInstance()->getOnlinePlayers(), function ($p) {
			return PlayerManager::get($p->getName())->getTeam() !== $this;
		}), "bed-destroyed-other", ["{team}" => TeamColor::getChatFormat($this) . TeamColor::getName($this), "{player}" => TeamColor::getChatFormat(PlayerManager::get($player)->getTeam()) . $player], false);
		ScoreboardHandler::update();
		return true;
	}
}