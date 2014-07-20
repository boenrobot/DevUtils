<?php

/**
 * packagexmlsetup.php for PEAR2_DevUtils.
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
use PEAR2\DevUtils\FileBuilder;

require_once 'PEAR2/Autoload.php';
Autoload::initialize('src');

$builder = new FileBuilder($package, $compatible);
$builder
    ->addEolReplacement('*.bat', FileBuilder::EOL_WINDOWS)
    ->addEolReplacement('*.sh', FileBuilder::EOL_UNIX)
    ->addReplacement('../src', 'php_dir', 'pear-config')
    ->addReplacement('GIT: $Id$', 'version', 'package-info')
    ->addReplacement('~~summary~~', 'summary', 'package-info')
    ->addReplacement('~~description~~', 'description', 'package-info')
    ->buildPackageFile();
