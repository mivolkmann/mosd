<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

class URL
{
   private static
   $httphost, $httpshost, $parts, $getVarSingle;

   public static
   $isSSL;

   public static function init($httphost, $httpshost)
   {
      self::$httphost = $httphost;
      self::$httpshost = $httpshost;
      self::$isSSL = !empty($_SERVER['HTTPS']);
      self::$getVarSingle = false;
   }

   public static function decode($url)
   {
      self::$parts = explode('/', $url);
   }

   public static function getNext()
   {
      return array_shift(self::$parts);
   }

   public function setSingleGetVars()
   {
      self::$getVarSingle = true;
   }

   public function getVars()
   {
      $r = array();
      $partAnz = count(self::$parts);

      if(!self::$getVarSingle)
      {
         for($i = 0; $i < $partAnz; $i += 2)
         {
            $r[self::$parts[$i]] = self::$parts[$i + 1];
         }
      }
      else
      {
         for($i = 0; $i < $partAnz; $i++)
         {
            $r[] = self::$parts[$i];
         }
      }

      return $r;
   }

   public static function create($path, $ssl = false)
   {
      return ($ssl ? (!self::$isSSL ? self::$httpshost : '') : (self::$isSSL ? self::$httphost : '')).'/'.$path;
   }

   public static function createML($path, $ssl = false)
   {
      return array(self::create($path, $ssl), ($ssl && self::$isSSL || !$ssl && !self::$isSSL) ? true : false);
   }
}

?>
