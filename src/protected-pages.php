<?php

namespace Dashifen\ProtectedPages;

use Dashifen\ProtectedPages\Includes\Controller;
use Dashifen\WPPB\Loader\Loader;

// now, we want to instantiate the controller object for our plugin.
// this object defines all of the actions and filters that we want to
// mess with, and then it's attachHooks() method actually connects
// various plugin object methods as callbacks for those hooks.

$protectedPagesLoader = new Loader();
$protectedPagesPlugin = new Controller(
	PROTECTED_PAGES_VERSION,
	$protectedPagesLoader
);

$protectedPagesPlugin->attachHooks();
