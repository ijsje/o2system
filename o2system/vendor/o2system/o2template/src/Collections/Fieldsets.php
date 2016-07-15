<?php
/**
 * Created by PhpStorm.
 * User: steevenz
 * Date: 1/14/2016
 * Time: 11:37 PM
 */

namespace O2System\Template\Collections;

use O2System\Bootstrap\Factory\Tag;
use O2System\Bootstrap\Factory\Fieldset;
use O2System\Glob\ArrayObject;

class Fieldsets extends ArrayObject
{
	public function __construct( array $fieldsets = [ ] )
	{
		if ( ! empty( $fieldsets ) )
		{
			foreach ( $fieldsets as $group => $fieldset )
			{
				foreach ( $fieldset as $legend => $set )
				{
					$fieldsets[ $group ][] = ( new Fieldset( Fieldset::PANEL_FIELDSET ) )
						->setLegend( $legend )
						->setGroupType( @$set[ 'type' ] )
						->addAttributes( $set[ 'attr' ] )
						->addItems( $set[ 'fields' ] );

					unset( $fieldsets[ $group ][ $legend ] );
				}
			}
		}

		parent::__construct( $fieldsets );
	}

	public function render()
	{
		if ( $this->isEmpty() === FALSE )
		{
			return implode( PHP_EOL, $this->getArrayCopy() );
		}

		return '';
	}

	public function __toString()
	{
		return $this->render();
	}
}