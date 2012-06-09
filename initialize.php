<?php

/**
* @author     Dac Chartrand <dac.chartrand@gmail.com>
* @license    Dual licensed under MIT and GNU LGPL v2.1
*/

// ----------------------------------------------------------------------------
// Global functions
// ----------------------------------------------------------------------------

// Autoloader
function cakepusher_autoload($class_name)
{
    $file[] = $GLOBALS['CONFIG']['PATH'] . "/includes/$class_name.php";
    // TODO: Add more $file[] combos

    foreach ($file as $f) {
        if (is_file($f)) {
            require($f);
            break;
        }
    }
}

spl_autoload_register('cakepusher_autoload');

/**
 * Get $_GET var.
 *
 * @param string $name This is a test.
 *
 * @global $_GET
 * @return mixed
 */
function _GET($name)
{
    return (isset($_GET[$name])) ? $_GET[$name] : null;
}


/**
 * Get $_POST var.
 *
 * @param string $name Blah.
 *
 * @global $POST
 * @return mixed
 */
function _POST($name)
{
    return (isset($_POST[$name])) ? $_POST[$name] : null;
}


// ----------------------------------------------------------------------------
// Procedure
// ----------------------------------------------------------------------------

// Get rid of register_globals
if (ini_get('register_globals')) {
    foreach ($_REQUEST as $k => $v) {
        unset($GLOBALS[$k]);
    }
}

// Enforce config
if (!isset($GLOBALS['CONFIG'])) {
    die("Something is wrong, can't initialize without configuration.");
}

// ----------------------------------------------------------------------------
// Stop PHP from sucking
// ----------------------------------------------------------------------------

// Sessions
ini_set('session.use_only_cookies', true);
session_start();

// Set UTF-8
header('Content-Type: text/html;charset=utf-8');
mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');
mb_language('uni');

// Avoid problems with arg_separator.output
ini_set('arg_separator.output', '&');

// Set the default timezone
date_default_timezone_set($GLOBALS['CONFIG']['TIMEZONE']);

// Get rid of magic quotes
if (get_magic_quotes_gpc() && (!ini_get('magic_quotes_sybase'))) {
    $in = array(&$_GET, &$_POST, &$_REQUEST, &$_COOKIE, &$_FILES);
    while (list($k, $v) = each($in)) {
        foreach ($v as $key => $val) {
            if (!is_array($val)) {
                $in[$k][$key] = stripslashes($val);
                continue;
            }
            $in[] =& $in[$k][$key];
        }
    }
    unset($in);
}
