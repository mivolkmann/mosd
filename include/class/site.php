<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

class Site extends VarProperties
{
   private
   $pageContent, $pageFunction, $withoutInclude, $message,
   $pageTitle = SITE_DEFAULT_TITLE,
   $stylesheets = array(SITE_DEFAULT_CSS),
   $javascripts = array(SITE_DEFAULT_JS),
   $javascriptsBlank = array(),
   $contentHistory = array(),
   $mistakes = array(),
   $mistakeCount = 0,
   $isMistake = false,
   $withoutMenu = false,
   $ident = -1;

   public function __construct($withoutInclude = false)
   {
      parent::__construct($withoutInclude);
      $this->withoutInclude = $withoutInclude;
   }

   public function setTitle($title)
   {
      # Seitentitel setzen
      $this->pageTitle = &$title;
   }

   public function addStylesheet($cssName)
   {
      # Stylesheet hinzufügen
      $this->stylesheets[] = &$cssName;
   }

   public function addJavascript($jsName)
   {
      # Javascript hinzufügen
      $this->javascripts[] = &$jsName;
   }

   public function addJavascriptBlank($code)
   {
      # Javascript in Codeform hinzufügen
      $this->javascriptsBlank[] = &$code;
   }

   public function setFunction($name)
   {
      # Standardseitenfunktion setzen
      $this->pageFunction = &$name;
   }

   public function setContent($name)
   {
      # Standardseiteninhalt setzen
      $this->pageContent = &$name;
   }

   public function setWithoutMenu()
   {
      $this->withoutMenu = true;
   }

   public function setIdent($idNum)
   {
      $this->ident = &$idNum;
   }

   protected function _reload($path = false)
   {
      header('location:'.(!$path ? $_SERVER['REQUEST_URI'] : REL_PATH.$path));
      exit;
   }

   public function addContentHistory($name, $link = '', $des = '')
   {
      $this->contentHistory[$name] = array(&$link, &$des);
   }

   protected function _setMistakeSpecifier($specifier, $desc = true)
   {
      $this->mistakes[$specifier] = &$desc;
      $this->mistakeCount++;
      $this->isMistake = true;
   }

   protected function _getMistake($specifier)
   {
      return isset($this->mistakes[$specifier]) ? $this->mistakes[$specifier] : false;
   }

   protected function _getMistakeCount()
   {
      return $this->mistakeCount;
   }

   protected function _setMessage($msgarr)
   {
      $this->message = array($msgarr['typ'], $msgarr['beschreibung']);

      if($msgarr['typ'] == 0)
      {
         $this->isMistake = true;
      }
   }

   protected function _messageHandling()
   {
      if(!empty($this->message))
      {
         if($this->message[0] == 0)
         {
            print '<div id="fehler">'.$this->message[1].'</div>';
         }
         else
         {
            print '<div id="succeed">'.$this->message[1].'</div>';
         }
      }
   }

   protected function _printHeader()
   {
      # Seitenheader erzeugen
?>
<!DOCTYPE html>
<html>
   <head>
      <title><?php print $this->pageTitle ?></title>
      <meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8"/>
      <meta http-equiv="content-language" content="de"/>
      <meta name="keywords" content=""/>
      <meta name="author" content="Ronny Bansemer"/>
<?php

      foreach($this->stylesheets as $k => $css)
      {
         print '      <link rel="stylesheet" type="text/css" media="screen" href="'.HTTP_HOST.'/css/'.$css.'"/>'."\n";
      }

?>
   </head>
   <body>
<?php

   }

   protected function _printFooter()
   {
      # Seitenfooter erzeugen

      # Javascripts ausgeben
      foreach($this->javascripts as $k => $js)
      {
         print "\n".'      <script type="text/javascript" src="'.HTTP_HOST.'/js/'.$js.'"></script>';
      }

      print "\n";

      # Javascripts in Codeform ausgeben
      if(count($this->javascriptsBlank) > 0)
      {
         print '      <script type="text/javascript">'."\n";

         foreach($this->javascriptsBlank as $k => $js)
         {
            print '         '.$js."\n";
         }

         print '      </script>'."\n";
      }

?>
   </body>
</html>
<?php

   }

