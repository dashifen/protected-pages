<?php

namespace Dashifen\ProtectedPages\Includes;

use Dashifen\Exception\Exception as DashifenException;

class Exception extends DashifenException {
	
	// the parent Exception class contains a constructor and the necessary
	// code to utilize constants in its children to accurately code the
	// exceptions encountered within this plugin.  so, all we need to do
	// here is actually define those constants.
	
	public const UNKNOWN_PLUGIN_OBJECT = 1;
	public const UNKNOWN_METHOD = 2;
	
}
