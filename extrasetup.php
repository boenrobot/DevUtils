<?php

/**
 * extrasetup.php for PEAR2_DevUtils.
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

use PEAR2\Autoload;
use PEAR2\DevUtils\Bundler;

require_once 'PEAR2/Autoload.php';
Autoload::initialize('src');

$bundler = new Bundler();

$extrafiles = $bundler
    ->addPackages('package.xml')
    ->generateExtrafiles();