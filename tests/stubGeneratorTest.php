<?php

/**
 * ~~summary~~
 *
 * ~~description~~
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_PackageUtils
 * @author    Vasil Rangelov <boen.robot@gmail.com>
 * @copyright 2014 Vasil Rangelov
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   GIT: $Id$
 * @link      http://pear2.php.net/PEAR2_PackageUtil
 */

use PEAR2\DevUtils\StubGenerator;

/**
 * ~
 *
 * @category PEAR2
 * @package  PEAR2_DevUtils
 * @author   Vasil Rangelov <boen.robot@gmail.com>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @link     http://pear2.php.net/PEAR2_DevUtils
 */
class RequestHandlingTest extends PHPUnit_Framework_TestCase
{
    public function testPEAR2CacheSHM()
    {
        $stubGen = new StubGenerator('input/PEAR2_Cache_SHM.xml');
        $stubGen->setExtraPre(
            <<<'NEWDOC'
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

NEWDOC
        );
        $stubGen->setAutoloader('src/PEAR2/Autoload.php');

        //$stubGen->save('expected/PEAR2_Cache_SHM.xml.php');
        $this->assertSame(
            file_get_contents('expected/PEAR2_Cache_SHM.xml.php'),
            $stubGen->build()
        );
    }

    public function testPEAR2NetRouterOS()
    {
        $stubGen = new StubGenerator('input/PEAR2_Net_RouterOS.xml');
        $stubGen->setExtraPre(
            <<<'NEWDOC'
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

NEWDOC
        );
        $stubGen->setAutoloader('src/PEAR2/Autoload.php');
        $stubGen->setScript('bin/roscon.php');
        $stubGen->addSymbolCheck(
            'function',
            'stream_socket_client',
            true,
            'Without it, you won\'t be able to make any connections.'
        );

        //$stubGen->save('expected/PEAR2_Net_RouterOS.xml.php');
        $this->assertSame(
            file_get_contents('expected/PEAR2_Net_RouterOS.xml.php'),
            $stubGen->build()
        );
    }
}