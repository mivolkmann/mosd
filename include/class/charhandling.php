<?php

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