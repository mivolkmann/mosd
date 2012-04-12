<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

class ZF
{
   public static function sqlString($string, $connection)
   {
      return "'".mysqli_real_escape_string($connection, (string)$string)."'";
   }

   public static function sqlInteger($integer)
   {
      return sprintf('%u', $integer) != 0 ? sprintf('%u', $integer) : '\'0\'';
   }

   public static function sqlDouble($double)
   {
      return sprintf('%f', $double);
   }

   public static function mysqlUnescapeString($string)
   {
      return stripslashes($string);
   }
}

?>
