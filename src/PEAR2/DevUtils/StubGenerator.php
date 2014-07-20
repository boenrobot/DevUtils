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

namespace PEAR2\DevUtils;

/**
 * Works over these sorts of packages.
 */

use DOMDocument;
use DOMXPath;
use Pyrus\Developer\PackageFile\v2;

/**
 * stub.php generator
 *
 * Provides generator for a sbub.php file, intended to be ran at "pyrus make"
 * time.
 *
 * @category PEAR2
 * @package  PEAR2_DevUtils
 * @author   Vasil Rangelov <boen.robot@gmail.com>
 * @license  http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 * @link     http://pear2.php.net/PEAR2_PackageUtil
 */
class StubGenerator
{
    const AUTOLOADER_NONE = 0;
    const AUTOLOADER_LIB = 1;
    const AUTOLOADER_SCRIPT = 2;
    const AUTOLOADER_DIAG = 4;
    const AUTOLOADER_ALL = 7;

    /**
     * @var string
     */
    private static $_packageNs = 'http://pear.php.net/dtd/package-2.1';

    /**
     * @var string
     */
    protected $shebangLine = '#!/usr/bin/env php';

    /**
     * @var string
     */
    protected $packageFile;

    /**
     * @var bool
     */
    protected $versionInfoPrint = true;

    /**
     * @var bool
     */
    protected $diagnostics = true;

    /**
     * @var string
     */
    protected $autoloader = '';

    /**
     * @var int
     */
    protected $includeAutoloaderOn = 3;

    /**
     * @var string
     */
    protected $script = '';

    /**
     * @var array<int,array>
     */
    protected $symbolChecks = array(
        0 => array(),
        1 => array()
    );

    /**
     * @var array<string,array>
     */
    protected $iniChecks = array();

    /**
     * @var array<string,string>
     */
    protected $extras = array(
        'pre' => '',
        'postAutoload' => '',
        'postScript' => '',
        'preDiag' => '',
        'postDiag' => '',
        'post' => ''
    );

    /**
     * Create a stub for a particular file.
     * 
     * @param string $packageFile Name of package.xml file to create a stub for.
     */
    public function __construct($packageFile)
    {
        $this->packageFile = $packageFile;
    }

    /**
     * Sets whether to print version information.
     * 
     * @param bool $value Whether to have package version information printed
     *     when PHAR is ran.
     * 
     * @return $this
     */
    public function setVersionInfoPrint($value)
    {
        $this->versionInfoPrint = (bool)$value;

        return $this;
    }

    /**
     * Checks whether version information will be printed by the generated stub.
     * 
     * @return bool
     */
    public function hasVersionInfoPrint()
    {
        return $this->versionInfoPrint;
    }

    /**
     * Sets a file as an autoloader.
     * 
     * @param string $file Path to a file (within the PHAR)
     *     to be used as an autoloader.
     * 
     * @return $this
     */
    public function setAutoloader($file)
    {
        $this->autoloader = (string)$file;

        return $this;
    }

    /**
     * Sets the events for which an autoloader will be included.
     * 
     * @param int $occasions Bitmask of AUTOLOADER_* constants.
     * 
     * @return $this
     */
    public function setIncludeAutoloaderOn($occasions)
    {
        $this->includeAutoloaderOn = (int)$occasions;

        return $this;
    }

    /**
     * Get the current events for which an autoloader will be included.
     * 
     * @return int
     */
    public function getIncludeAutoloaderOn()
    {
        return $this->includeAutoloaderOn;
    }

    /**
     * Get the current file used as an autoloader.
     * 
     * @return string
     */
    public function getAutoloader()
    {
        return $this->autoloader;
    }

    /**
     * Sets a script.
     * 
     * @param string $file File name (within the PHAR) to include when the PHAR
     *    is ran from the command line, with arguments (no arguments are still
     *    reserved for version info and/or diagnostics).
     * 
     * @return $this
     */
    public function setScript($file)
    {
        $this->script = $file;

        return $this;
    }

