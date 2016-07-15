<?php
/**
 * O2DB
 *
 * Open Source PHP Data Object Wrapper for PHP 5.4.0 or newer
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014, .
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
 * @package     O2ORM
 * @author      Steeven Andrian Salim
 * @copyright   Copyright (c) 2005 - 2014, .
 * @license     http://circle-creative.com/products/o2db/license.html
 * @license     http://opensource.org/licenses/MIT  MIT License
 * @link        http://circle-creative.com
 * @filesource
 */
// ------------------------------------------------------------------------

namespace O2System\DB\Drivers\Cubrid;

// ------------------------------------------------------------------------

use O2System\DB\Interfaces\Forge as ForgeInterface;

/**
 * PDO Cubrid Forge Class
 *
 * Based on CodeIgniter PDO Cubrid Forge Class
 *
 * @category      Database
 * @author        O2System Developer Team
 * @link          http://o2system.in/features/o2db
 */
class Forge extends ForgeInterface
{
	/**
	 * CREATE DATABASE statement
	 *
	 * @type    string
	 */
	protected $_create_database = FALSE;

	/**
	 * DROP DATABASE statement
	 *
	 * @type    string
	 */
	protected $_drop_database = FALSE;

	/**
	 * CREATE TABLE keys flag
	 *
	 * Whether table keys are created from within the
	 * CREATE TABLE statement.
	 *
	 * @type    bool
	 */
	protected $_create_table_keys = TRUE;

	/**
	 * DROP TABLE IF statement
	 *
	 * @type    string
	 */
	protected $_drop_table_if = 'DROP TABLE IF EXISTS';

	/**
	 * UNSIGNED support
	 *
	 * @type    array
	 */
	protected $_unsigned = [
		'SHORT'    => 'INTEGER',
		'SMALLINT' => 'INTEGER',
		'INT'      => 'BIGINT',
		'INTEGER'  => 'BIGINT',
		'BIGINT'   => 'NUMERIC',
		'FLOAT'    => 'DOUBLE',
		'REAL'     => 'DOUBLE',
	];

	// --------------------------------------------------------------------

	/**
	 * ALTER TABLE
	 *
	 * @param    string $alter_type ALTER type
	 * @param    string $table      Table name
	 * @param    mixed  $field      Column definition
	 *
	 * @return    string|string[]
	 */
	protected function _alterTable( $alter_type, $table, $field )
	{
		if ( in_array( $alter_type, [ 'DROP', 'ADD' ], TRUE ) )
		{
			return parent::_alterTable( $alter_type, $table, $field );
		}

		$sql  = 'ALTER TABLE ' . $this->_driver->escapeIdentifiers( $table );
		$sqls = [ ];
		for ( $i = 0, $c = count( $field ); $i < $c; $i++ )
		{
			if ( $field[ $i ][ '_literal' ] !== FALSE )
			{
				$sqls[] = $sql . ' CHANGE ' . $field[ $i ][ '_literal' ];
			}
			else
			{
				$alter_type = empty( $field[ $i ][ 'new_name' ] ) ? ' MODIFY ' : ' CHANGE ';
				$sqls[]     = $sql . $alter_type . $this->_processColumn( $field[ $i ] );
			}
		}

		return $sqls;
	}

	// --------------------------------------------------------------------

	/**
	 * Process column
	 *
	 * @param    array $field
	 *
	 * @return    string
	 */
	protected function _processColumn( $field )
	{
		$extra_clause = isset( $field[ 'after' ] )
			? ' AFTER ' . $this->_driver->escapeIdentifiers( $field[ 'after' ] ) : '';

		if ( empty( $extra_clause ) && isset( $field[ 'first' ] ) && $field[ 'first' ] === TRUE )
		{
			$extra_clause = ' FIRST';
		}

		return $this->_driver->escapeIdentifiers( $field[ 'name' ] )
		. ( empty( $field[ 'new_name' ] ) ? '' : ' ' . $this->_driver->escapeIdentifiers( $field[ 'new_name' ] ) )
		. ' ' . $field[ 'type' ] . $field[ 'length' ]
		. $field[ 'unsigned' ]
		. $field[ 'null' ]
		. $field[ 'default' ]
		. $field[ 'auto_increment' ]
		. $field[ 'unique' ]
		. $extra_clause;
	}

	// --------------------------------------------------------------------

	/**
	 * Field attribute TYPE
	 *
	 * Performs a data type mapping between different databases.
	 *
	 * @param    array &$attributes
	 *
	 * @return    void
	 */
	protected function _attrType( &$attributes )
	{
		switch ( strtoupper( $attributes[ 'TYPE' ] ) )
		{
			case 'TINYINT':
				$attributes[ 'TYPE' ]     = 'SMALLINT';
				$attributes[ 'UNSIGNED' ] = FALSE;

				return;
			case 'MEDIUMINT':
				$attributes[ 'TYPE' ]     = 'INTEGER';
				$attributes[ 'UNSIGNED' ] = FALSE;

				return;
			default:
				return;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Process indexes
	 *
	 * @param    string $table (ignored)
	 *
	 * @return    string
	 */
	protected function _processIndexes( $table )
	{
		$sql = '';

		for ( $i = 0, $c = count( $this->keys ); $i < $c; $i++ )
		{
			if ( is_array( $this->keys[ $i ] ) )
			{
				for ( $i2 = 0, $c2 = count( $this->keys[ $i ] ); $i2 < $c2; $i2++ )
				{
					if ( ! isset( $this->fields[ $this->keys[ $i ][ $i2 ] ] ) )
					{
						unset( $this->keys[ $i ][ $i2 ] );
						continue;
					}
				}
			}
			elseif ( ! isset( $this->fields[ $this->keys[ $i ] ] ) )
			{
				unset( $this->keys[ $i ] );
				continue;
			}

			is_array( $this->keys[ $i ] ) OR $this->keys[ $i ] = [ $this->keys[ $i ] ];

			$sql .= ",\n\tKEY " . $this->_driver->escapeIdentifiers( implode( '_', $this->keys[ $i ] ) )
				. ' (' . implode( ', ', $this->_driver->escapeIdentifiers( $this->keys[ $i ] ) ) . ')';
		}

		$this->keys = [ ];

		return $sql;
	}
}