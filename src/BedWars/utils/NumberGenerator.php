<?php

namespace BedWars\utils;

use UnexpectedValueException;

class NumberGenerator
{
	/**
	 * @param string $number
	 * @return int
	 */
	public static function toInt(string $number)
	{
		$result = 0;
		foreach (["thousand", "hundred"] as $faktor) {
			$map = explode($faktor, $number);
			if (isset($map[2])) {
				throw new UnexpectedValueException("Couldn't format number " . $number);
			}
			if (isset($map[1])) {
				$result += self::toInt($map[0]) * self::findShort($faktor);
				$number = $map[1];
			}else{
				$number = $map[0];
			}
		}
		return $result + self::findShort($number);
	}

	/**
	 * @param string $number
	 * @return int
	 */
	public static function findShort(string $number): int
	{
		$numbers = ["" => 0,
			"one" => 1,
			"two" => 2,
			"three" => 3,
			"four" => 4,
			"five" => 5,
			"six" => 6,
			"seven" => 7,
			"eight" => 8,
			"nine" => 9,
			"ten" => 10,
			"eleven" => 11,
			"twelve" => 12,
			"thirteen" => 13,
			"fourteen" => 14,
			"fifteen" => 15,
			"sixteen" => 16,
			"seventeen" => 17,
			"eighteen" => 18,
			"nineteen" => 19,
			"twenty" => 20,
			"thirty" => 30,
			"forty" => 40,
			"fifty" => 50,
			"sixty" => 60,
			"seventy" => 70,
			"eighty" => 80,
			"ninety" => 90,
			"hundred" => 100,
			"thousand" => 1000];
		if (isset($numbers[$number])) {
			return $numbers[$number];
		}
		$break = false;
		$len = strlen($number);
		for ($i = 1; $i < $len; $i++) {
			if (isset($numbers[substr($number, 0, $i)])) {
				if($break){
					break;
				}

				$break = true;
			}
		}
		if (isset($numbers[substr($number, $i)])) {
			return $numbers[substr($number, 0, $i)] + $numbers[substr($number, $i)];
		}
		throw new UnexpectedValueException("Couldn't format number " . $number);
	}
}