    /**
     * Get the current script file.
     * 
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * Sets whether to generate diagnostics in the stub.
     * 
     * @param bool $value Whether to have diagnostics in the generated stub.
     * 
     * @return $this
     */
    public function setDiagnostics($value)
    {
        $this->diagnostics = (bool)$value;

        return $this;
    }

    /**
     * Check whether diagnostics generation is enabled.
     * 
     * @return bool
     */
    public function hasDiagnostics()
    {
        return $this->diagnostics;
    }

    /**
     * Adds a symbol check to the diagnostics.
     * 
     * @param string $type       The type of symbol to check.
     *     Should be one that has an "_exists" function for it.
     * @param string $name       Name of symbol to check for.
     * @param bool   $isRequired Whether the symbol is required.
     * @param string $reason     Reason the symbol is required/reccomended.
     *     Will be displayed when it doesn't exist or is disabled.
     *
     * @return $this
     */
    public function addSymbolCheck(
        $type,
        $name,
        $isRequired = true,
        $reason = ''
    ) {
        $this->symbolChecks[(int)(bool)$isRequired][] = array($type, $name, $reason);

        return $this;
    }

    /**
     * Removes a check for a symbol.
     * 
     * @param string $type The type of symbol to check.
     * @param string $name The name of the symbol to check for.
     *
     * @return $this
     */
    public function removeSymbolCheck($type, $name)
    {
        for ($t = count($this->symbolChecks), $l = -1; $t > $l; --$t) {
            for ($i = count($this->symbolChecks[$t]); $i > $l; --$i) {
                if ($type === $this->symbolChecks[$t][$i][0]
                    && $name === $this->symbolChecks[$t][$i][1]
                ) {
                    unset($this->symbolChecks[$t][$i]);
                }
            }
        }
        return $this;
    }

    /**
     * Adds a check for a php.ini setting to the diagnostics.
     * 
     * @param string $directive   Name of php.ini directive to check.
     * @param mixed  $value       The value the directive should have.
     * @param string $ifExtension Only do the check if a particular extension is
     *     enabled.
     * @param string $reason      Reason the ini setting should have the
     *     specified value.
     * @param string $test        Operator to perform between the actual value
     *     (on the left) and the expected value (on the right).
     *
     * @return $this
     */
    public function addIniCheck(
        $directive,
        $value,
        $ifExtension = null,
        $reason = '',
        $test = '==='
    ) {
        $this->iniChecks[(string)$directive] = array(
            $value,
            null === $ifExtension ? null : (string)$ifExtension,
            (string)$reason,
            (string)$test
        );

        return $this;
    }

    /**
     * Removes a check for a php.ini directive.
     * 
     * @param string $directive Name of php.ini directive to check.
     *
     * @return $this
     */
    public function removeIniCheck($directive)
    {
        unset($this->iniChecks[(string)$directive]);

        return $this;
    }

    /**
     * Sets additional PHP code.
     * 
     * @param string $source PHP source code (without enclosing tags) to be
     *    inserted within the generated stub,
     *    after the shebang line and opening "<?php" tag, before anything else.
     *
     * @return $this
     */
    public function setExtraPre($source)
    {
        $this->extras['pre'] = (string)$source;

        return $this;
    }

    /**
     * Sets additional PHP code.
     * 
     * @param string $source PHP source code (without enclosing tags) to be
     *    inserted within the generated stub,
     *    just after the autoloader is included.
     *
     * @return $this
     */
    public function setExtraPostAutoload($source)
    {
        $this->extras['postAutoload'] = (string)$source;

        return $this;
    }

    /**
     * Sets additional PHP code.
     * 
     * @param string $source PHP source code (without enclosing tags) to be
     *    inserted within the generated stub,
     *    just after the script file is included.
     *
     * @return $this
     */
    public function setExtraPostScript($source)
    {
        $this->extras['postScript'] = (string)$source;

        return $this;
    }

