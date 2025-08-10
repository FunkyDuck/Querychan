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
}