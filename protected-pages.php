<?php
/*
Plugin Name: Protected Pages
Plugin URI: https://github.com/dashifen/protected-pages
Description: A WordPress plugin to create protected pages available from from a list of specified domains.
Version: 1.0.0
Author: David Dashifen Kees
Author URI: https://dashifen.com
*/

// if this file is called directly, abort.

if (!defined("WPINC")) {
	die;
}

// in the past, i"ve edited the version number above without doing so here.
// to avoid this, and because this file is short, i read this file"s content
// and rip the version out of the comment for our definition here.

$content = file_get_contents(__FILE__);
preg_match("/Version: ([\.0-9]+)/", file_get_contents(__FILE__), $matches);
define("PROTECTED_PAGES_VERSION", $matches[1]);

// now, there's two files to include:  our PSR-4 autoloader and the
// PHP file which initializes our plugin.  my IDE gets confused when
// trying to include these files as it finds the $path variable
// incomprehensible in this context.  but, it ignores dynamic includes
// so we'll do that instead.

$path = plugin_dir_path(__FILE__);
$autoloader = $path . "vendor/autoload.php";
$plugin = $path . "vendor/protected-pages.php";

require_once $autoloader;
require_once $plugin;