    /**
     * Sets additional PHP code.
     * 
     * @param string $source PHP source code (without enclosing tags) to be
     *    inserted within the generated stub,
     *    before any generated diagnostics begin.
     *
     * @return $this
     */
    public function setExtraPreDiag($source)
    {
        $this->extras['preDiag'] = (string)$source;

        return $this;
    }

    /**
     * Sets additional PHP code.
     * 
     * @param string $source PHP source code (without enclosing tags) to be
     *    inserted within the generated stub,
     *    after any generated diagnostics have passed.
     *
     * @return $this
     */
    public function setExtraPostDiag($source)
    {
        $this->extras['postDiag'] = (string)$source;

        return $this;
    }

    /**
     * Sets additional PHP code.
     * 
     * @param string $source PHP source code (without enclosing tags) to be
     *    inserted within the generated stub,
     *    after everything, just before the __HALT_COMPILER(); call.
     *
     * @return $this
     */
    public function setExtraPost($source)
    {
        $this->extras['post'] = (string)$source;

        return $this;
    }

    /**
     * Builds the diagnostics, based on a package.xml file.
     * 
     * @param string $packageFile The package.xml file, based on which to build
     *     diagnostics.
     *
     * @return string
     */
    public static function buildDiagnostics($packageFile)
    {
        $result = '';
        $dom = new DOMDocument;
        $dom->loadXML(file_get_contents($packageFile));
        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('p', self::$_packageNs);
        $minPhp = $xpath->evaluate(
            'string(/p:package/p:dependencies/p:required/p:php/p:min/text())'
        );
        $maxPhp = $xpath->evaluate(
            'string(/p:package/p:dependencies/p:required/p:php/p:max/text())'
        );

        if ($minPhp) {
            $result .= <<<HEREDOC
if (version_compare(phpversion(), '{$minPhp}', '<')) {
    echo "\\nERROR: This package requires PHP {$minPhp} or later.\\n";
    exit(2);
}

HEREDOC;
        }

        if ($maxPhp) {
            $result .= <<<HEREDOC
if (version_compare(phpversion(), '{$maxPhp}', '>')) {
    echo "\\nERROR: This package requires PHP no later than {$maxPhp}.\\n";
    exit(2);
}

HEREDOC;
        }

        $excludedPhpVersions = array();
        foreach ($xpath->query(
            '/p:package/p:dependencies/p:php/p:exclude'
        ) as $phpExc) {
            $excludedPhpVersions[] = $phpExc->nodeValue;
        }
        if (!empty($excludedPhpVersions)) {
            $result .= 'if (in_array(phpversion(), ' .
                var_export($excludedPhpVersions, true) . ')) {' . <<<HEREDOC

    echo "\\nERROR: This package is not compatible with your particular PHP version,
       although it's within the allowed range.\\n";
    exit(2);
}

HEREDOC;
        }
        $requiredExtensions = $xpath->query(
            '/p:package/p:dependencies/p:required/p:extension'
        );
        if ($requiredExtensions->length > 0) {
            $result .= "\$errors = array();\n";
        }
        foreach ($requiredExtensions as $reqExt) {
            $extName = var_export(
                $reqExt->getElementsByTagName('name')->item(0)->nodeValue,
                true
            );
            $extMin = $reqExt->getElementsByTagName('min');
            if ($extMin->length > 0) {
                $extMin = $extMin->item(0)->nodeValue;
            } else {
                $extMin = false;
            }
            $extMax = $reqExt->getElementsByTagName('max');
            if ($extMax->length > 0) {
                $extMax = $extMax->item(0)->nodeValue;
            } else {
                $extMax = false;
            }
            $negateCheck = $reqExt->getElementsByTagName('conflicts')->length > 0;
            $extExcluded = array();
            foreach ($reqExt->getElementsByTagName('exclude') as $extExc) {
                $extExcluded[] = $extExc->nodeValue;
            }

            if ($negateCheck) {
                if (false === $extMin
                    && false === $extMax
                    && empty($extExcluded)
                ) {
                    $result .= <<<HEREDOC
if (extension_loaded({$extName})) {
    \$errors[] = "\\nERROR: This package requres that the {$extName} extension 
       be disabled.\\n";
}

HEREDOC;
                }
                if (empty($extExcluded)) {
                    $excCheck = '';
                } else {
                    $excCheck = "\n    && !in_array(phpversion({$extName}), " .
                        var_export($extExcluded, true) . ')';
                }
                if ($extMin) {
                    $result .= <<<HEREDOC
if (extension_loaded({$extName})
    && version_compare(phpversion({$extName}), '{$extMin}', '>='){$excCheck}
) {
    \$errors[] = "\\nERROR: This package requires that the {$extName} extension
       is version earlier than {$extMin}.\\n";
}

HEREDOC;
                }

                if ($extMax) {
                    $result .= <<<HEREDOC
if (extension_loaded({$extName})
    && version_compare(phpversion({$extName}), '{$extMax}', '<='){$excCheck}
) {
    echo "\\nERROR: This package requires that the {$extName} extension
       is version later than {$extMax}.\\n";
    exit(4);
}

HEREDOC;
                }
            } else {
                $result .= <<<HEREDOC
if (!extension_loaded({$extName})) {
    \$errors[] = "\\nERROR: This package requres the {$extName} extension.\\n";
}

HEREDOC;

                if ($extMin) {
                    $result .= <<<HEREDOC
if (version_compare(phpversion({$extName}), '{$extMin}', '>=')) {
    \$errors[] = "\\nERROR: This package requires that the {$extName} extension
       is version {$extMin} or later.\\n";
}

HEREDOC;
                }

                if ($extMax) {
                    $result .= <<<HEREDOC
if (version_compare(phpversion({$extName}), '{$extMax}', '<=')) {
    \$errors[] = "\\nERROR: This package requires that the {$extName} extension
       is version no later than {$extMax}.\\n";
}

HEREDOC;
                }
                if (!empty($extExcluded)) {
                    $result .= "if (in_array(phpversion({$extName}), " .
                        var_export($extExcluded, true) . ')) {' . <<<HEREDOC

    \$errors[] = "\\nERROR: This package is not compatible with your particular
       {$extName} version, although it's within the allowed range.\\n";
}

HEREDOC;
                }
            }
        }


        if ($requiredExtensions->length > 0) {
            $result .= <<<'NEWDOC'
if (!empty($errors)) {
    echo implode('', $errors);
    exit(3);
}

NEWDOC;
        }

        foreach ($xpath->query(
            '/p:package/p:dependencies/p:optional/p:extension'
        ) as $optExt) {
            $extName = var_export(
                $optExt->getElementsByTagName('name')->item(0)->nodeValue,
                true
            );
            $extMin = $optExt->getElementsByTagName('min');
            if ($extMin->length > 0) {
                $extMin = $extMin->item(0)->nodeValue;
            } else {
                $extMin = false;
            }
            $extMax = $optExt->getElementsByTagName('max');
            if ($extMax->length > 0) {
                $extMax = $extMax->item(0)->nodeValue;
            } else {
                $extMax = false;
            }
            $negateCheck
                = $optExt->getElementsByTagName('conflicts')->length > 0;
            $extExcluded = array();
            foreach ($optExt->getElementsByTagName('exclude') as $extExc) {
                $extExcluded[] = $extExc->nodeValue;
            }

            if ($negateCheck) {
                if (false === $extMin
                    && false === $extMax
                    && empty($extExcluded)
                ) {
                    $result .= <<<HEREDOC
if (extension_loaded({$extName})) {
    echo "\\nWARNING: This package reccomends that the {$extName} extension 
       be disabled, though it should work regardless.\\n";
}

HEREDOC;
                }
                if (empty($extExcluded)) {
                    $excCheck = '';
                } else {
                    $excCheck = "\n    && !in_array(phpversion({$extName}), " .
                        var_export($extExcluded, true) . ')';
                }
                if ($extMin) {
                    $result .= <<<HEREDOC
if (extension_loaded({$extName})
    && version_compare(phpversion({$extName}), '{$extMin}', '>='){$excCheck}
) {
    echo "\\nWARNING: This package reccomends that the {$extName} extension
         is version earlier than {$extMin}.\\n";
}

HEREDOC;
                }

                if ($extMax) {
                    $result .= <<<HEREDOC
if (extension_loaded({$extName})
    && version_compare(phpversion({$extName}), '{$extMax}', '<='){$excCheck}
) {
    echo "\\nWARNING: This package reccomends that the {$extName} extension
         is version later than {$extMax}.\\n";
}

HEREDOC;
                }
            } else {
                $result .= <<<HEREDOC
if (!extension_loaded({$extName})) {
    echo "\\nWARNING: This package may optionally use the {$extName} extension, 
         which is currently missing or is disabled.\\n";
} else {

HEREDOC;

                if ($extMin) {
                    $result .= <<<HEREDOC
    if (!version_compare(phpversion({$extName}), '{$extMin}', '>=')) {
        echo "\\nWARNING: This package may optionally use the {$extName} extension
         if its version is {$extMin} or later, and your version isn't.\\n";
    }

HEREDOC;
                }

                if ($extMax) {
                    $result .= <<<HEREDOC
    if (!version_compare(phpversion({$extName}), '{$extMax}', '<=')) {
        echo "\\nWARNING: This package may optionally use the {$extName} extension
         if its version is no later than {$extMax}, and your version is.\\n";
    }

HEREDOC;
                }
                if (!empty($extExcluded)) {
                    $result .= "    if (in_array(phpversion({$extName}), " .
                        var_export($extExcluded, true) . ')) {' . <<<HEREDOC

        echo "\\nWARNING: This package may optionally use {$extName},
         but not with your version.\\n";
    }

HEREDOC;
                }
                $result .= "}\n\n";
            }
        }

        return $result;
    }

    /**
     * Builds checks for php.ini directives.
     * 
     * @return string
     */
    public function buildIniChecks()
    {
        $result = '';
        foreach ($this->iniChecks as $directive => $info) {
            list($value, $ifExtension, $reason, $test) = $info;
            $directive = var_export($directive, true);
            $value = var_export($value, true);
            $reason = var_export($reason, true);
            $result .= 'if (';
            if (null !== $ifExtension) {
                $result .= 'extension_loaded("{$ifExtension}") && ';
            }
            $result .= "(\$iniVal = ini_get({$directive})) {$test} " .
                $value . ") {\n" . <<<HEREDOC
    echo "\\nWARNING: INI setting '" . {$directive} . "' does not have
         the reccomended value (has value " . var_export(\$iniVal, true) . 
         " instead)." . {$reason} . "\\n";
}

HEREDOC;
        }
        return $result;
    }

    /**
     * Builds the symbol checks.
     * 
     * @return string
     */
    public function buildSymbolChecks()
    {
        $result = '';
        if (!empty($this->symbolChecks[1])) {
            $result .= "\$errors = array();\n";
        }
        foreach ($this->symbolChecks[1] as $symbolInfo) {
            list($type, $name, $reason) = $symbolInfo;
            $reason = str_replace('"', '\\"', $reason);
            $autoload = 'function' !== $type ? ', true' : '';
            $result .= <<<HEREDOC
if (!{$type}_exists('{$name}'{$autoload})) {
    \$errors[] = "\\nERROR: The \"{$name}\" {$type}
       is missing or is disabled.
       {$reason}\\n";
}

HEREDOC;
        }
        if (!empty($this->symbolChecks[1])) {
            $result .= "echo implode('', \$errors);\n\n";
        }

        foreach ($this->symbolChecks[0] as $symbolInfo) {
            list($type, $name, $reason) = $symbolInfo;
            $reason = str_replace('"', '\\"', $reason);
            $autoload = 'function' !== $type ? ', true' : '';
            $result .= <<<HEREDOC
if (!{$type}_exists('{$name}'{$autoload})) {
    echo "\\nWARNING: The \"{$name}\" {$type} is missing or is disabled.
         {$reason}\\n";
}

HEREDOC;
        }
        return $result;
    }

    /**
     * Builds the stub.
     * 
     * @return string
     */
    public function build()
    {
        $result = '';
        if ('' !== $this->shebangLine) {
            $result .= $this->shebangLine . "\n";
        }
        $result .= <<<'NEWDOC'
<?php


NEWDOC;
        $result .= $this->extras['pre'];
        if (static::AUTOLOADER_NONE !== $this->includeAutoloaderOn
            || '' !== $this->script
        ) {
            $condition = array();
            if ($this->includeAutoloaderOn & static::AUTOLOADER_LIB) {
                $condition[] = 'count(get_included_files()) > 1';
            }
            if ($this->includeAutoloaderOn & static::AUTOLOADER_SCRIPT
                && '' !== $this->script
            ) {
                $condition[] = "(\$argc > 1 && 'cli' === PHP_SAPI)";
            }
            if (static::AUTOLOADER_ALL !== $this->includeAutoloaderOn) {
                $result .= 'if (' . implode(' || ', $condition) . ") {\n";
            } elseif ($this->includeAutoloaderOn & static::AUTOLOADER_DIAG) {
                $result .= 'if (!(' . implode(' || ', $condition) . ")) {\n";
            }
            $result .= <<<'NEWDOC'
    if (!extension_loaded('phar')) {
        echo 'Missing required extension "PHAR".
Install it, or extract the archive, and use the extracted files instead.';
        exit(1);
    }
    Phar::mapPhar();

NEWDOC;
            if ($this->autoloader) {
                $result .= <<<'NEWDOC'
    include_once 'phar://' . __FILE__ . DIRECTORY_SEPARATOR .
        '@PACKAGE_NAME@-@PACKAGE_VERSION@' . DIRECTORY_SEPARATOR .
        '
NEWDOC;
                $result .= $this->autoloader . "';\n";
                $result .= $this->extras['postAutoload'];
            }
            if ($this->script) {
                $result .= <<<'NEWDOC'
    if ($argc > 1 && count(get_included_files()) === 1) {
        include_once 'phar://' . __FILE__ . DIRECTORY_SEPARATOR .
            '@PACKAGE_NAME@-@PACKAGE_VERSION@' . DIRECTORY_SEPARATOR .
            '
NEWDOC;
                $result .= $this->script . "';\n    }\n";
                $result .= $this->extras['postScript'];
            }
            if (static::AUTOLOADER_NONE !== $this->includeAutoloaderOn
                && static::AUTOLOADER_ALL !== $this->includeAutoloaderOn
            ) {
                $result .= "}\n\n";
            }
        }
        if ($this->versionInfoPrint) {
            $result .= <<<'NEWDOC'
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


NEWDOC;
        }
        if ($this->diagnostics) {
            $result .= $this->extras['preDiag'];
            $result .= static::buildDiagnostics($this->packageFile);
            $result .= $this->buildSymbolChecks();
            $result .= $this->buildIniChecks();
            $result .= $this->extras['postDiag'];
        }
        $result .= $this->extras['post'];
        $result .= '
__HALT_COMPILER();
';
        return $result;
    }

    /**
     * Builds the stub, and saves it to a file.
     * 
     * @param string $filename Name of file, where the stub will be saved to.
     *
     * @return int Number of bytes written to the file.
     */
    public function save($filename = 'stub.php')
    {
        return file_put_contents($filename, $this->build());
    }
}
