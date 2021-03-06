<?php
/**
 * O2Bootstrap
 *
 * An open source bootstrap components factory for PHP 5.4+
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
 * @package     O2Bootstrap
 * @author      Circle Creative Dev Team
 * @copyright   Copyright (c) 2005 - 2015, .
 * @license     http://circle-creative.com/products/o2bootstrap/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com/products/o2parser.html
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\Bootstrap\Interfaces;


use O2System\Core\Interfaces;

abstract class FactoryInterface
{
	protected $_tag = NULL;

	/**
	 * HTML Output Attributes
	 *
	 * @access  protected
	 * @type    string
	 */
	protected $_attributes = [ ];

	final public function __construct()
	{
		return call_user_func_array( [ $this, 'build' ], func_get_args() );
	}

	final public function create()
	{
		return call_user_func_array( [ $this, 'build' ], func_get_args() );
	}

	abstract public function build();

	public function setTag( $tag )
	{
		$this->_tag = $tag;

		return $this;
	}

	public function getTag()
	{
		return $this->_tag;
	}

	/**
	 * Set HTML ID
	 *
	 * @param   string $id
	 *
	 * @access  public
	 * @return  $this
	 */
	public function setId( $id )
	{
		$this->addAttribute( 'id', $id );

		return $this;
	}

	public function getClasses()
	{
		return $this->_attributes[ 'class' ];
	}

	public function addClasses( array $classes )
	{
		if ( ! isset( $this->_attributes[ 'class' ] ) )
		{
			$this->_attributes[ 'class' ] = $classes;
		}
		else
		{
			if ( is_string( $this->_attributes[ 'class' ] ) )
			{
				$this->_attributes[ 'class' ] = array_map( 'trim', explode( ' ', $this->_attributes[ 'class' ] ) );
			}

			$this->_attributes[ 'class' ] = array_merge( $this->_attributes[ 'class' ], $classes );
		}

		$this->_attributes[ 'class' ] = array_unique( $this->_attributes[ 'class' ] );

		return $this;
	}

	public function setClass( $class )
	{
		if ( is_string( $class ) )
		{
			$class = array_map( 'trim', explode( ' ', $class ) );
		}

		$this->_attributes[ 'class' ] = $class;

		return $this;
	}

	public function addClass( $class )
	{
		if ( ! isset( $this->_attributes[ 'class' ] ) )
		{
			$this->_attributes[ 'class' ] = [ ];
		}
		elseif ( is_string( $this->_attributes[ 'class' ] ) )
		{
			$this->_attributes[ 'class' ] = array_map( 'trim', explode( ' ', $this->_attributes[ 'class' ] ) );
		}

		array_push( $this->_attributes[ 'class' ], $class );
		$this->_attributes[ 'class' ] = array_unique( $this->_attributes[ 'class' ] );

		return $this;
	}

	public function removeClasses( array $classes )
	{
		if ( isset( $this->_attributes[ 'class' ] ) )
		{
			if ( is_string( $this->_attributes[ 'class' ] ) )
			{
				$this->_attributes[ 'class' ] = array_map( 'trim', explode( ' ', $this->_attributes[ 'class' ] ) );
			}

			$this->_attributes[ 'class' ] = array_diff( $this->_attributes[ 'class' ], $classes );
		}

		return $this;
	}

	public function removeClass( $class )
	{
		return $this->removeClasses( [ $class ] );
	}

	public function hasClass( $class )
	{
		if ( isset( $this->_attributes[ 'class' ] ) )
		{
			if ( is_string( $this->_attributes[ 'class' ] ) )
			{
				$this->_attributes[ 'class' ] = array_map( 'trim', explode( ' ', $this->_attributes[ 'class' ] ) );
			}

			return (bool) in_array( $class, $this->_attributes[ 'class' ] );
		}

		return FALSE;
	}

	public function setAttributes( array $attributes )
	{
		$this->_attributes = $attributes;

		return $this;
	}

	public function getAttributes()
	{
		return $this->_attributes;
	}

	public function addAttributes( array $attributes )
	{
		if ( empty( $this->_attributes ) )
		{
			$this->_attributes = $attributes;
		}
		else
		{
			$this->_attributes = array_merge( $this->_attributes, $attributes );
		}

		return $this;
	}

	public function addAttribute( $name, $value )
	{
		$this->_attributes[ $name ] = $value;

		return $this;
	}

	public function removeAttributes( array $attributes )
	{
		foreach ( $attributes as $attribute )
		{
			$this->removeAttribute( $attribute );
		}

		return $this;
	}

	public function removeAttribute( $attribute )
	{
		unset( $this->_attributes[ $attribute ] );

		return $this;
	}

	public function hasAttribute( $attribute )
	{
		return (bool) array_key_exists( $attribute, $this->_attributes );
	}

	public function getAttribute( $key )
	{
		return isset( $this->_attributes[ $key ] ) ? $this->_attributes[ $key ] : NULL;
	}

	protected function _stringifyAttributes( array $attributes = [ ] )
	{
		$attributes = empty( $attributes ) ? $this->_attributes : $attributes;

		if ( is_object( $attributes ) && count( $attributes ) > 0 )
		{
			$attributes = (array) $attributes;
		}

		if ( is_array( $attributes ) )
		{
			$attr = '';

			if ( count( $attributes ) === 0 )
			{
				return $attr;
			}

			foreach ( $attributes as $key => $value )
			{
				if ( $key === 'class' )
				{
					if ( is_string( $value ) )
					{
						$value = explode( ' ', $value );
					}

					$value = array_map( 'trim', array_unique( $value ) );

					$value = implode( ' ', $value );
				}

				if ( is_array( $value ) )
				{
					$value = implode( ', ', $value );
				}

				if ( is_bool( $value ) )
				{
					$value = $value === TRUE ? 'true' : 'false';
				}

				if ( $key === 'js' )
				{
					$attr .= $key . '=' . $value . ',';
				}
				else
				{
					$attr .= ' ' . $key . '="' . $value . '"';
				}
			}

			return rtrim( $attr, ',' );
		}
		elseif ( is_string( $attributes ) && strlen( $attributes ) > 0 )
		{
			return ' ' . $attributes;
		}

		return $attributes;
	}

	public function __get( $property )
	{
		if ( $property === 'content' )
		{
			if ( isset( $this->_content ) )
			{
				return implode( ' ', $this->_content );
			}
		}
		elseif ( $property === 'contents' )
		{
			if ( isset( $this->_content ) )
			{
				return $this->_content;
			}
		}
		elseif ( $property === 'label' )
		{
			if ( isset( $this->_label ) )
			{
				return implode( ' ', $this->_label );
			}
			elseif ( isset( $this->label ) )
			{
				return $this->label;
			}
		}
		elseif ( $property === 'labels' )
		{
			if ( isset( $this->_label ) )
			{
				return $this->_label;
			}
		}
		elseif ( $property === 'text' )
		{
			if ( isset( $this->_label ) )
			{
				if ( is_string( $this->_label ) )
				{
					return $this->_label;
				}
				elseif ( is_array( $this->_label ) )
				{
					return implode( ' ', $this->_label );
				}
			}
		}
		elseif ( $property === 'input' )
		{
			return $this->_setField();
		}
		elseif ( isset( $this->_items ) AND array_key_exists( $property, $this->_items ) )
		{
			return $this->_items[ $property ];
		}
		elseif ( $property === 'items' )
		{
			if ( isset( $this->_items ) )
			{
				return $this->_items;
			}
		}
		elseif ( isset( $this->_items[ $property ] ) )
		{
			return $this->_items[ $property ];
		}
	}

	public function __call( $method, $args = [ ] )
	{
		if ( method_exists( $this, $method ) )
		{
			return call_user_func_array( [ $this, $method ], $args );
		}
		else
		{
			if ( isset( $this->_contextual_classes ) )
			{
				if ( in_array( ltrim( $method, 'is_' ), $this->_contextual_classes ) )
				{
					return call_user_func_array( [ $this, 'is_' . ltrim( $method, 'is_' ) ], $args );
				}
			}
			elseif ( isset( $this->_sizes ) )
			{
				if ( in_array( ltrim( $method, 'is_' ), $this->_sizes ) )
				{
					return call_user_func_array( [ $this, 'is_' . ltrim( $method, 'is_' ) ], $args );
				}
			}
		}
	}

	abstract public function render();

	public function __toString()
	{
		$output = $this->render();

		return empty( $output ) ? '' : $output;
	}
}