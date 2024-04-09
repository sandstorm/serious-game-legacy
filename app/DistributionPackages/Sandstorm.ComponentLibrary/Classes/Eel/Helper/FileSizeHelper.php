<?php

declare(strict_types=1);

namespace Sandstorm\ComponentLibrary\Eel\Helper;

use Neos\Eel\ProtectedContextAwareInterface;

class FileSizeHelper implements ProtectedContextAwareInterface
{

    /**
     * Get size of a file and format to human readable
     *
     * @param float $size
     * @return string
     *
     */
    public function format(float $size): string
    {
        if ($size == 0) {
            return '0 B';
        }
        // Return when $size is null, negative or 0, otherwise $base would result in NAN
        if (!$size || $size < 0) {
            return  '';
        }

        // https://stackoverflow.com/a/2510540
        $base = log($size, 1024);
        $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');

        return round(pow(1024, $base - floor($base)), 0) . ' ' . $suffixes[floor($base)];
    }

    /**
     * All methods are considered safe, i.e. can be executed from within Eel
     *
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
