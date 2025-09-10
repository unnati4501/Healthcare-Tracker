<?php

declare (strict_types = 1);
namespace App\mThumb;

/**
 * mthumb-config.php
 *
 * Example mThumb configuration file.
 *
 * @created   4/2/14 11:52 AM
 * @author    Mindshare Studios, Inc.
 * @copyright Copyright (c) 2006-2015
 * @link      http://www.mindsharelabs.com/
 *
 */

// Max sizes
if (!defined('MAX_WIDTH')) {
    define('MAX_WIDTH', 3600);
}
if (!defined('MAX_HEIGHT')) {
    define('MAX_HEIGHT', 3600);
}
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 20971520); // 20MB
}

define('ALLOW_EXTERNAL', true);

/*
 *  External Sites
 */
global $ALLOWED_SITES;


// Get DO doamin full url and fetch domain
$disk    = config('medialibrary.disk_name');
$path    = config("medialibrary.{$disk}.domain");
$do_host = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// Allowed DO doamins
$ALLOWED_SITES = array(
    $do_host,
    "zevolife.local",
    "dev.zevolife.com",
    "qa.zevolife.com",
    "uat.zevolife.com",
    "performance.zevolife.com",
    "zevo.app",
);

// Allow placeholder domain for testing in local
if (config('app.env') == 'local') {
    $ALLOWED_SITES[] = 'via.placeholder.com';
}

// The rest of the code in this config only applies to Apache mod_userdir  (URIs like /~username)
if (mthumbInUrl('~')) {
    $_SERVER['DOCUMENT_ROOT'] = mthumbFindWpRoot();
}

/**
 *  We need to set DOCUMENT_ROOT in cases where /~username URLs are being used.
 *  In a default WordPress install this should result in the same value as ABSPATH
 *  but ABSPATH and all WP functions are not accessible in the current scope.
 *
 *  This code should work in 99% of cases.
 *
 * @param int $levels
 *
 * @return bool|string
 */
function mthumbFindWpRoot($levels = 9)
{
    $dir_name = dirname(__FILE__) . '/';

    for ($i = 0; $i <= $levels; $i++) {
        $path = realpath($dir_name . str_repeat('../', $i));
        if (file_exists($path . '/wp-config.php')) {
            return $path;
        }
    }
    return false;
}

/**
 *
 * Gets the current URL.
 *
 * @return string
 */
function mthumbGetUrl()
{
    $s        = isset($_SERVER["HTTPS"]) ? $_SERVER["HTTPS"] : "";
    $protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")) . $s;
    $port     = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":" . $_SERVER["SERVER_PORT"]);
    return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
}

/**
 *
 * Checks to see if $text is in the current URL.
 *
 * @param $text
 *
 * @return bool
 */
function mthumbInUrl($text)
{
    if (stristr(mthumbGetUrl(), $text)) {
        return true;
    } else {
        return false;
    }
}
