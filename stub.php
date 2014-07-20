#!/usr/bin/env php
<?php

/**
 * Stub for PEAR2_DevUtils.
 * 
 * PHP version 5.3
 * 
 * @category  PEAR2
 * @package   PEAR2_DevUtils
 * @author    Vasil Rangelov <boen.robot@gmail.com>
 * @copyright 2014 Vasil Rangelov
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   GIT: $Id$
 * @link      http://pear2.php.net/PEAR2_DevUtils
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

__HALT_COMPILER();
