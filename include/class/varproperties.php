<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

class VarProperties
{
   private
   $getVars, $withoutInclude,
   $classes = array(),
   $constants = array(),
   $authorizationLevel = -1,
   $documentStatus = 200;

   public
   $postVars;

   public function __construct($withoutInclude)
   {
      $this->withoutInclude = &$withoutInclude;

      if(!$withoutInclude)
      {
         require(INCLUDE_PATH.'/class/roh/mailer.php');
         require(INCLUDE_PATH.'/class/funclib.php');
         require(INCLUDE_PATH.'/class/email.php');
         require(INCLUDE_PATH.'/class/querybuild.php');
         require(INCLUDE_PATH.'/class/database.php');
         require(INCLUDE_PATH.'/class/charhandling.php');
         require(INCLUDE_PATH.'/class/url.php');

         DB::init(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
         URL::init(HTTP_HOST, HTTPS_HOST);
      }
   }

   public function requireLogin($level)
   {
      $this->authorizationLevel = &$level;
   }

   public function addConstants($name)
   {
      $this->constants[] = &$name;
   }

   public function addClass($className)
   {
      $this->classes[] = &$className;
   }

   public function getGetVars()
   {
      return $this->getVars;
   }

   public function getPostVars()
   {
      return $this->postVars;
   }

   private function _setGetVars($get, $trim = true)
   {
      $this->getVars = $trim === true ? Funclib::trimValues($get) : $get;
   }

   protected function _setPostVars($post, $trim = true, $merge = false)
   {
      if(!$merge || ($merge && empty($this->postVars)))
      {
         $this->postVars = $trim === true ? Funclib::trimValues($post) : $post;
      }
      else if($merge && !empty($this->postVars))
      {
         $this->postVars = array_merge($this->postVars, $trim === true ? Funclib::trimValues($post) : $post);
      }
   }

   public function setDocumentStatus($status)
   {
      $this->documentStatus = &$status;
   }

   public function setContentParam($key, $value)
   {
      $this->contentParam[$key] = &$value;
   }

   protected function _getContentParam($key)
   {
      return !empty($this->contentParam[$key]) ? $this->contentParam[$key] : false;
   }

   protected function _getExists($key = false, $ignoreEmpty = true)
   {
      if(!$key)
      {
         return !empty($this->getVars);
      }
      else
      {
         if($ignoreEmpty)
         {
            return isset($this->getVars[$key]) ? true : false;
         }
         else
         {
            return isset($this->getVars[$key]) && !empty($this->getVars[$key]) ? true : false;
         }
      }
   }

   protected function _postExists($key = false, $ignoreEmpty = true)
   {
      if(!$key)
      {
         return !empty($this->postVars);
      }
      else
      {
         if($ignoreEmpty)
         {
            return isset($this->postVars[$key]);
         }
         else
         {
            return isset($this->postVars[$key]) && !empty($this->postVars[$key]) ? true : false;
         }
      }
   }

   protected function _clearPost()
   {
      $this->postVars = array();
   }

   protected function _post($key)
   {
      return $this->_postExists($key) ? $this->postVars[$key] : false;
   }

   protected function _flushGet()
   {
      $this->getVars = array();
   }

   protected function _get($key)
   {
      return $this->_getExists($key) ? $this->getVars[$key] : false;
   }

   protected function _httpStatusHandling()
   {
      switch($this->documentStatus)
      {
         case 400:
            header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request');
            print 'Fehlerhafte Anfrage';
            break;

         case 401:
            header($_SERVER['SERVER_PROTOCOL'].' 401 Unauthorized');
            print 'Zugriff nicht gestattet';
            break;

         case 403:
            header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
            print 'Zugriff nicht gestattet';
            break;

         case 404:
            header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
            print 'Die angeforderte Datei wurde nicht gefunden';
            break;

         case 410:
            header($_SERVER['SERVER_PROTOCOL'].' 410 Gone');
            print 'Das Dokument ist nicht mehr vorhanden';
            break;

         case 500:
            header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');
            print 'Es ist ein interner Serverfehler aufgetreten';
            break;

         default:
            print 'Es ist ein nicht aufgelisteter Fehler';
      }
   }

   protected function _initGlobalVars()
   {
      if(isset($_POST))
      {
         $this->_setPostVars($_POST);
      }

      $this->_setGetVars(URL::getVars());

      unset($_POST);
      unset($_GET);
   }

   protected function _importFiles()
   {
      foreach($this->constants as $k => $c)
      {
         require(INCLUDE_PATH.'/'.$c.'.php');
      }

      foreach($this->classes as $k => $class)
      {
         require(INCLUDE_PATH.'/class/'.$class.'.php');
      }
   }

   protected function _getAuthorizationLevel()
   {
      return $this->authorizationLevel;
   }

   protected function _getDocumentStatus()
   {
      return $this->documentStatus;
   }

   public function __destruct()
   {
      if(!$this->withoutInclude)
      {
         DB::deinit();
      }
   }
}
?>
