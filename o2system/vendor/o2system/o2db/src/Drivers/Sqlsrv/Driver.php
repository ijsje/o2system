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

namespace O2System\DB\Drivers\Sqlsrv;

// ------------------------------------------------------------------------

use O2System\DB\Exception;
use O2System\DB\Interfaces\Driver as DriverInterface;

/**
 * PDO SQLSERV Driver Adapter Class
 *
 * Based on CodeIgniter PDO SQLSERV Driver Adapter Class
 *
 * @category      Database
 * @author        O2System Developer Team
 * @link          http://o2system.in/features/o2db
 */
class Driver extends DriverInterface
{
	/**
	 * Platform
	 *
	 * @type    string
	 */
	public $platform = 'SQLSERV';

	// --------------------------------------------------------------------

	/**
	 * ORDER BY random keyword
	 *
	 * @type    array
	 */
	protected $_random_keywords = [ 'NEWID()', 'RAND(%d)' ];

	/**
	 * Quoted identifier flag
	 *
	 * Whether to use SQL-92 standard quoted identifier
	 * (double quotes) or brackets for identifier escaping.
	 *
	 * @type    bool
	 */
	protected $_quoted_identifier;

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * Builds the DSN if not already set.
	 *
	 * @param    array $params
	 *
	 * @return    void
	 */
	public function __construct( $params )
	{
		parent::__construct( $params );

		if ( empty( $this->dsn ) )
		{
			$this->dsn = 'sqlsrv:Server=' . ( empty( $this->hostname ) ? '127.0.0.1' : $this->hostname );

			empty( $this->port ) OR $this->dsn .= ',' . $this->port;
			empty( $this->database ) OR $this->dsn .= ';Database=' . $this->database;

			// Some custom options

			if ( isset( $this->QuotedId ) )
			{
				$this->dsn .= ';QuotedId=' . $this->QuotedId;
				$this->_quoted_identifier = (bool) $this->QuotedId;
			}

			if ( isset( $this->ConnectionPooling ) )
			{
				$this->dsn .= ';ConnectionPooling=' . $this->ConnectionPooling;
			}

			if ( $this->encrypt === TRUE )
			{
				$this->dsn .= ';Encrypt=1';
			}

			if ( isset( $this->TraceOn ) )
			{
				$this->dsn .= ';TraceOn=' . $this->TraceOn;
			}

			if ( isset( $this->TrustServerCertificate ) )
			{
				$this->dsn .= ';TrustServerCertificate=' . $this->TrustServerCertificate;
			}

			empty( $this->APP ) OR $this->dsn .= ';APP=' . $this->APP;
			empty( $this->Failover_Partner ) OR $this->dsn .= ';Failover_Partner=' . $this->Failover_Partner;
			empty( $this->LoginTimeout ) OR $this->dsn .= ';LoginTimeout=' . $this->LoginTimeout;
			empty( $this->MultipleActiveResultSets ) OR $this->dsn .= ';MultipleActiveResultSets=' . $this->MultipleActiveResultSets;
			empty( $this->TraceFile ) OR $this->dsn .= ';TraceFile=' . $this->TraceFile;
			empty( $this->WSID ) OR $this->dsn .= ';WSID=' . $this->WSID;
		}
		elseif ( preg_match( '/QuotedId=(0|1)/', $this->dsn, $match ) )
		{
			$this->_quoted_identifier = (bool) $match[ 1 ];
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Database connection
	 *
	 * @param    bool $persistent
	 *
	 * @return    object
	 */
	public function dbConnect( $persistent = FALSE )
	{
		if ( ! empty( $this->charset ) && preg_match( '/utf[^8]*8/i', $this->charset ) )
		{
			$this->options[ PDO::SQLSRV_ENCODING_UTF8 ] = 1;
		}

		$this->pdo_conn = parent::dbConnect( $persistent );

		if ( ! is_object( $this->pdo_conn ) OR is_bool( $this->_quoted_identifier ) )
		{
			return $this->pdo_conn;
		}

		// Determine how identifiers are escaped
		$query                    = $this->query( 'SELECT CASE WHEN (@@OPTIONS | 256) = @@OPTIONS THEN 1 ELSE 0 END AS qi' );
		$query                    = $query->rowArray();
		$this->_quoted_identifier = empty( $query ) ? FALSE : (bool) $query[ 'qi' ];
		$this->_escape_character  = ( $this->_quoted_identifier ) ? '"' : [ '[', ']' ];

		return $this->pdo_conn;
	}

	// --------------------------------------------------------------------

	/**
	 * Show table query
	 *
	 * Generates a platform-specific query string so that the table names can be fetched
	 *
	 * @param    bool $prefix_limit
	 *
	 * @return    string
	 */
	protected function _listTablesStatement( $prefix_limit = FALSE )
	{
		$sql = 'SELECT ' . $this->escapeIdentifiers( 'name' )
			. ' FROM ' . $this->escapeIdentifiers( 'sysobjects' )
			. ' WHERE ' . $this->escapeIdentifiers( 'type' ) . " = 'U'";

		if ( $prefix_limit === TRUE && $this->table_prefix !== '' )
		{
			$sql .= ' AND ' . $this->escapeIdentifiers( 'name' ) . " LIKE '" . $this->escapeLikeString( $this->table_prefix ) . "%' "
				. sprintf( $this->_like_escape_string, $this->_like_escape_character );
		}

		return $sql . ' ORDER BY ' . $this->escapeIdentifiers( 'name' );
	}

	// --------------------------------------------------------------------

	/**
	 * Show column query
	 *
	 * Generates a platform-specific query string so that the column names can be fetched
	 *
	 * @param    string $table
	 *
	 * @return    string
	 */
	protected function _listColumnsStatement( $table = '' )
	{
		return 'SELECT COLUMN_NAME
			FROM INFORMATION_SCHEMA.Columns
			WHERE UPPER(TABLE_NAME) = ' . $this->escape( strtoupper( $table ) );
	}

	// --------------------------------------------------------------------

	/**
	 * Returns an object with field data
	 *
	 * @param    string $table
	 *
	 * @return    array
	 */
	public function fieldData( $table )
	{
		$sql = 'SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, COLUMN_DEFAULT
			FROM INFORMATION_SCHEMA.Columns
			WHERE UPPER(TABLE_NAME) = ' . $this->escape( strtoupper( $table ) );

		if ( ( $query = $this->query( $sql ) ) === FALSE )
		{
			return FALSE;
		}
		$query = $query->resultObject();

		$result = [ ];
		for ( $i = 0, $c = count( $query ); $i < $c; $i++ )
		{
			$result[ $i ]             = new stdClass();
			$result[ $i ]->name       = $query[ $i ]->COLUMN_NAME;
			$result[ $i ]->type       = $query[ $i ]->DATA_TYPE;
			$result[ $i ]->max_length = ( $query[ $i ]->CHARACTER_MAXIMUM_LENGTH > 0 ) ? $query[ $i ]->CHARACTER_MAXIMUM_LENGTH : $query[ $i ]->NUMERIC_PRECISION;
			$result[ $i ]->default    = $query[ $i ]->COLUMN_DEFAULT;
		}

		return $result;
	}

	// --------------------------------------------------------------------

	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data
	 *
	 * @param    string $table
	 * @param    array  $values
	 *
	 * @return    string
	 */
	protected function _updateStatement( $table, $values )
	{
		$this->qb_limit   = FALSE;
		$this->qb_orderby = [ ];

		return parent::_updateStatement( $table, $values );
	}

	// --------------------------------------------------------------------

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @param    string $table
	 *
	 * @return    string
	 */
	protected function _delete( $table )
	{
		if ( $this->qb_limit )
		{
			return 'WITH ci_delete AS (SELECT TOP ' . $this->qb_limit . ' * FROM ' . $table . $this->_compileWhereHaving( 'qb_where' ) . ') DELETE FROM ci_delete';
		}

		return parent::_delete( $table );
	}

	// --------------------------------------------------------------------

	/**
	 * LIMIT
	 *
	 * Generates a platform-specific LIMIT clause
	 *
	 * @param    string $sql SQL Query
	 *
	 * @return    string
	 */
	protected function _limit( $sql )
	{
		// As of SQL Server 2012 (11.0.*) OFFSET is supported
		if ( version_compare( $this->version(), '11', '>=' ) )
		{
			// SQL Server OFFSET-FETCH can be used only with the ORDER BY clause
			empty( $this->qb_orderby ) && $sql .= ' ORDER BY 1';

			return $sql . ' OFFSET ' . (int) $this->qb_offset . ' ROWS FETCH NEXT ' . $this->qb_limit . ' ROWS ONLY';
		}

		$limit = $this->qb_offset + $this->qb_limit;

		// An ORDER BY clause is required for ROW_NUMBER() to work
		if ( $this->qb_offset && ! empty( $this->qb_orderby ) )
		{
			$orderby = $this->_compileOrderBy();

			// We have to strip the ORDER BY clause
			$sql = trim( substr( $sql, 0, strrpos( $sql, $orderby ) ) );

			// Get the fields to select from our subquery, so that we can avoid O2DB_rownum appearing in the actual results
			if ( count( $this->qb_select ) === 0 )
			{
				$select = '*'; // Inevitable
			}
			else
			{
				// Use only field names and their aliases, everything else is out of our scope.
				$select       = [ ];
				$field_regexp = ( $this->_quoted_identifier )
					? '("[^\"]+")' : '(\[[^\]]+\])';
				for ( $i = 0, $c = count( $this->qb_select ); $i < $c; $i++ )
				{
					$select[] = preg_match( '/(?:\s|\.)' . $field_regexp . '$/i', $this->qb_select[ $i ], $m )
						? $m[ 1 ] : $this->qb_select[ $i ];
				}
				$select = implode( ', ', $select );
			}

			return 'SELECT ' . $select . " FROM (\n\n"
			. preg_replace( '/^(SELECT( DISTINCT)?)/i', '\\1 ROW_NUMBER() OVER(' . trim( $orderby ) . ') AS ' . $this->escapeIdentifiers( 'O2DB_rownum' ) . ', ', $sql )
			. "\n\n) " . $this->escapeIdentifiers( 'O2DB_subquery' )
			. "\nWHERE " . $this->escapeIdentifiers( 'O2DB_rownum' ) . ' BETWEEN ' . ( $this->qb_offset + 1 ) . ' AND ' . $limit;
		}

		return preg_replace( '/(^\SELECT (DISTINCT)?)/i', '\\1 TOP ' . $limit . ' ', $sql );
	}

	// --------------------------------------------------------------------

	/**
	 * Insert batch statement
	 *
	 * Generates a platform-specific insert string from the supplied data.
	 *
	 * @param    string $table  Table name
	 * @param    array  $keys   INSERT keys
	 * @param    array  $values INSERT values
	 *
	 * @return    string|bool
	 */
	protected function _insertBatch( $table, $keys, $values )
	{
		// Multiple-value inserts are only supported as of SQL Server 2008
		if ( version_compare( $this->version(), '10', '>=' ) )
		{
			return parent::_insertBatch( $table, $keys, $values );
		}

		throw new Exception( 'Unsupported feature of the database platform you are using.' );
	}
}