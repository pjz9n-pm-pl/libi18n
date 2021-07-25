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

final class LangHolder
{
    /** @var LangInstance */
    private static $lang;

    public static function init(LangInstance $lang): void
    {
        self::$lang = $lang;
    }

    public static function get(): LangInstance
    {
        return self::$lang;
    }

    /**
     * Wrapper method for easy calling
     *
     * @see LangInstance::translate()
     */
    public static function t($key, array $parameters = [], ?string $class = null, bool $prefixOnly = false, bool $processTextFormat = true): string
    {
        return self::$lang->translate($key, $parameters, $class ?? (debug_backtrace()[1]["class"] ?? null), $prefixOnly, $processTextFormat);
    }

    private function __construct()
    {
        //NOOP
    }
}