   public function output()
   {
      // init global Vars
      $this->_initGlobalVars();

      # HTTP-Document-Status ausgeben
      if($this->_getDocumentStatus() != 200)
      {
         $this->_httpStatusHandling();
         exit;
      }

      # Weiche stellen, wenn Login benötigt ist, aber Nutzer nicht eingeloggt ist
      if($this->_getAuthorizationLevel() != -1)
      {
         if(isset($_SESSION['UISauthLevel']) && $this->_getAuthorizationLevel() < $_SESSION['UISauthLevel'])
         {
            $login = new Site(true);
            $login->setContent('denied');
            $login->output();
            exit;
         }
         else if(!isset($_SESSION['UISauthLevel']))
         {
            $login = new Site(true);
            $login->setTitle('MOSD - Middleware For Open Source Devices');
            $login->setWithoutMenu();

            if($this->_postExists())
            {
               $login->_setPostVars($this->getPostVars(), false);
            }

            #$login->setContentParam('authLevel', $this->authorizationLevel);
            $login->setFunction('login');
            $login->setContent('login');
            $login->addJavascriptBlank('$(\'focusele\').focus();');
            $login->output();
            exit;
         }
      }

      $this->_importFiles();

      if(!empty($_SESSION['unternehmenid']))
      {
         # Fehler-Logging abfragen
         DB::query('SELECT
                        COUNT(*) AS `gesamt`
                    FROM
                        `fehler_log`
                    WHERE
                        `unternehmenid` = ?', 1);
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         DB::nextResult();
         $fehlerAnzahl = DB::result('gesamt');
      }

      # Standardseitenfunktion importieren (sofern gesetzt)
      if(!empty($this->pageFunction))
      {
         require(INCLUDE_PATH.'/functions/'.$this->pageFunction.'.php');
      }

      # Seite bauen
      $this->_printHeader();

      if(!$this->withoutMenu)
      {
?>
      <div id="header">
         <img src="<?php print IMAGE_PATH ?>uis.jpg" alt="Ubimic Information Systems"/>
         <span>company: <em>TU Chemnitz - Ireko Projekt</em></span>
         <a href="<?php print HTTPS_HOST ?>/logout">Logout</a>
      </div>
      <div id="main">
         <div id="menu">
            <span><a href="<?php print HTTP_HOST ?>/start<?php print $this->_getExists('editmode') ? '/editmode' : '' ?>">Main page</a></span>
            <span>Device settings</span>
            <ul>
               <li>
                  <a href="<?php print HTTP_HOST ?>/start/editmode">create a new device</a>
               </li>
               <li>
                  <a href="<?php print HTTP_HOST ?>/conditions<?php print $this->_getExists('editmode') ? '/editmode' : '' ?>">notification conditions</a>
               </li>
               <li>
                  <a href="<?php print HTTP_HOST ?>/accounts<?php print $this->_getExists('editmode') ? '/editmode' : '' ?>">notification accounts</a>
               </li>
            </ul>
            <span>Logging</span>
            <ul>
               <li>
                  <a href="<?php print HTTP_HOST ?>/dashboard<?php print $this->_getExists('editmode') ? '/editmode' : '' ?>">dashboard</a>
               </li>
               <li>
                  <a href="<?php print HTTP_HOST ?>/values">logged device data</a>
               </li>
               <li>
                  <a href="<?php print HTTP_HOST ?>/log/messages">published messages</a>
               </li>
               <li>
                  <a href="<?php print HTTP_HOST ?>/log/events<?php print $this->_getExists('editmode') ? '/0/editmode' : '' ?>">executed events</a>
               </li>
               <li>
                  <a href="<?php print HTTP_HOST ?>/log/errors<?php print $this->_getExists('editmode') ? '/0/editmode' : '' ?>" class="<?php print $fehlerAnzahl > 0 ? 'fehler' : 'ok' ?>">recognized errors<?php print $fehlerAnzahl > 0 ? ' ('.$fehlerAnzahl.')' : '' ?></a>
               </li>
            </ul>
            <span>Client data</span>
            <ul>
               <li>
                  <a href="<?php print HTTP_HOST ?>/connections<?php print $this->_getExists('editmode') ? '/editmode' : '' ?>">publish connection types</a>
               </li>
               <li>
                  <a href="<?php print HTTP_HOST ?>/settings<?php print $this->_getExists('editmode') ? '/editmode' : '' ?>">client settings</a>
               </li>
            </ul>
            <span>Administration</span>
            <ul>
               <li>
                  <a href="<?php print HTTP_HOST ?>/clients<?php print $this->_getExists('editmode') ? '/editmode' : '' ?>">clients</a>
               </li>
            </ul>
            <div id="loginlogosmall">
               <img src="<?php print IMAGE_PATH ?>esf.jpg" alt="sponsored by ESF" width="190" height="46"/><br />
               The first version is being developed within the scope of the project <a href="http://ireko.tu-chemnitz.de/index.html.en" target="_blank" title="IREKO">IREKO</a> which is
               funded by the European Social Fund and the Free State of Saxony.<br /><br />
               <span>
                  <a href="http://creativecommons.org/licenses/by-sa/3.0/" target="_blank" style="border: none;">
                     <img alt="Creative Commons License" src="http://i.creativecommons.org/l/by-sa/3.0/80x15.png"/>
                  </a>
               </span>
               This work is licensed under a <a href="http://creativecommons.org/licenses/by-sa/3.0/" target="_blank">Creative Commons Attribution-ShareAlike 3.0 Unported License</a>.
            </div>
         </div>
         <div id="content">
<?php

      }

      $this->_messageHandling();
      require(INCLUDE_PATH.'/content/'.$this->pageContent.'.php');

      if(!$this->withoutMenu)
      {

?>
         </div>
      </div>
<?php

      }

      $this->_printFooter();
   }

   public function __destruct()
   {
      parent::__destruct();
   }
}

?>
