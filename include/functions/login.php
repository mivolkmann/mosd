<?php

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