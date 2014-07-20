<?php

/**
 * ~~summary~~
 *
 * ~~description~~
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_DevUtils
 * @author    Vasil Rangelov <boen.robot@gmail.com>
 * @copyright 2014 Vasil Rangelov
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @version   GIT: $Id$
 * @link      http://pear2.php.net/PEAR2_PackageUtil
 */

namespace PEAR2\DevUtils;

/**
 * Works over these sorts of packages.
 */

use Pyrus\Developer\PackageFile\v2;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * package.xml utitiles
 *
 * Provides common "pyrus make" time utitilies for package.xml files.
 *
 * @category PEAR2
 * @package  PEAR2_DevUtils
 * @author   Vasil Rangelov <boen.robot@gmail.com>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @link     http://pear2.php.net/PEAR2_PackageUtil
 */
class FileBuilder
{
    /**
     * Used in {@link addEolReplacement()}
     * to remove previously assigned replacement on matching files.
     */
    const EOL_DEFAULT = null;

    /**
     * Used in {@link addEolReplacement()}
     * to set Windows EOL on matching files.
     */
    const EOL_WINDOWS = 'windows';

    /**
     * Used in {@link addEolReplacement()}
     * to set UNIX EOL on matching files.
     */
    const EOL_UNIX = 'unix';

    /**
     * @var v2
     */
    protected $package;

    /**
     * @var v2
     */
    protected $compatible;

    /**
     * @var array<string,array<string, string>>
     */
    protected $replacements = array();

    /**
     * @var array<string,string>
     */
    protected $eolReplacements = array();

    /**
     * Perform taks over the package objects.
     * 
     * @param v2 $package    The object representing the main package.
     * @param v2 $compatible The object representing the compatible package.
     */
    public function __construct(v2 $package, v2 $compatible = null)
    {
        $this->package = $package;
        $this->compatible = $compatible;
    }

    /**
     * Add a replacement over all files.
     * 
     * @param string $from String to look for.
     * @param string $to   The name of an attribute,
     *     the value of which will be used as a replacement.
     * @param string $type The type of attribute.
     *
     * @return $this
     */
    public function addReplacement($from, $to, $type)
    {
        $this->replacements[$from] = array(
            'to' => $to,
            'type' => $type
        );

        return $this;
    }

    /**
     * Remove a previously added replacement.
     * 
     * @param string $from The string that was supposed to be replaced.
     *
     * @return $this
     */
    public function removeReplacement($from)
    {
        unset($this->replacements[$from]);

        return $this;
    }

    /**
     * Add an EOL replacement.
     * 
     * @param string $fileMatch fnmatch() pattern to match files against.
     * @param string $eolType   The type of EOL to use for matching files.
     *     Should be one of this class' EOL_* constants.
     *
     * @return $this
     */
    public function addEolReplacement($fileMatch, $eolType)
    {
        if (static::EOL_DEFAULT === $eolType) {
            unset($this->eolReplacements[$fileMatch]);
        } else {
            $this->eolReplacements[$fileMatch] = $eolType;
        }

        return $this;
    }

    /**
     * Builds the package file.
     *
     * @return $this
     */
    public function buildPackageFile()
    {
        $tasksNs = $this->package->getTasksNs();
        $cTasksNs = $this->compatible ? $compatible->getTasksNs() : '';
        $oldCwd = getcwd();
        chdir($this->package->filepath);
        $this->package->setRawRelease('php', array());
        $release = $this->package->getReleaseToInstall('php', true);
        if ($this->compatible) {
            $this->compatible->setRawRelease('php', array());
            $cRelease = $this->compatible->getReleaseToInstall('php', true);
        }
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                '.',
                RecursiveDirectoryIterator::UNIX_PATHS
                | RecursiveDirectoryIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::LEAVES_ONLY
        ) as $path) {
                $filename = substr($path->getPathname(), 2);
                $cFilename = str_replace('src/', 'php/', $filename);
            if (isset($this->package->files[$filename])) {
                $parsedFilename = pathinfo($filename);
                $as = (strpos($filename, 'examples/') === 0)
                    ? $filename
                    : substr($filename, strpos($filename, '/') + 1);
                if (strpos($filename, 'scripts/') === 0) {
                    if (isset($parsedFilename['extension'])
                        && 'php' === $parsedFilename['extension']
                        && !is_file(
                            $parsedFilename['dirname'] . '/' .
                            $parsedFilename['filename']
                        )
                        && is_file(
                            $parsedFilename['dirname'] . '/' .
                            $parsedFilename['filename'] . '.bat'
                        )
                    ) {
                        $as = substr($as, 0, -4);
                    }
                }
                $release->installAs($filename, $as);
                if ($this->compatible) {
                    $cRelease->installAs($cFilename, $as);
                }

                $contents = file_get_contents($filename);
                foreach ($this->replacements as $from => $attribs) {
                    if (strpos($contents, $from) !== false) {
                        $attribs['from'] = $from;
                        $this->package->files[$filename] = array_merge_recursive(
                            $this->package->files[$filename]->getArrayCopy(),
                            array(
                                "{$tasksNs}:replace" => array(
                                    array(
                                        'attribs' => $attribs
                                    )
                                )
                            )
                        );

                        if ($this->compatible) {
                            $this->compatible->files[$cFilename]
                                = array_merge_recursive(
                                    $this->compatible->files[$cFilename]
                                        ->getArrayCopy(),
                                    array(
                                        "{$cTasksNs}:replace" => array(
                                            array(
                                                'attribs' => $attribs
                                            )
                                        )
                                    )
                                );
                        }
                    }
                }

                foreach ($this->eolReplacements as $pattern => $platform) {
                    if (fnmatch($pattern, $filename)) {
                        $this->package->files[$filename] = array_merge_recursive(
                            $this->package->files[$filename]->getArrayCopy(),
                            array(
                                "{$tasksNs}:{$platform}eol" => array()
                            )
                        );

                        if ($this->compatible) {
                            $this->compatible->files[$cFilename]
                                = array_merge_recursive(
                                    $this->compatible->files[$cFilename]
                                        ->getArrayCopy(),
                                    array(
                                        "{$cTasksNs}:{$platform}eol" => array()
                                    )
                                );
                        }
                    }
                }
            }
        }
        chdir($oldCwd);

        return $this;
    }
}
