<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

class Ajax extends VarProperties
{
   private
   $pageFunction, $returnValue;

   public function __construct($withoutInclude = false)
   {
      parent::__construct($withoutInclude);
   }

   public function setFunction($name)
   {
      $this->pageFunction = &$name;
   }

   private function _setReturnValue($value)
   {
      $this->returnValue = $value;
   }

   public function output()
   {
      $this->_initGlobalVars();

      if($this->_getDocumentStatus() != 200)
      {
         $this->_httpStatusHandling();
         exit;
      }

      if($this->_getAuthorizationLevel() != -1)
      {
         if(isset($_SESSION['UISauthLevel']) && $this->_getAuthorizationLevel() < $_SESSION['UISauthLevel'])
         {
            $this->_jsonOutput(2, $this->errorHandling(0));
         }
         else if(!isset($_SESSION['UISauthLevel']))
         {
            $this->_jsonOutput(3, 'load');
         }
      }

      $this->_importFiles();

      if(!empty($this->pageFunction))
      {
         require(INCLUDE_PATH.'/ajax/'.$this->pageFunction.'.php');
      }

      $this->_jsonOutput(2, $this->returnValue);
   }

   private function _jsonOutput($statusCode, $value = '')
   {
      $ret = array('s' => $statusCode);

      if(!empty($value))
      {
         $ret['c'] = $value;
      }

      print json_encode($ret);
      exit;
   }

   private function errorHandling($code)
   {
      switch($code)
      {
         case 0: return 'Sie besitzen nicht die benötigten Rechte zum Zugriff auf diese Seite';
      }
   }

   public function __destruct()
   {
      parent::__destruct();
   }
}

?>
