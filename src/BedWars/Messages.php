<?php

namespace BedWars;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class Messages
{
	private static $messages = [];

	public static function load(string $dir): void
	{
		self::$messages = (new Config($dir . "messages.yml", Config::YAML))->getAll();
	}

	/**
	 * @param array|string|Player $players
	 * @param string $id
	 * @param array|string $replace
	 * @param bool $prefix
	 */
	public static function send($players, string $id, $replace = [], $prefix = true): void
	{
		if (is_array($players)) {
			foreach ($players as $player) {
				if ($player instanceof Player) {
					$player->sendMessage(($prefix ? self::translate("prefix") : "") . self::replace($id, $replace));
				} elseif (($player = Server::getInstance()->getPlayerExact($player)) instanceof Player) {
					$player->sendMessage(($prefix ? self::translate("prefix") : "") . self::replace($id, $replace));
				}
			}
		} else {
			self::send([$players], $id, $replace, $prefix);
		}
	}

	/**
	 * @param array|string $players
	 * @param string $id
	 * @param array|string $replace
	 * @param string $subtitle
	 */
	public static function title($players, string $id, $replace = [], string $subtitle = ""): void
	{
		if (is_array($players)) {
			foreach ($players as $player) {
				if (($player = Server::getInstance()->getPlayerExact($player)) instanceof Player) {
					$player->sendTitle(self::replace($id, $replace), self::replace($subtitle, $replace), 1, 20, 1);
				}
			}
		} else {
			self::title([$players], $id, $replace, $subtitle);
		}
	}

	/**
	 * @param string $id
	 * @param array|string $replace
	 * @return string
	 */
	public static function replace(string $id, $replace = []): string
	{
		if (is_array($replace)) {
			return str_replace(array_keys($replace), array_values($replace), self::translate($id));
		}
		return str_replace("{player}", $replace, self::translate($id));
	}

	public static function translate(string $id)
	{
		return self::$messages[$id] ?? $id;
	}
}