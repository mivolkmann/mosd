<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

   if(!$this->_getContentParam('logout'))
   {
      if($this->_postExists())
      {
         switch($this->_post('aktion'))
         {
            case 'login':
               $login = Funclib::checkLogin($this->_post('unternehmen'),
                                            $this->_post('passwort'));

               switch($login)
               {
                  case -1:
                     $this->_setMessage(array(0, 'you have set a wrong password/username'));
                     break;

                  case 0:
                     $this->_setMessage(array(0, 'you have set a wrong password/username'));
                     break;

                  default:
                     $_SESSION['UISauthLevel'] = 0;
                     $_SESSION['unternehmenid'] = $login;

                     $this->_reload();
               }
               break;
         }
      }
   }
   else
   {
      session_destroy();
      $this->_reload('/start');
   }

?>
