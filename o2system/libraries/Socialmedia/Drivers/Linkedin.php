<?php
/**
 * O2System
 *
 * An open source application development framework for PHP 5.4+
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2015, PT. Lingkar Kreasi (Circle Creative).
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
 * @package     O2System
 * @author      Circle Creative Dev Team
 * @copyright   Copyright (c) 2005 - 2015, PT. Lingkar Kreasi (Circle Creative).
 * @license     http://circle-creative.com/products/o2system-codeigniter/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com/products/o2system-codeigniter.html
 * @since       Version 2.0
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Libraries\Socialmedia\Drivers;
defined( 'ROOTPATH' ) OR exit( 'No direct script access allowed' );

// ------------------------------------------------------------------------

use O2System\CURL;
use O2System\Libraries\OAuth\Utility;
use O2System\Libraries\Socialmedia\Interfaces\DriverInterface;
use O2System\Libraries\Socialmedia\Metadata\Connection;
use O2System\Libraries\Socialmedia\Metadata\Name;
use O2System\Libraries\Socialmedia\Metadata\Token;

class Linkedin extends DriverInterface
{
	protected $_url = [
		'base'  => 'https://www.linkedin.com/',
		'api'   => 'https://api.linkedin.com/v1/',
		'share' => 'https://www.linkedin.com/shareArticle?mini=true&url=%s',
	];

	public function getShareLink( $link = NULL )
	{
		$link = isset( $link ) ? $link : current_url();

		return sprintf( $this->_url[ 'share' ], urlencode( $link ) );
	}

	public function getAuthorizeLink( $callback )
	{
		$redirect_uri = strpos( $callback, 'http' ) !== FALSE ? $callback : base_url( $callback );
		$state                               = md5( uniqid( mt_rand(), TRUE ) );

		\O2System::Session()->setUserdata(
			'socialmedia_linkedin_request', [
			'redirect_uri' => $redirect_uri,
			'state'        => $state,
		] );

		$curl = new CURL();

		$response = $curl->get(
			$this->_url[ 'base' ], 'oauth/v2/authorization', [
			'response_type' => 'code',
			'redirect_uri'  => $redirect_uri,
			'state'         => $state,
			'client_id'     => $this->_config[ 'client_id' ],
			'client_secret' => $this->_config[ 'client_secret' ],
			'scope'         => implode( ' ', $this->_config[ 'client_scopes' ] ),
		] );

		if ( isset( $response->info->url ) )
		{
			return $response->info->url;
		}
		elseif ( isset( $response->info->redirect_url ) )
		{
			return $response->info->redirect_url;
		}
	}

	/**
	 * Get Authorize Token
	 *
	 * @access  public
	 * @return  array|bool
	 */
	public function getAuthorizeToken()
	{
		if ( $get = \O2System::Input()->get() )
		{
			if ( \O2System::Session()->hasUserdata( 'socialmedia_linkedin_request' ) )
			{
				$request = \O2System::Session()->userdata( 'socialmedia_linkedin_request' );

				$curl = new CURL();

				if ( $get->offsetExists( 'code' ) )
				{
					$response = $curl->post(
						$this->_url[ 'base' ] . 'oauth/v2/accessToken', [
						'grant_type'    => 'authorization_code',
						'code'          => $get->code,
						'redirect_uri'  => $request[ 'redirect_uri' ],
						'client_id'     => $this->_config[ 'client_id' ],
						'client_secret' => $this->_config[ 'client_secret' ],
					] );

					if ( $response->info->http_code === 200 )
					{
						return $response->data->__toArray();
					}
				}
				elseif ( $get->offsetExists( 'error' ) )
				{
					$this->_errors[] = $get->error . ': ' . $get->error_description;
				}
				else
				{
					$this->_errors[] = 'User-Canceled: Canceled by User';
				}
			}
			else
			{
				$this->_errors[] = 'Session: Undefined Linkedin Request';
			}
		}
		else
		{
			$this->_errors[] = 'GET: Undefined Linkedin Feedback Code';
		}

		return FALSE;
	}


	/**
	 * Set Connection
	 *
	 * @param Connection|array $connection
	 *
	 * @return bool
	 */
	public function setConnection( $connection )
	{
		if ( $connection instanceof Connection )
		{
			if ( isset( $connection->token[ 'access_token' ] ) )
			{
				$this->_connection = new Connection();

				if ( $connection->token instanceof Token )
				{
					$this->_connection->setToken( $connection->token->__toArray() );
				}
				else
				{
					$this->_connection->setToken( (array) $connection->token );
				}
			}
		}
		elseif ( is_array( $connection ) )
		{
			if ( isset( $connection[ 'access_token' ] ) )
			{
				$this->_connection = new Connection();
				$this->_connection->setToken( $connection );
			}
		}

		if ( $this->isConnected() )
		{
			return $this->__buildConnection();
		}

		return FALSE;
	}

	/**
	 * Connection Builder
	 *
	 * @return bool
	 */
	protected function __buildConnection()
	{
		if ( $user_profile = $this->getUserProfile() )
		{
			$x_publicProfileUrl     = explode( '/', $user_profile->publicProfileUrl );
			$user_profile->username = end( $x_publicProfileUrl );

			$this->_connection->setUserProfile(
				[
					'id'          => $user_profile->id,
					'username'    => $user_profile->username,
					'name'        => new Name(
						[
							'first' => $user_profile->firstName,
							'last'  => $user_profile->lastName,
						] ),
					'description' => $user_profile->summary,
					'url'         => $user_profile->publicProfileUrl,
					'avatar'      => $user_profile->pictureUrl,
					'cover'       => end( $user_profile->pictureUrls[ 'values' ] ),
				] );

			$this->_connection->setMetadata( $user_profile->__toArray() );

			return TRUE;
		}

		return FALSE;
	}

	public function request( $path = NULL, $params = [ ], $method = 'GET' )
	{
		$curl = new CURL();

		$curl->setHeaders(
			[
				'x-li-format'   => 'json',
				'authorization' => 'Bearer ' . $this->_connection[ 'token' ]->access_token,
				'content-type'  => 'application/json',
			] );

		$curl->setOption( CURLOPT_FOLLOWLOCATION, TRUE );

		$params[ 'format' ] = 'json';

		$response = $curl->request( $this->_url[ 'api' ] . $path, $params, $method );

		if ( $response->info->http_code === 200 )
		{
			return $response->data;
		}

		return FALSE;
	}

	public function getUserProfile( array $fields = [ 'id', 'first-name', 'last-name', 'email-address', 'summary', 'picture-url', 'public-profile-url', 'picture-urls::(original)' ] )
	{
		$path = 'people/~:(' . implode( ',', $fields ) . ')';

		if ( $profile = $this->request( $path ) )
		{
			return $profile;
		}

		return FALSE;
	}
}