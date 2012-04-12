<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

class DB
{
   private static
   $connection, $queryObject, $currentResult, $currentRow,
   $bufferedResult = array();

   public static function init($host, $user, $pw, $db)
   {
      self::$connection = mysqli_connect($host, $user, $pw, $db);

      if(!self::$connection)
      {
         die('<strong>Es konnte keine Verbindung zur Datenbank hergestellt werden.</strong><br/>Wir arbeiten bereits an dem Problem und entschuldigen uns für diesen Ausfall.');
      }

      self::query('SET NAMES \'utf8\'', 0);
      self::exec();
   }

   public static function query($queryString, $paramAnzahl)
   {
      self::$queryObject = new QueryBuild($queryString, $paramAnzahl, self::$connection);
   }

   public static function setParam($wert, $typ)
   {
      self::$queryObject->setParam($wert, $typ);
   }

   public static function exec($returnId = false)
   {
      $qrystr = self::$queryObject->exec();

      if(!(self::$currentResult = mysqli_query(self::$connection, $qrystr)))
      {
         print mysqli_error(self::$connection);
      }
      else if($returnId)
      {
         return mysqli_insert_id(self::$connection);
      }
   }

   public static function numRows()
   {
      return mysqli_num_rows(self::$currentResult);
   }

   public static function nextResult()
   {
      if(self::$currentRow = &mysqli_fetch_assoc(self::$currentResult))
      {
         foreach(self::$currentRow as $key => $value)
         {
            self::$currentRow[$key] = ZF::mysqlUnescapeString($value);
         }

         return true;
      }
      else
      {
         return false;
      }
   }

   public static function result($value)
   {
      return self::$currentRow[$value];
   }

   public static function resultIsEmpty($value)
   {
      return empty(self::$currentRow[$value]);
   }

   public static function resultRow($isFetched = false)
   {
      if(!$isFetched)
      {
         self::nextResult();
      }

      return self::$currentRow;
   }

   public static function dataSeek($startRow = 0)
   {
      if($startRow < 0 || $startRow > self::numRows() - 1)
      {
         die(self::_getError(3));
      }

      mysqli_data_seek(self::$currentResult, $startRow);
   }

   public static function moveCurrentResultToBuffer($bufferName)
   {
      self::$bufferedResult[$bufferName] = self::$currentResult;
   }

   public static function moveBufferToCurrentResult($bufferName)
   {
      if(!isset(self::$bufferedResult[$bufferName]))
      {
         die(self::_getError(2));
      }

      self::$currentResult = self::$bufferedResult[$bufferName];
   }

   public static function unsetBuffer($bufferName)
   {
      unset(self::$bufferedResult[$bufferName]);
   }

   public static function deinit()
   {
      mysqli_close(self::$connection);
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
