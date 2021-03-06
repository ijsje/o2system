<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/26/2016
 * Time: 9:50 PM
 */

namespace O2System\Bootstrap\Components;

use O2System\Bootstrap\Interfaces\FactoryInterface;

class Slide extends FactoryInterface
{
	protected $_tag        = 'div';
	protected $_attributes = [
		'class' => [ 'container' ],
	];

	public $image       = NULL;
	public $caption     = NULL;
	public $description = NULL;
	public $link        = NULL;
	public $buttons     = [ ];

	// ------------------------------------------------------------------------

	/**
	 * build
	 *
	 * @return object
	 */
	public function build()
	{
		@list( $attr ) = func_get_args();

		if ( is_array( $attr ) )
		{
			$this->addAttributes( $attr );
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	public function __clone()
	{
		foreach ( [ 'image', 'caption', 'description', 'link' ] as $object )
		{
			if ( is_object( $this->{$object} ) )
			{
				$this->{$object} = clone $this->{$object};
			}
		}

		foreach ( $this->buttons as $key => $button )
		{
			$this->buttons[ $key ] = clone $button;
		}

		return $this;
	}

	public function setImage( $image )
	{
		if ( $image instanceof Image )
		{
			$this->image = $image;
		}
		else
		{
			$this->image = new Image( Image::RESPONSIVE_IMAGE );
			$this->image->setSource( $image );
		}

		return $this;
	}

	/**
	 * Alert Title
	 *
	 * @param string $caption
	 * @param string $tag
	 *
	 * @return object
	 */
	public function setCaption( $caption, $tag = 'h1', $attr = [ ] )
	{
		if ( is_array( $tag ) )
		{
			$attr = $tag;
		}

		if ( $caption instanceof FactoryInterface )
		{
			$this->caption = clone $caption;
			$this->caption->setTag( $tag );
			$this->caption->addClass( 'carousel-header' );
		}
		else
		{
			if ( isset( $attr[ 'class' ] ) )
			{
				if ( is_array( $attr[ 'class' ] ) )
				{
					array_push( $attr[ 'class' ], 'carousel-header' );
				}
				else
				{
					$attr[ 'class' ] = $attr[ 'class' ] . ' carousel-header';
				}
			}
			else
			{
				$attr[ 'class' ] = 'carousel-header';
			}

			$this->caption = new Tag( $tag, ucwords( $caption ), $attr );
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	public function setDescription( $description, $tag = 'p', $attr = [ ] )
	{
		if ( is_array( $tag ) )
		{
			$attr = $tag;
			$tag  = 'p';
		}

		if ( $description instanceof Tag )
		{
			$this->description = $description;
		}
		else
		{
			$this->description = new Tag( $tag, $description, $attr );
		}

		return $this;
	}

	/**
	 * link
	 *
	 * @param string $link
	 * @param string $href
	 * @param string $attributes
	 *
	 * @return object
	 */
	public function setLink( $link, $attr = [ ] )
	{
		if ( $link instanceof Link )
		{
			$this->link = clone $link;
		}
		else
		{
			$this->link = new Link( '', $link, $attr );
		}

		return $this;
	}

	// ------------------------------------------------------------------------

	public function addButton( $label, $attr = [ ] )
	{
		if ( $label instanceof Button )
		{
			$this->buttons[] = $label;
		}
		elseif ( $label instanceof Link )
		{
			$this->buttons[] = $label;
		}
		else
		{
			$this->buttons = new Button( $label, $attr );
		}

		return $this;
	}

	/**
	 * Render
	 *
	 * @return
	 */
	public function render()
	{
		if ( ! empty( $this->image ) )
		{
			if ( isset( $this->link ) )
			{
				$image = clone $this->link;
				$image->setLabel( $this->image );

				$this->link->setLabel( $this->caption );
				$caption = new Tag( 'div', $this->link, [ 'class' => 'carousel-caption' ] );
			}
			else
			{
				$image   = $this->image;
				$caption = new Tag( 'div', $this->caption, [ 'class' => 'carousel-caption' ] );
			}

			if ( isset( $this->description ) )
			{
				$caption->appendContent( $this->description );
			}

			if ( ! empty( $this->buttons ) )
			{
				$button = new Tag( 'p', implode( PHP_EOL, $this->buttons ), [ 'class' => 'thumbnail-buttons' ] );

				$caption->appendContent( $button );
			}

			return $image . ( new Tag( $this->_tag, $caption, $this->_attributes ) )->render();
		}

		return '';
	}
}