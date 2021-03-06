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

namespace O2System\Template\Collections\Metadata;

// ------------------------------------------------------------------------

use O2System\Core\SPL\ArrayAccess;

/**
 * Template Opengraph Driver
 *
 * @package          Template
 * @subpackage       Library
 * @category         Driver
 * @version          1.0 Build 11.09.2012
 * @author           Circle Creative Developer Team
 * @copyright        Copyright (c) 2005 - 2014
 * @license          http://www.circle-creative.com/products/o2system/license.html
 * @link             http://www.circle-creative.com
 */
// ------------------------------------------------------------------------

class Opengraph extends ArrayAccess
{
	const WEBSITE = 'website';
	const PROFILE = 'profile';
	const MUSIC   = 'music';
	const VIDEO   = 'video';
	const BOOK    = 'book';
	const ARTICLE = 'article';
	const PRODUCT = 'product';
	const SERVICE = 'service';

	protected $namespace;

	public function setNamespace( $namespace )
	{
		$this->namespace = strtolower( $namespace );

		if ( $this->namespace === 'website' )
		{
			$this->setType( 'website' );
		}
	}

	public function setLocale( $locale, array $alternates = [ ] )
	{
		$this->storage[ 'og:locale' ] = $locale;

		if ( count( $alternates ) > 0 )
		{
			foreach ( $alternates as $alternate )
			{
				$this->storage[ 'og:locale:alternate' ][] = $alternate;
			}
		}
	}

	public function setTitle( $title )
	{
		$this->storage[ 'og:title' ] = $title;
	}

	public function setType( $type )
	{
		$this->storage[ 'og:type' ] = $type;
	}

	public function setUrl( $url )
	{
		$this->storage[ 'og:url' ] = $url;
	}

	public function setSiteName( $site_name )
	{
		$this->storage[ 'og:site_name' ] = $site_name;
	}

	public function setDescription( $description )
	{
		$this->storage[ 'og:description' ] = $description;
	}

	public function setImage( $image, array $properties = [ ] )
	{
		$this->storage[ 'og:image' ] = $image;

		if ( count( $properties ) > 0 )
		{
			foreach ( $properties as $key => $value )
			{
				$this->storage[ 'og:image:' . $key ][] = $value;
			}
		}
	}

	public function setAudio( $audio, array $properties = [ ] )
	{
		$this->storage[ 'og:audio' ] = $audio;

		if ( count( $properties ) > 0 )
		{
			foreach ( $properties as $key => $value )
			{
				$this->storage[ 'og:audio:' . $key ][] = $value;
			}
		}
	}

	public function setMusic( $music, array $properties = [ ] )
	{
		$this->setNamespace( self::MUSIC );
		$this->storage[ 'og:music' ] = $music;

		if ( count( $properties ) > 0 )
		{
			foreach ( $properties as $key => $value )
			{
				$this->storage[ 'og:audio:' . $key ][] = $value;
			}
		}
	}

	public function setVideo( $video, array $properties = [ ] )
	{
		$this->setNamespace( self::VIDEO );
		$this->storage[ 'og:video' ] = $video;

		if ( count( $properties ) > 0 )
		{
			foreach ( $properties as $key => $value )
			{
				$this->storage[ 'og:video:' . $key ][] = $value;
			}
		}
	}

	public function __call( $method, array $args = [ ] )
	{
		if ( method_exists( $this, $method ) )
		{
			call_user_func_array( [ $this, $method ], $args );
		}
		elseif ( strrpos( $method, 'Properties' ) !== FALSE )
		{
			$object_key = str_replace( [ 'set', 'Properties' ], '', $method );
			$object_key = underscore( $object_key );

			list( $object_properties ) = $args;

			if ( isset( $object_properties ) AND is_array( $object_properties ) )
			{
				foreach ( $object_properties as $key => $value )
				{
					if ( is_array( $value ) )
					{
						foreach ( $value as $item )
						{
							$this->storage[ 'og:' . $object_key . ':' . $key ][] = $item;
						}
					}
					else
					{
						$this->storage[ 'og:' . $object_key . ':' . $key ] = $value;
					}
				}
			}
		}
		elseif ( strpos( $method, 'set' ) !== FALSE )
		{
			$object_key = str_replace( 'set', '', $method );
			$object_key = underscore( $object_key );

			@list( $object_value, $object_properties ) = $args;

			$this->storage[ 'og:' . $object_key ] = $object_value;

			if ( isset( $object_properties ) AND is_array( $object_properties ) )
			{
				foreach ( $object_properties as $key => $value )
				{
					$key = underscore( $key );

					if ( is_array( $value ) )
					{
						foreach ( $value as $item )
						{
							$this->storage[ 'og:' . $object_key . ':' . $key ][] = $item;
						}
					}
					else
					{
						$this->storage[ 'og:' . $object_key . ':' . $key ] = $value;
					}
				}
			}
		}
	}

	public function setFacebookAppId( $facebook_app_id )
	{
		$this->storage[ 'fb:app_id' ] = $facebook_app_id;
	}

	public function getHtmlOpenTagAttributes()
	{
		return [
			'xmlns'    => 'http://www.w3.org/1999/xhtml',
			'xmlns:og' => 'http://ogp.me/ns#',
			'xmlns:fb' => 'https://www.facebook.com/2008/fbml',
		];
	}

	public function getHtmlOpenTag( $previous_html_tag = NULL )
	{
		$attributes = $this->getHtmlOpenTagAttributes();

		if ( isset( $previous_html_tag ) )
		{
			$previous_attributes = parse_attributes( $previous_html_tag );

			$attributes = array_merge( $previous_attributes, $attributes );
		}

		return '<html' . _stringify_attributes( $attributes ) . '>';
	}

	public function getHeadOpenTagAttributes()
	{
		if ( empty( $this->namespace ) )
		{
			return [
				'prefix' => 'og: http://ogp.me/ns#',
			];
		}
		else
		{
			return [
				'prefix' => $this->namespace . ': http://ogp.me/' . $this->namespace . '#',
			];
		}
	}

	public function getHeadOpenTag( $previous_head_tag = NULL )
	{
		$attributes = $this->getHeadOpenTagAttributes();

		if ( isset( $previous_head_tag ) )
		{
			$previous_attributes = parse_attributes( $previous_head_tag );

			$attributes = array_merge( $previous_attributes, $attributes );
		}

		return '<head' . _stringify_attributes( $attributes ) . '>';
	}

	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Render Metadata
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function render()
	{
		if ( ! empty( $this->storage ) )
		{
			return implode( PHP_EOL, $this->storage );
		}

		return '';
	}
}