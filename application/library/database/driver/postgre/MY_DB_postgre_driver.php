<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Postgre Database Adapter Class extension
 */
class MY_DB_postgre_driver extends CI_DB_postgre_driver
{

	/**
	 * PostgreSQL DB driver extension construct
	 *
	 * @param	void
	 * @return	void
	 */
	public function __construct( $param )
	{
		parent::__construct( $param );
		log_message('debug', _('Extended PostgreSQL database driver class instantiated.'));
	}

	/**
	 * Starts a copy statement, to copy data between file and table
	 * More details: http://www.postgresql.org/docs/9.2/static/sql-copy.html
	 *
	 * @access	public
	 * @param	string	name of the table
	 * @param	array	a list of columns
	 * @param	string	defines the direction of the transmission of data (FROM/TO)
	 * @param	string	form of target stdin/out or file
	 * @param	array	contains options for the statement
	 * @return	bool
	 */
	public function copy_start( $table_name, $cols = NULL, $indicator = 'FROM', $target = 'STDIN', array $options = array() )
	{
		$sql = 'COPY ' . $table_name;

		if( $cols !== NULL && sizeof($cols) )
		{
			$sql .= ' (';
			$sql .= rtrim(implode(',', $cols), ',');
			$sql .= ')';
		}

		// Append indicator and target to the query string
		if( $indicator !== NULL && $target !== NULL )
		{
			$indicator = strtoupper($indicator);
			$sql .= ' '.$indicator.' ';

			$sql .= ( (bool) preg_match('/^((?!STDIN|STDOUT).)*$/i', $target)) ? '\''.$target.'\'' : $target;
		}

		// Append options to the query string
		foreach( $options as $option )
		{
			list( $param, $val, $quote ) = $option;

			( $quote === TRUE ) && $val = '\''.$val.'\''; // put value in quotes

			( $param !== NULL ) && $sql .= strtoupper($param).' '; // define param
			( $val !== NULL ) && $sql .= $val.' '; // define value
		}

		// Remove spaces from both ends of the query string
		$sql = trim($sql);

		$query = $this->query($sql);

		if( $query )
		{
			return TRUE;
		}

		return FALSE;

	}

	// --------------------------------------------------------------------

	/**
	 * Writes a line to stdin
	 *
	 * @param	string	contains a tab-separated line
	 * @param	bool	escape the line, if lines contain escaped characters
	 * @return	bool
	 */
	public function copy_put_line($line, $escape = FALSE)
	{
		( $escape === TRUE) && $line = "E'{$line}'";

		$line .= "\n";

		return @pg_put_line($this->conn_id, $line);
	}

	// --------------------------------------------------------------------

	/**
	 * Converts an array to a string and writes it to stdin
	 *
	 * @param	array	contains field values for a row
	 * @param	bool	escape the line 
	 * @return	bool
	 */
	public function copy_put_line_array( array $lines, $escape = FALSE )
	{
		$line = '';

		foreach( $lines as $_line )
		{
			$line .= str_replace("\t", '\t', $_line);
			$line .= "\t";
		}

		return $this->put_line( $line, $escape );
	}

	// --------------------------------------------------------------------

	/**
	 * Closes the copy statement
	 *
	 * @param	bool	determines if the copy process should be ended automatically
	 * @return	bool
	 */
	public function copy_end( $insert_ending = FALSE )
	{
		( $insert_ending === TRUE ) && $this->put_line('\,');

		return @pg_end_copy($this->conn_id);
	}

	// --------------------------------------------------------------------

	/**
	 * Wrapper function for PHP's pg_copy_to function
	 *
	 * @param	string	name of a table
	 * @param	string	delimiter. default TAB
	 * @param	string	SQL NULL values representation form. default \N
	 * @return	array	
	 */
	public function copy_to( $table_name, $delimiter = "\t", $null_as = "\\N" )
	{
		$rows = pg_copy_to($this->conn_id, $table_name);

		return $rows;
	}

	// --------------------------------------------------------------------

	/**
	 * CI query builder wrapper for PHP's pg_copy_from function
	 *
	 * @param	string	the name of the table
	 * @param	array	a formatted set of rows
	 * @param	string	delimiter. default TAB
	 * @param	string	SQL NULL values representation form. default \N
	 * @return	bool
	 */
	public function copy_from( $table_name, array $rows, $delimiter = "\t", $null_as = "\\N" )
	{
		return @pg_copy_from($this->conn_id, $table_name, $rows, $delimiter, $null_as);
	}

/* End of file MY_DB_postgre_driver.php */
/* Location: ./application/library/database/drivers/postgre/postgre_driver.php */
}