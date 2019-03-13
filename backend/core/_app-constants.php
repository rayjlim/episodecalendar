<?php
/** * Constants
 *
 * PHP version 5
 *
 * @category PHP
 * @package  MovieDB
 * @author   Raymond Lim <raymond@lilplaytime.com>
 * @license  http://moviedb.lilplaytime.com None
 * @version  SVN: 1.0
 * @link     http://moviedb.lilplaytime.com
*/


date_default_timezone_set('America/Los_Angeles');
$getcwd = getcwd();
// Windows or Unix separators
$DIR_SEP = (strpos($getcwd, "\\") != 0) ? "\\" : "/";
define("DIR_SEP", $DIR_SEP);
define("ABSPATH", $getcwd . $DIR_SEP);

//define("TEMPLATE_DIR", ABSPATH . 'backend\templates');

define ("LOGS_DIR",  ABSPATH .'_logs');
define ("LOG_PREFIX", 'ep_cal');

define('MINIMUM_ROW_LENGTH', 50);

define ('UFC_PROGRAM_ID', 2);