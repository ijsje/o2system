<?php
/**
 * YukBisnis.com
 *
 * Application Engine under O2System Framework for PHP 5.4 or newer
 *
 * This content is released under PT. Yuk Bisnis Indonesia License
 *
 * Copyright (c) 2015, PT. Yuk Bisnis Indonesia.
 *
 * @package        Applications
 * @author         Steeven Andrian Salim
 * @copyright      Copyright (c) 2015, PT. Yuk Bisnis Indonesia.
 * @since          Version 2.0.0
 * @filesource
 */

// ------------------------------------------------------------------------

namespace Test\Plugins\Testing\Controllers;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Core\Controller;

/**
 * Test Controller
 *
 * @package       Applications
 * @subpackage    Controllers
 * @category      Global Controller
 * @version       1.0.0
 */
class Hellow extends Controller
{
	public function index()
	{
		$this->view->load( 'hellow' );
	}

}