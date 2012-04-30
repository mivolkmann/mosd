<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* Multiton implementation for the database class
*
* @author Michael Volkmann
*/
class DBConnection
{
	private
	$connection, $queryObject, $currentResult, $currentRow,
	$bufferedResult = array();

	private static $_connections = array();

	/**
	* get the named instance
	* @param name identifier for the connection
	*/
	public static function getInstance($name) {
		if (!array_key_exists($name, self::$_connections))
		{
			self::$_connections[$name] = new DBConnection();
		}
		return self::$_connections[$name];
	}

	/**
	* connect to the database
	* @param host hostname
	* @param user username
	* @param pw password for the user
	* @param db database to be used
	*/
	public function init($host, $user, $pw, $db)
	{
		$this->connection = mysqli_connect($host, $user, $pw, $db);

		if(!$this->connection)
		{
			die('<strong>Es konnte keine Verbindung zur Datenbank hergestellt werden.</strong><br/>Wir arbeiten bereits an dem Problem und entschuldigen uns für diesen Ausfall.');
		}

		$this->query('SET NAMES \'utf8\'', 0);
		$this->exec();
	}

	/**
	* is this connection open
	* @return boolean
	*/
	public function isOpen() {
		if ($this->connection == null) {
			return false;
		}
		return mysqli_ping($this->connection);
	}

	/**
	* creates a new query for this connection
	* @param queryString the sqlstring
	* @param paramAnzahl number of parameters
	*/
	public function query($queryString, $paramAnzahl)
	{
		$this->queryObject = new QueryBuild($queryString, $paramAnzahl, $this->connection);
	}

	/**
	* setting a value for a parameter
	* @param wert value of the parameter
	* @param typ type of the parameter (int, string...)
	*/
	public function setParam($wert, $typ)
	{
		$this->queryObject->setParam($wert, $typ);
	}

	/**
	* executes the current query
	* @param returnId return the id when insert etc.
	* @return nothing or id
	*/
	public function exec($returnId = false)
	{
		$qrystr = $this->queryObject->exec();

		if(!($this->currentResult = mysqli_query($this->connection, $qrystr)))
		{
			print mysqli_error($this->connection);
		}
		else if($returnId)
		{
			return mysqli_insert_id($this->connection);
		}
	}

	/**
	* number of rows in the current result
	* @return number of rows
	*/
	public function numRows()
	{
		return mysqli_num_rows($this->currentResult);
	}

	/**
	* is there a result left in the resultset
	* @return boolean
	*/
	public function nextResult()
	{
		if($this->currentRow = &mysqli_fetch_assoc($this->currentResult))
		{
			foreach($this->currentRow as $key => $value)
			{
				$this->currentRow[$key] = ZF::mysqlUnescapeString($value);
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* get a value from the current row
	* @param value column name
	* @return any
	*/
	public function result($value)
	{
		return $this->currentRow[$value];
	}

	/**
	* is there a value in the column for the current row
	* @param value columnname
	* @return boolean
	*/
	public function resultIsEmpty($value)
	{
		return empty($this->currentRow[$value]);
	}

	/**
	* get a (current or next) row
	* @param isFetched true = the row is the current row
	* @return assoc array
	*/
	public function resultRow($isFetched = false)
	{
		if(!$isFetched)
		{
			$this->nextResult();
		}

		return $this->currentRow;
	}

	/**
	* seek to a row in the resultset
	* @param startRow
	*/
	public function dataSeek($startRow = 0)
	{
		if($startRow < 0 || $startRow > $this->numRows() - 1)
		{
			die(self::_getError(3));
		}

		mysqli_data_seek($this->currentResult, $startRow);
	}

	/**
	* buffer the current resultset
	* @param bufferName name for the resultset in the buffer
	*/
	public function moveCurrentResultToBuffer($bufferName)
	{
		$this->bufferedResult[$bufferName] = $this->currentResult;
	}

	/**
	* move a resultset from the buffer to current
	* @param bufferName name for the resultset in the buffer
	*/
	public function moveBufferToCurrentResult($bufferName)
	{
		if(!isset($this->bufferedResult[$bufferName]))
		{
			die(self::_getError(2));
		}

		$this->currentResult = $this->bufferedResult[$bufferName];
	}

	/**
	* clean up the buffer
	* @param bufferName the name of the buffer
	*/
	public function unsetBuffer($bufferName)
	{
		unset($this->bufferedResult[$bufferName]);
	}

	/**
	* close the connection
	*/
	public function deinit()
	{
		mysqli_close($this->connection);
	}

	private static function _getError($errorId)
	{
		switch($errorId)
		{
		case 1:
			return 'Es ist ein Fehler im Query aufgetreten';

		case 2:
			return 'Das angegebene gepufferte Ergebnis existiert nicht';

		case 3:
			return 'Der Pointer liegt außerhalb der Ergebnisumgebung';

		default:
			return 'Es ist ein unbekannter Fehler aufgetreten';
		}
	}
}

?>
