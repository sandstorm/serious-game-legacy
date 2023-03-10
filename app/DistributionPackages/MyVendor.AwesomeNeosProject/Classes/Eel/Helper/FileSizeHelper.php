<?php
declare(strict_types=1);

namespace MyVendor\AwesomeNeosProject\Eel\Helper;

use Neos\Eel\ProtectedContextAwareInterface;

class FileSizeHelper implements ProtectedContextAwareInterface
{

    /**
     * Get size of a file and format to human readable
     *
     * @param $size float
     * @return string
     *
     */
    public function format(float $size): string
    {
        // https://stackoverflow.com/a/2510540
        $base = log($size, 1024);
        $suffixes = array('', 'KB', 'MB', 'GB', 'TB');

        return round(pow(1024, $base - floor($base)), 0) .' '. $suffixes[floor($base)];
    }

    /**
     * All methods are considered safe, i.e. can be executed  from within Eel
     *
     * @param string $methodName
     * @return boolean
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}

