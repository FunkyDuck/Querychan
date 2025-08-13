<?php

namespace Querychan\ORM;

class Utils {
    public static function getPackageVersion(): ?string {
        $composerFile =  __DIR__. "/../../composer.json";
        if(!file_exists($composerFile)) {
            return null;
        }

        $json = json_decode(file_get_contents($composerFile), true);
        return $json['version'] ?? null;
    }

    public static function toTitleCase(string $text): string {
        $text = str_replace(['-', '_', '.'], ' ', $text);
        $words = explode(' ', $text);
        $words = array_map(fn($w) => ucfirst($w), $words);
        $text = implode('', $words);
        return $text;
    }

    public static function toSnakeCase(string $text): string {
        $text = str_replace(['-', '.'], '_', $text);
        $text = strtolower($text);
        return $text;
    }

    public static function titleToSnake(string $text): string {
        $text = preg_replace('/(?<!^)[A-Z]/', '_$0', $text);
        return strtolower($text);
    }
}