<?php

namespace Unzer\bin;


class CopyAdimnAssets
{
    public static function run()
    {
        self::fixAndCopyDirectory('js', 'js');
        self::fixAndCopyDirectory('css', 'css');
        self::fixAndCopyDirectory('fonts', 'fonts');
    }

    private static function fixAndCopyDirectory($from, $to)
    {
        self::copyDirectory(
            __DIR__ . '/../vendor/unzer/unzer-core/Resources/admin-ui/dist/' . $from,
            __DIR__ . '/../public/admin-ui/'. $to);
    }

    private static function copyDirectory($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::copyDirectory($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }

        closedir($dir);
    }
}

CopyAdimnAssets::run();
