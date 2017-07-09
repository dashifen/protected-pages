<?php

namespace Dashifen\ProtectedPages;

use Dashifen\ProtectedPages\Includes\Controller;

// now, we want to instantiate the controller object for our plugin.
// this object defines all of the actions and filters that we want to
// mess with, and then it's run() method actually connects various
// plugin object methods as callbacks for those hooks.

$ProtectedPagesPlugin = new Controller();
$ProtectedPagesPlugin->run();
