#!/usr/bin/env php
<?php

/**
 * stub for PEAR2_Cache_SHM.
 * 
 * PHP version 5.3
 * 
 * @category  Caching
 * @package   PEAR2_Cache_SHM
 * @author    Vasil Rangelov <boen.robot@gmail.com>
 * @copyright 2011 Vasil Rangelov
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   GIT: $Id$
 * @link      http://pear2.php.net/PEAR2_Cache_SHM
 */
if (count(get_included_files()) > 1) {
    if (!extension_loaded('phar')) {
        echo 'Missing required extension "PHAR".
Install it, or extract the archive, and use the extracted files instead.';
        exit(1);
    }
    Phar::mapPhar();
    include_once 'phar://' . __FILE__ . DIRECTORY_SEPARATOR .
        '@PACKAGE_NAME@-@PACKAGE_VERSION@' . DIRECTORY_SEPARATOR .
        'src/PEAR2/Autoload.php';
}

$isHttp = isset($_SERVER['REQUEST_URI']);
if ($isHttp) {
    header('Content-Type: text/plain;charset=UTF-8');
}
echo "@PACKAGE_NAME@ @PACKAGE_VERSION@\n";

if (extension_loaded('phar')) {
    try {
        $phar = new Phar(__FILE__);
        $sig = $phar->getSignature();
        echo "{$sig['hash_type']} hash: {$sig['hash']}\n";
    } catch (Exception $e) {
        echo <<<HEREDOC

The PHAR extension is available, but was unable to read this PHAR file's hash.
HEREDOC;
        if (false !== strpos($e->getMessage(), 'file extension')) {
            echo <<<HEREDOC

This can happen if you've renamed the file to ".php" instead of ".phar".
Regardless, you should be able to include this file without problems.
HEREDOC;
        }
    }
} else {
    echo <<<HEREDOC
WARNING: If you wish to use this package directly from this archive, you need
         to install and enable the PHAR extension. Otherwise, you must instead
         extract this archive, and include the autoloader.

HEREDOC;
}

if (version_compare(phpversion(), '5.3.0', '<')) {
    echo "\nERROR: This package requires PHP 5.3.0 or later.\n";
    exit(2);
}
if (!extension_loaded('apc')) {
    echo "\nWARNING: This package may optionally use the 'apc' extension, 
         which is currently missing or is disabled.\n";
} else {
    if (!version_compare(phpversion('apc'), '3.0.13', '>=')) {
        echo "\nWARNING: This package may optionally use the 'apc' extension
         if its version is 3.0.13 or later, and your version isn't.\n";
    }
}

if (!extension_loaded('wincache')) {
    echo "\nWARNING: This package may optionally use the 'wincache' extension, 
         which is currently missing or is disabled.\n";
} else {
    if (!version_compare(phpversion('wincache'), '1.1.0', '>=')) {
        echo "\nWARNING: This package may optionally use the 'wincache' extension
         if its version is 1.1.0 or later, and your version isn't.\n";
    }
}


__HALT_COMPILER();
