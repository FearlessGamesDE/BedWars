<?php

namespace BedWars\utils;

use BedWars\team\Team;
use pocketmine\utils\Color;
use pocketmine\utils\TextFormat;
use UnexpectedValueException;

class TeamColor
{
	public const ORANGE = 1;
	public const AQUA = 3;
	public const YELLOW = 4;
	public const GREEN = 5;
	public const PINK = 6;
	public const GRAY = 8;
	public const BLUE = 11;
	public const RED = 14;

	/**
	 * @param int $teamCount
	 * @return int[]
	 */
	public static function getColors(int $teamCount): array
	{
		switch ($teamCount) {
			case 2:
				return [self::BLUE, self::RED];
			case 4:
				return [self::BLUE, self::GREEN, self::RED, self::YELLOW];
			case 8:
				return [self::BLUE, self::AQUA, self::GREEN, self::PINK, self::RED, self::ORANGE, self::YELLOW, self::GRAY];
		}
		throw new UnexpectedValueException("Unknown Teamcount " . $teamCount);
	}

	/**
	 * @param Team $team
	 * @return string
	 */
	public static function getChatFormat(Team $team): string
	{
		switch ($team->getColor()) {
			case self::ORANGE:
				return TextFormat::GOLD;
			case self::AQUA:
				return TextFormat::AQUA;
			case self::YELLOW:
				return TextFormat::YELLOW;
			case self::GREEN:
				return TextFormat::GREEN;
			case self::PINK:
				return TextFormat::LIGHT_PURPLE;
			case self::GRAY:
				return TextFormat::GRAY;
			case self::BLUE:
				return TextFormat::BLUE;
			case self::RED:
				return TextFormat::RED;
		}
		throw new UnexpectedValueException("Invalid Team " . $team->getColor());
	}

	/**
	 * @param Team $team
	 * @return string
	 */
	public static function getName(Team $team): string
	{
		switch ($team->getColor()) {
			case self::ORANGE:
				return "Orange";
			case self::AQUA:
				return "Aqua";
			case self::YELLOW:
				return "Yellow";
			case self::GREEN:
				return "Green";
			case self::PINK:
				return "Pink";
			case self::GRAY:
				return "Gray";
			case self::BLUE:
				return "Blue";
			case self::RED:
				return "Red";
		}
		throw new UnexpectedValueException("Invalid Team " . $team->getColor());
	}

	/**
	 * @param string $team
	 * @return string
	 */
	public static function getNormalName(string $team): string
	{
		switch (self::fromName($team)) {
			case self::ORANGE:
				return "Orange";
			case self::AQUA:
				return "Aqua";
			case self::YELLOW:
				return "Yellow";
			case self::GREEN:
				return "Green";
			case self::PINK:
				return "Pink";
			case self::GRAY:
				return "Gray";
			case self::BLUE:
				return "Blue";
			case self::RED:
				return "Red";
		}
		throw new UnexpectedValueException("Invalid Team " . $team);
	}

	/**
	 * @param string $color
	 * @return int
	 */
	public static function fromName(string $color): int
	{
		if (defined(self::class . "::" . mb_strtoupper($color))) {
			return constant(self::class . "::" . mb_strtoupper($color));
		}
		throw new UnexpectedValueException("Invalid Team " . $color);
	}

	/**
	 * @param Team $team
	 * @return Color
	 */
	public static function getDyeColor(Team $team): Color
	{
		switch ($team->getColor()) {
			case self::ORANGE:
				return Color::fromRGB(0xFFAA00);
			case self::AQUA:
				return Color::fromRGB(0x55FFFF);
			case self::YELLOW:
				return Color::fromRGB(0xFFFF55);
			case self::GREEN:
				return Color::fromRGB(0x55FF55);
			case self::PINK:
				return Color::fromRGB(0xFF55FF);
			case self::GRAY:
				return Color::fromRGB(0xAAAAAA);
			case self::BLUE:
				return Color::fromRGB(0x5555FF);
			case self::RED:
				return Color::fromRGB(0xFF5555);
		}
		throw new UnexpectedValueException("Invalid Team " . $team->getColor());
	}
}