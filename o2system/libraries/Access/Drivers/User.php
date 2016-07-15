<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, .
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package        O2System
 * @author         Circle Creative Dev Team
 * @copyright      Copyright (c) 2005 - 2015, .
 * @license        http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license        http://opensource.org/licenses/MIT	MIT License
 * @link           http://circle-creative.com/products/o2system-codeigniter.html
 * @since          Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Libraries\Access\Drivers;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\Glob\ArrayObject;
use O2System\Glob\Interfaces\DriverInterface;
use O2System\Model;

/**
 * User Driver Class
 *
 * @package        O2System
 * @subpackage     libraries/Access/drivers
 * @category       Driver Class
 * @author         Steeven Andrian Salim
 * @link           http://o2system.center/products/o2system-ci3.0.0/user-guide/libraries/access/login.html
 */
class User extends DriverInterface
{
	/**
	 * User Account
	 *
	 * @param string $return
	 *
	 * @return array|null|object
	 */
	public function account( $return = NULL )
	{
		if ( $this->isLogin() )
		{
			if ( $account = \O2System::Session()->userdata( 'account' ) )
			{
				if ( $account instanceof \ArrayObject )
				{
					if ( isset( $return ) )
					{
						if ( $account->offsetExists( $return ) )
						{
							return $account->offsetGet( $return );
						}
					}

					return $account;
				}
			}
		}

		\O2System::Session()->unsetUserdata( 'account' );

		return NULL;
	}

	// ------------------------------------------------------------------------

	/**
	 * Is Login
	 *
	 * Check if user is already login
	 *
	 * @access  public
	 * @return  bool
	 */
	public function isLogin()
	{
		if ( \O2System::Session()->hasUserdata( 'account' ) )
		{
			$account = \O2System::Session()->userdata( 'account' );

			if ( $account instanceof ArrayObject )
			{
				if ( $account->isEmpty() === FALSE )
				{
					\O2System::$active[ 'account' ] = $account;

					return TRUE;
				}
			}

			\O2System::Session()->unsetUserdata( 'account' );

			return $this->isLogin();
		}
		elseif ( \O2System::Useragent()->isBrowser() )
		{
			if ( $credentials = $this->_library->cookie->getRemember() )
			{
				return $this->_library->login->fromCredentials( $credentials );
			}
			elseif ( $credentials = $this->_library->cookie->getSso() )
			{
				return $this->_library->login->fromCredentials( $credentials );
			}
		}

		if ( $http_authorization = \O2System::Input()->server( 'HTTP_AUTHORIZATION_TOKEN' ) )
		{
			list( $token ) = sscanf( $http_authorization, 'WT-%s' );

			if ( isset( $token ) )
			{
				if ( $credentials = $this->_library->token->getCredentials( $token ) )
				{
					return $this->_library->login->fromCredentials( $credentials );
				}
			}
		}

		return FALSE;
	}

	// ------------------------------------------------------------------------


	/**
	 * Logout
	 *
	 * Destroy session, cookie, sso for active user
	 *
	 * @access  public
	 */
	public function destroy()
	{
		foreach ( [ 'remember', 'sso' ] as $cookie_name )
		{
			if ( $credentials = $this->_library->cookie->{'get' . ucfirst( $cookie_name )}() )
			{
				if ( $cache = \O2System::Cache()->get( $cookie_name . '-' . $credentials[ 'signature' ] ) )
				{
					\O2System::Cache()->delete( $cookie_name . '-' . $credentials[ 'signature' ] );
				}
			}
		}

		if ( $cache = \O2System::Cache()->get( 'token-' . $credentials[ 'signature' ] ) )
		{
			\O2System::Cache()->delete( 'token-' . $credentials[ 'signature' ] );
		}

		$this->_library->cookie->destroy();
		\O2System::Session()->destroy();
	}
}
