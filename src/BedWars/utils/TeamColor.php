<?php

namespace BedWars\utils;

use BedWars\team\Team;
use pocketmine\block\utils\DyeColor;
use pocketmine\color\Color;
use pocketmine\item\Dye;
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
	 * @return DyeColor
	 */
	public static function getDyeColor(Team $team): DyeColor
	{
		switch ($team->getColor()) {
			case self::ORANGE:
				return DyeColor::ORANGE();
			case self::AQUA:
				return DyeColor::CYAN();
			case self::YELLOW:
				return DyeColor::YELLOW();
			case self::GREEN:
				return DyeColor::LIME();
			case self::PINK:
				return DyeColor::PINK();
			case self::GRAY:
				return DyeColor::LIGHT_GRAY();
			case self::BLUE:
				return DyeColor::BLUE();
			case self::RED:
				return DyeColor::RED();
		}
		throw new UnexpectedValueException("Invalid Team " . $team->getColor());
	}
}