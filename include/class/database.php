<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

include ("./dbconnection.php");


class DB
{
	private static connection;

	public static function init($host, $user, $pw, $db)
	{
		self::$connection = DBConnection.getInstance("main");
		if (!self::$connection.isOpen())
		{
			self::$connection->init($host, $user, $pw, $db);
		}
	}

	public static function query($queryString, $paramAnzahl)
	{
		self::$connection->query($queryString, $paramAnzahl);
	}

	public static function setParam($wert, $typ)
	{
		self::connection->setParam($wert, $typ);
	}

	public static function exec($returnId = false)
	{
		return self::$connection->exec($returnId);
	}

	public static function numRows()
	{
		return self::$connection->numRows();
	}

	public static function nextResult()
	{
		return self::$connection->nextResult();
	}

	public static function result($value)
	{
		return self::$connection->result($value);
	}

	public static function resultIsEmpty($value)
	{
		return self::$connection->resultIsEmpty($value);
	}

	public static function resultRow($isFetched = false)
	{
		return self::$connection->resultRow($isFetched);
	}

	public static function dataSeek($startRow = 0)
	{
		self::$connection->dataSeek($startRow);
	}

	public static function moveCurrentResultToBuffer($bufferName)
	{
		self::$connection->moveCurrentResultToBuffer($bufferName);
	}

	public static function moveBufferToCurrentResult($bufferName)
	{
		self::$connection->moveBufferToCurrentResult($bufferName);
	}

	public static function unsetBuffer($bufferName)
	{
		self::$connection->unsetBuffer($bufferName);
	}

	public static function deinit()
	{
		self::$connection->deinit();
	}
}

?>
