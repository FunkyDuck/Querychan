<?php

namespace FunkyDuck\Querychan\ORM;

class Utils {
    public static function getPackageVersion(): ?string {
        $composerFile =  __DIR__. "/../../vendor/composer/installed.json";
        if(!file_exists($composerFile)) {
            return null;
        }

        $json = json_decode(file_get_contents($composerFile), true);
        if(!isset($json['packages'])) {
            return null;
        }

        foreach($json['packages'] as $package) {
            if(isset($package['name']) && $package['name'] === 'funkyduck/querychan') {
                return $package['version_normalized'] ?? $package['version'] ?? null;
            }
        }

        return null;
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