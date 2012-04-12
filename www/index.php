<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

   session_start();

   $time = microtime();

   require('../include/constants.php');
   require(INCLUDE_PATH.'/class/varproperties.php');
   require(INCLUDE_PATH.'/class/site.php');

   $seite = new Site();
   $seite->addJavascriptBlank('var ENV = {"codeBase":"'.REL_PATH.'", "ajaxBase":"'.REL_PATH.'/ajax"};');
   $seite->requireLogin(1);

   URL::decode($_GET['token']);

   switch(URL::getNext())
   {
      case 'start':
         $seite->addClass('sitechange');
         $seite->setFunction('start');
         $seite->setContent('start');
         break;

      case 'sensors':
         $seite->setFunction('sensoren');
         $seite->setContent('sensoren');
         break;

      case 'values':
         $seite->addClass('sitechange');
         URL::setSingleGetVars();
         $seite->setFunction('values');
         $seite->setContent('values');
         break;

      case 'conditions':
         $seite->setFunction('conditions');
         $seite->setContent('conditions');
         break;

      case 'dashboard':
         $seite->setFunction('dashboard');
         $seite->setContent('dashboard');
         break;

      case 'log':
         $seite->addClass('sitechange');

         switch(URL::getNext())
         {
            case 'messages':
               $seite->setFunction('messages');
               $seite->setContent('messages');
               URL::setSingleGetVars();
               break;

            case 'events':
               $seite->setFunction('events');
               $seite->setContent('events');
               break;

            case 'errors':
               $seite->requireLogin(1);
               $seite->setFunction('errors');
               $seite->setContent('errors');
               break;

            default:
               $seite->setDocumentStatus(404);
         }

         $seite->setContentParam('site', URL::getNext());
         break;

      case 'accounts':
         $seite->setFunction('accounts');
         $seite->setContent('accounts');
         break;

      case 'connections':
         $seite->setFunction('connections');
         $seite->setContent('connections');
         break;

      case 'settings':
         $seite->setFunction('settings');
         $seite->setContent('settings');
         break;

      case 'clients':
         $seite->setFunction('clients');
         $seite->setContent('clients');
         break;

      case 'logout':
         $seite->setContentParam('logout', true);
         $seite->setWithoutMenu();
         $seite->setFunction('login');
         $seite->setContent('login');
         break;

      default:
         $seite->setDocumentStatus(404);
   }

   $seite->output();

?>
