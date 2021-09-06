<?php

namespace BedWars;

use BedWars\generator\Generator;
use BedWars\generator\GeneratorManager;
use BedWars\player\BedWarsPlayer;
use BedWars\player\PlayerManager;
use BedWars\shop\ShopManager;
use BedWars\team\Team;
use BedWars\team\TeamManager;
use BedWars\utils\Compass;
use BedWars\utils\TeamColor;
use Exception;
use LobbySystem\server\VirtualServer;
use LobbySystem\utils\WorldLoader;
use pocketmine\block\tile\Sign;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;
use pocketmine\world\World;
use UnexpectedValueException;

class BedWars extends VirtualServer
{
	public const PRE_GAME = 0;
	public const PLAYING = 1;
	public const AFTER_GAME = 2;

	private static int $status = self::PRE_GAME;
	private static TaskHandler $ticker;

	public function onInit(): void
	{
		Stats::load();
		$world = Server::getInstance()->getWorldManager()->getDefaultWorld();
		if ($world instanceof World) {
			$world->setTime(World::TIME_NOON);
			$world->stopTime();
		}
		Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
			$this->start();
		}), 20);
	}

	public function start(): void
	{
		self::$status = self::PLAYING;
		TeamManager::start();
		ScoreboardHandler::update();
		self::$ticker = Loader::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
			$this->tick();
		}), 20);
	}

	public function tick(): void
	{
		try {
			$winner = $this->getWinner();
		} catch (Exception) {
		}

		if (isset($winner)) {
			$this->end($winner);
		}

		GeneratorManager::tick();
		PlayerManager::tick();
		Compass::update();

		if (Server::getInstance()->getTick() % 1200 === 0) {
			ScoreboardHandler::update();
		}
	}

	/**
	 * @return Team
	 */
	public function getWinner(): Team
	{
		foreach (TeamManager::getTeams() as $team) {
			if ($team->hasBed() || $team->getAlivePlayers() > 0) {
				if (isset($alive)) {
					throw new UnexpectedValueException("More than one team is alive");
				}

				$alive = $team;
			}
		}
		return $alive ?? TeamManager::getTeams()[array_rand(TeamManager::getTeams())];
	}

	/**
	 * @param Team $winner
	 */
	public function end(Team $winner): void
	{
		self::$ticker->cancel();
		self::$status = self::AFTER_GAME;
		$winners = [];
		foreach (TeamManager::getTeams() as $team) {
			if ($team === $winner) {
				foreach ($team->getPlayers() as $player) {
					Stats::$wins->changeScore($player->getName(), 1);
					if ($player->getStatus() !== BedWarsPlayer::ALIVE && !$team->hasBed()) {
						Stats::$finalDeaths->changeScore($player->getName(), 1);
					}
					Messages::title($player->getName(), "victory");
					$winners[] = $player->getName();
				}
			} else {
				foreach ($team->getPlayers() as $player) {
					Stats::$losses->changeScore($player->getName(), 1);
					//leaving shouldn't be an option
					Stats::$finalDeaths->changeScore($player->getName(), 1);
					Messages::title($player->getName(), "game-over");
				}
			}
		}
		foreach (Server::getInstance()->getOnlinePlayers() as $player) {
			PlayerManager::get($player->getName())->setStatus(BedWarsPlayer::SPECTATOR);
			HudManager::send($player);
		}
		arsort(Stats::$killCounter);
		$killtop = array_map(static function ($player, $score) {
			return TeamColor::getChatFormat(PlayerManager::get($player)->getTeam()) . $player . " §r- " . $score;
		}, array_keys(Stats::$killCounter), array_values(Stats::$killCounter));
		Messages::send(Server::getInstance()->getOnlinePlayers(), "end", ["{team}" => TeamColor::getChatFormat($winner) . TeamColor::getName($winner), "{winners}" => implode(", ", $winners), "{killtop}" => implode("\n", array_slice($killtop, 0, 3))], false);
		Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(static function (): void {
			foreach (Server::getInstance()->getOnlinePlayers() as $player) {
				Server::getInstance()->dispatchCommand($player, "play " . BedWars::getGamemodeId());
			}
		}), 20 * 15);
		Loader::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
			Server::getInstance()->shutdown();
		}), 20 * 25);
	}

	/**
	 * @return int
	 */
	public static function getStatus(): int
	{
		return self::$status;
	}

	public static function scanMap(): void
	{
		$world = Server::getInstance()->getWorldManager()->getDefaultWorld();
		if (!$world instanceof World) {
			Server::getInstance()->getLogger()->critical("No Level found! Stopping ASAP...");
			Server::getInstance()->shutdown();
			return;
		}

		foreach (WorldLoader::getTiles($world) as $tile) {
			if ($tile instanceof Sign) {
				switch (strtoupper($tile->getText()->getLine(0))) {
					case "SPAWN":
						TeamManager::cache(["team " . TeamColor::fromName($tile->getText()->getLine(1)) => ["spawn" => $tile->getPosition()]]);
						$world->setBlock($tile->getPosition(), VanillaBlocks::AIR());
						for ($x = -3; $x <= 3; $x++) {
							for ($z = -3; $z <= 3; $z++) {
								for ($y = -1; $y <= 3; $y++) {
									BlockManager::deny($tile->getPosition()->addVector(new Vector3($x, $y, $z)));
								}
							}
						}
						break;
					case "SPAWNER":
						switch (strtoupper($tile->getText()->getLine(1))) {
							case "EMERALD":
								new Generator(Generator::TYPE_EMERALD, $tile->getPosition());
								break;
							case "DIAMOND":
								new Generator(Generator::TYPE_DIAMONDS, $tile->getPosition());
								break;
							default:
								TeamManager::cache(["team " . TeamColor::fromName($tile->getText()->getLine(1)) => ["spawner" => $tile]]);
						}
						$world->setBlock($tile->getPosition(), VanillaBlocks::AIR());
						for ($x = -2; $x <= 2; $x++) {
							for ($z = -2; $z <= 2; $z++) {
								for ($y = -1; $y <= 3; $y++) {
									BlockManager::deny($tile->getPosition()->addVector(new Vector3($x, $y, $z)));
								}
							}
						}
						break;
					case "SHOP":
						ShopManager::read($tile);
						for ($x = -1; $x <= 1; $x++) {
							for ($z = -1; $z <= 1; $z++) {
								for ($y = -1; $y <= 1; $y++) {
									BlockManager::deny($tile->getPosition()->addVector(new Vector3($x, $y, $z)));
								}
							}
						}
						break;
					case "UPGRADER":
						ShopManager::read($tile);
						$world->setBlock($tile->getPosition(), VanillaBlocks::AIR());
						for ($x = -1; $x <= 1; $x++) {
							for ($z = -1; $z <= 1; $z++) {
								for ($y = -1; $y <= 1; $y++) {
									BlockManager::deny($tile->getPosition()->addVector(new Vector3($x, $y, $z)));
								}
							}
						}
						break;
					case "UTILITY":
						ShopManager::read($tile);
						$world->setBlock($tile->getPosition(), VanillaBlocks::AIR());
						for ($x = -2; $x <= 2; $x++) {
							for ($z = -2; $z <= 2; $z++) {
								for ($y = -1; $y <= 3; $y++) {
									BlockManager::deny($tile->getPosition()->addVector(new Vector3($x, $y, $z)));
								}
							}
						}
				}
			}
		}
	}
}