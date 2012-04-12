<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

   session_start();

   require('../include/constants.php');
   require(INCLUDE_PATH.'/class/varproperties.php');
   require(INCLUDE_PATH.'/class/ajax.php');

   $ajax = new Ajax();
   $ajax->requireLogin(1);

   URL::decode($_GET['token']);

   switch(URL::getNext())
   {
      case 'microblog':
         $ajax->setFunction('microblog');
         break;

      case 'accounts':
         $ajax->setFunction('accounts');
         break;

      case 'sensoren':
         $ajax->setFunction('sensoren');
         break;

      case 'conditions':
         $ajax->setFunction('conditions');
         break;

      case 'dashboard':
         $ajax->setFunction('dashboard');
         break;

      case 'logs':
         $ajax->setFunction('logs');
         break;

      case 'connections':
         $ajax->setFunction('connections');
         break;
   }

   $ajax->output();

?>
