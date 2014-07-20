<?php

/**
 * bootstrap.php for PEAR2_DevUtils.
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

namespace PEAR2\DevUtils;

/**
 * Possible autoloader to initialize.
 */
use PEAR2\Autoload;

chdir(__DIR__);

$autoloader = stream_resolve_include_path('../vendor/autoload.php');
if (false !== $autoloader) {
    include_once $autoloader;
} else {
    $autoloader = stream_resolve_include_path('PEAR2/Autoload.php');
    if (false !== $autoloader) {
        include_once $autoloader;
        Autoload::initialize(realpath('../src'));
    } else {
        die('No recognized autoloader is available.');
    }
}
unset($autoloader);