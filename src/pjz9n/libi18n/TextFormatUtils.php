<?php

/*
 * Copyright (c) 2021 PJZ9n.
 *
 * This file is part of libi18n.
 *
 * libi18n is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * libi18n is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with libi18n. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace pjz9n\libi18n;

use pocketmine\utils\TextFormat;

final class TextFormatUtils
{
    /**
     * Returns a formats
     * For example:
     * §a§b§lHello§o => ["§a", "§b", "§l", "§o"]
     *
     * @return string[]
     */
    public static function getFormats(string $text): array
    {
        preg_match_all("/" . TextFormat::ESCAPE . "[0-9a-fk-or]/", $text, $matches);
        return $matches[0];
    }

    /**
     * Returns a last valid formats
     * For example:
     * §a§b§lHello§o => ["§b", "§o"]
     *
     * @return string[]
     */
    public static function getLastValidFormats(string $text): array
    {
        $formats = self::getFormats($text);

        $validColor = "";
        $validControl = "";

        foreach ($formats as $format) {
            if (preg_match("/" . TextFormat::ESCAPE . "[0-9a-f]/", $format) === 1) {
                $validColor = $format;
            } else if (preg_match("/" . TextFormat::ESCAPE . "[k-o]/", $format) === 1) {
                $validControl = $format;
            } else if (preg_match("/" . TextFormat::ESCAPE . "r/", $format) === 1) {
                $validColor = "";
                $validControl = "";
            }
        }

        return [$validColor, $validControl];
    }

    private function __construct()
    {
        //NOOP
    }
}
