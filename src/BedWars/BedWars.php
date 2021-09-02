<?php

namespace BedWars;

use alemiz\sga\StarGateAtlantis;
use BedWars\generator\Generator;
use BedWars\generator\GeneratorManager;
use BedWars\player\BedWarsPlayer;
use BedWars\player\PlayerManager;
use BedWars\shop\item\ItemManager;
use BedWars\shop\ShopManager;
use BedWars\team\Team;
use BedWars\team\TeamManager;
use BedWars\utils\Bed;
use BedWars\utils\Compass;
use BedWars\utils\NumberGenerator;
use BedWars\utils\TeamColor;
use Exception;
use LobbySystem\server\VirtualServer;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;
use pocketmine\tile\Chest;
use pocketmine\tile\Sign;
use UnexpectedValueException;

class BedWars extends VirtualServer
{
	public const PRE_GAME = 0;
	public const PLAYING = 1;
	public const AFTER_GAME = 2;

	/**
	 * @var int
	 */
	private static $status = self::PRE_GAME;
	/**
	 * @var TaskHandler
	 */
	private static $ticker;
	/**
	 * @var string
	 */
	private static $id;

	public function onInit(): void
	{
		// TODO: Implement onInit() method.
	}

	/**
	 * @return string
	 */
	public static function getId(): string
	{
		return self::$id;
	}

	/** @noinspection PhpUnusedParameterInspection */
	public function start(): void
	{
		self::$status = self::PLAYING;
		TeamManager::start();
		ScoreboardHandler::update();
		self::$ticker = $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $currentTick): void {
			$this->tick();
		}), 20);
	}

	public function tick(): void
	{
		try {
			$winner = $this->getWinner();
		} catch (Exception $exception) {

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
	 * @noinspection PhpUnusedParameterInspection
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
		$this->getScheduler()->scheduleDelayedTask(new ClosureTask(static function (int $currentTick): void {
			foreach (Server::getInstance()->getOnlinePlayers() as $player) {
				Server::getInstance()->dispatchCommand($player, "play " . BedWars::getId());
				StarGateAtlantis::getInstance()->transferPlayer($player, "lobby");
			}
		}), 20 * 15);
		$this->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick): void {
			$this->getServer()->shutdown();
		}), 20 * 20);
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
		$level = Server::getInstance()->getDefaultLevel();
		if (!$level instanceof Level) {
			Server::getInstance()->getLogger()->critical("No Level found! Stopping ASAP...");
			Server::getInstance()->shutdown();
			return;
		}

		for ($x = -10; $x <= 10; $x++) {
			for ($z = -10; $z <= 10; $z++) {
				$level->loadChunk($x, $z);
			}
		}
		foreach ($level->getTiles() as $tile) {
			if ($tile instanceof Sign) {
				switch (strtoupper($tile->getLine(0))) {
					case "SPAWN":
						TeamManager::cache(["team " . TeamColor::fromName($tile->getLine(1)) => ["spawn" => $tile->asVector3()]]);
						$level->setBlock($tile, BlockFactory::get(Block::AIR));
						for ($x = -3; $x <= 3; $x++) {
							for ($z = -3; $z <= 3; $z++) {
								for ($y = -1; $y <= 3; $y++) {
									BlockManager::deny($tile->add(new Vector3($x, $y, $z)));
								}
							}
						}
						break;
					case "SPAWNER":
						switch (strtoupper($tile->getLine(1))) {
							case "EMERALD":
								new Generator(Generator::TYPE_EMERALD, $tile);
								break;
							case "DIAMOND":
								new Generator(Generator::TYPE_DIAMONDS, $tile);
								break;
							default:
								TeamManager::cache(["team " . TeamColor::fromName($tile->getLine(1)) => ["spawner" => $tile]]);
						}
						$level->setBlock($tile, BlockFactory::get(Block::AIR));
						for ($x = -2; $x <= 2; $x++) {
							for ($z = -2; $z <= 2; $z++) {
								for ($y = -1; $y <= 3; $y++) {
									BlockManager::deny($tile->add(new Vector3($x, $y, $z)));
								}
							}
						}
						break;
					case "SHOP":
						ShopManager::read($tile);
						for ($x = -1; $x <= 1; $x++) {
							for ($z = -1; $z <= 1; $z++) {
								for ($y = -1; $y <= 1; $y++) {
									BlockManager::deny($tile->add(new Vector3($x, $y, $z)));
								}
							}
						}
						break;
					case "UPGRADER":
						ShopManager::read($tile);
						$level->setBlock($tile, BlockFactory::get(Block::AIR));
						for ($x = -1; $x <= 1; $x++) {
							for ($z = -1; $z <= 1; $z++) {
								for ($y = -1; $y <= 1; $y++) {
									BlockManager::deny($tile->add(new Vector3($x, $y, $z)));
								}
							}
						}
						break;
					case "UTILITY":
						ShopManager::read($tile);
						$level->setBlock($tile, BlockFactory::get(Block::AIR));
						for ($x = -2; $x <= 2; $x++) {
							for ($z = -2; $z <= 2; $z++) {
								for ($y = -1; $y <= 3; $y++) {
									BlockManager::deny($tile->add(new Vector3($x, $y, $z)));
								}
							}
						}
				}
			}
		}
	}
}