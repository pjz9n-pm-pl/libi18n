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

use Logger;
use pocketmine\utils\TextFormat;

/**
 * Object containing translation data
 * A class like pocketmine\lang\BaseLang
 */
class LangInstance
{
    /** @var string Translation key regular expression */
    public const KEY_REGEX = "/%?(.+)/";

    /** @var string Prefix for translation key */
    public const KEY_PREFIX = "%";

    /** @var string Separator for translation key */
    public const KEY_SEPARATOR = ".";

    /** @var string Parameter regular expression */
    public const PARAMETER_REGEX = "/%{([^}]+)}/";

    /**
     * Returns a list of available languages
     *
     * @param string $localePath The directory path where the locale yml file is located
     *
     * @return string[] language => path
     */
    public static function availableLanguages(string $localePath): array
    {
        $languages = [];
        foreach (scandir($localePath) as $path) {
            $path = realpath($path);
            $info = pathinfo($path);
            if (is_file($path) && $info["extension"] === "yml") {
                $languages[$info["filename"]] = $path;
            }
        }
        return $languages;
    }

    /** @var string */
    private $language;

    /** @var string */
    private $fallbackLanguage;

    /** @var string */
    private $localePath;

    /** @var Logger */
    private $logger;

    /** @var string[] */
    private $texts = [];

    /** @var string[] */
    private $fallbackTexts = [];

    /**
     * @param string $language Language to use
     * @param string $fallbackLanguage Language to use when it does not exist
     * @param string $localePath The directory path where the locale yml file is located
     * @param Logger $logger A logger that outputs information
     */
    public function __construct(string $language, string $fallbackLanguage, string $localePath, Logger $logger)
    {
        $this->language = $language;
        $this->fallbackLanguage = $fallbackLanguage;
        $this->localePath = realpath($localePath);
        $this->logger = $logger;
        //load
        $filePath = $this->localePath . DIRECTORY_SEPARATOR . $this->language . ".yml";
        if (file_exists($filePath)) {
            $this->texts = yaml_parse_file($filePath);
        } else {
            $this->logger->error("Missing required language file $filePath");
        }
        $filePath = $this->localePath . DIRECTORY_SEPARATOR . $this->fallbackLanguage . ".yml";
        if (file_exists($filePath)) {
            $this->fallbackTexts = yaml_parse_file($filePath);
        } else {
            $this->logger->error("Missing required language file $filePath");
        }
        //check required
        if ($this->get("_" . self::KEY_SEPARATOR . "name") === null) {
            $this->logger->error("Required attribute \"_" . self::KEY_SEPARATOR . "name\" is not found");
        }
    }

    /**
     * @param string|Translation $key Translate key or Translation object
     * @param string[]|Translation[] $parameters Associative array of parameters
     * @param class-string|null $class Class used for resolution when relative translation key is specified
     * @param bool $prefixOnly Process only translation keys starting with the prefix (%)
     * @param bool $processTextFormat Whether message textformat do not overwrite parameter textformat
     */
    public function translate($key, array $parameters = [], ?string $class = null, bool $prefixOnly = false, bool $processTextFormat = true): string
    {
        if ($class === null) {
            $class = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]["class"];
        }
        if ($key instanceof Translation) {
            assert($parameters === [], "When using Translation object, \$params are ignored");
            $parameters = $key->getParams();
            $key = $key->getKey();
        }
        //extract real key
        if (($pos = strpos($key, self::KEY_PREFIX)) !== false) {//includes prefix
            preg_match(self::KEY_REGEX, $key, $matches, 0, $pos);
            if (isset($matches[1])) {
                $realKey = $matches[1];
            } else {
                $this->logger->debug("includes prefix and does not match");
                $this->logger->error("Could not find translation key: $key");
                return $key;
            }
        } else if (!$prefixOnly) {
            preg_match(self::KEY_REGEX, $key, $matches);
            if (isset($matches[1])) {
                $realKey = $matches[1];
            } else {
                $this->logger->debug("not includes prefix and does not match");
                $this->logger->error("Could not find translation key: $key");
                return $key;
            }
        } else {
            $this->logger->debug("not includes prefix and does not match (onlyprefix)");
            $this->logger->error("Could not find translation key: $key");
            return $key;
        }
        //calculate format
        $firstHalfKey = "";
        $lastValidFormats = [];
        if ($key !== $realKey) {
            $firstHalfKey = mb_substr($key, 0, mb_strpos($key, $realKey));
            //remove percent
            $percentPosition = strpos($firstHalfKey, "%", -1);//no mb
            if ($percentPosition !== false) {
                $firstHalfKey = substr_replace($firstHalfKey, "", $percentPosition);
            }
            $lastValidFormats = TextFormatUtils::getLastValidFormats($firstHalfKey);
        }
        //resolve relative key
        if (strpos($realKey, self::KEY_SEPARATOR) === 0) {
            $realKey = str_replace("\\", self::KEY_SEPARATOR, $class) . $realKey;
            $this->logger->debug("resolved relative key: $realKey");
        }
        //get the text
        $text = $this->get($realKey);
        if ($text === null) {
            $this->logger->error("Could not find text for key: $realKey");
            return $key;
        }
        //replace params
        preg_match_all(self::PARAMETER_REGEX, $text, $matches);
        $markers = $matches[0];
        $markerKeys = $matches[1];
        foreach ($markers as $k => $marker) {
            $markerKey = $markerKeys[$k];
            if (!array_key_exists($markerKey, $parameters)) {
                $this->logger->warning("Could not find parameter \"$markerKey\" for key: $realKey");
                continue;
            }
            $parameter = $parameters[$markerKey];
            if (!($parameter instanceof Translation) && !is_string($parameter)) {
                $parameter = (string)$parameter;
            }
            if ((is_string($parameter) && $parameter === $key) || ($parameter instanceof Translation && $parameter->getKey() === $key)) {
                $this->logger->warning("A recursive translation has been detected");
                continue;
            }
            if ($processTextFormat) {
                $text = str_replace($marker, TextFormat::RESET . $marker . TextFormat::RESET . implode($lastValidFormats), $text);
            }
            if ($parameter instanceof Translation) {
                $parameter = $this->translate($parameter, [], $class, $prefixOnly, $processTextFormat);
            }
            $text = str_replace($marker, $parameter, $text);
        }
        //complete
        return $firstHalfKey . $text;
    }

    public function getName(): string
    {
        return $this->get("_" . self::KEY_SEPARATOR . "name") ?? "Unknown";
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    private function get(string $key): ?string
    {
        $explodedKey = explode(self::KEY_SEPARATOR, $key);
        $texts = $this->texts;
        $fallbackTexts = $this->fallbackTexts;
        for ($i = 0, $nestCount = count($explodedKey); $i < $nestCount; $i++) {
            if (array_key_exists($explodedKey[$i], $texts)) {
                $texts = $texts[$explodedKey[$i]];
                if ($i === $nestCount - 1) {
                    return $texts;
                }
            }
        }
        for ($i = 0, $nestCount = count($explodedKey); $i < $nestCount; $i++) {
            if (array_key_exists($explodedKey[$i], $fallbackTexts)) {
                $fallbackTexts = $fallbackTexts[$explodedKey[$i]];
                if ($i === $nestCount - 1) {
                    return $fallbackTexts;
                }
            }
        }
        return null;
    }
}
