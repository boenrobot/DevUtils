#!/usr/bin/env php
<?php

/**
 * Stub for PEAR2_Net_RouterOS.
 * 
 * PHP version 5.3
 * 
 * @category  Net
 * @package   PEAR2_Net_RouterOS
 * @author    Vasil Rangelov <boen.robot@gmail.com>
 * @copyright 2011 Vasil Rangelov
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   GIT: $Id$
 * @link      http://pear2.php.net/PEAR2_Net_RouterOS
 */
if (count(get_included_files()) > 1 || ($argc > 1 && 'cli' === PHP_SAPI)) {
    if (!extension_loaded('phar')) {
        echo 'Missing required extension "PHAR".
Install it, or extract the archive, and use the extracted files instead.';
        exit(1);
    }
    Phar::mapPhar();
    include_once 'phar://' . __FILE__ . DIRECTORY_SEPARATOR .
        '@PACKAGE_NAME@-@PACKAGE_VERSION@' . DIRECTORY_SEPARATOR .
        'src/PEAR2/Autoload.php';
    if ($argc > 1 && count(get_included_files()) === 1) {
        include_once 'phar://' . __FILE__ . DIRECTORY_SEPARATOR .
            '@PACKAGE_NAME@-@PACKAGE_VERSION@' . DIRECTORY_SEPARATOR .
            'bin/roscon.php';
    }
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
$errors = array();
if (!extension_loaded('PCRE')) {
    $errors[] = "\nERROR: This package requres the 'PCRE' extension.\n";
}
if (!empty($errors)) {
    echo implode('', $errors);
    exit(3);
}
if (!extension_loaded('iconv')) {
    echo "\nWARNING: This package may optionally use the 'iconv' extension, 
         which is currently missing or is disabled.\n";
} else {
}

$errors = array();
if (!function_exists('stream_socket_client')) {
    $errors[] = "\nERROR: The \"stream_socket_client\" function
       is missing or is disabled.
       Without it, you won't be able to make any connections.\n";
}
echo implode('', $errors);


__HALT_COMPILER();
