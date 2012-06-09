<?php

/**
* @author     Dac Chartrand <dac.chartrand@gmail.com>
* @license    Dual licensed under MIT and GNU LGPL v2.1
*/

set_time_limit(0);

require_once(dirname(__FILE__) . '/config.php'); // Configuration
require_once(dirname(__FILE__) . '/initialize.php'); // Initialization

$updates = Toolbox::hustler();

// Debug
if ($GLOBALS['CONFIG']['DEBUG']) {
    echo "### Updates:\n";
    print_r($updates);
    echo "\n\n";
}

if (count($updates)) {

    // TODO:
    // Check harassment threshold
    // Don't dupe proposals

    if ($GLOBALS['CONFIG']['DEBUG']) {
        echo "### Sending status updates...\n";
        echo "\n\n";
    }

    Toolbox::dent($updates);

    if ($GLOBALS['CONFIG']['DEBUG']) {
        echo "### ...Done!\n";
    }

}
