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

/**
 * An object that contains the information needed for translation
 * A class like pocketmine\lang\TranslationContainer
 */
class Translation
{
    /** @var string */
    private $key;

    /** @var array */
    private $params;

    public function __construct(string $key, array $params = [])
    {
        $this->key = $key;
        $this->params = $params;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
