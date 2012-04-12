<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

class Mail extends Mailer
{
   private $dbProto;

   public function __construct($isHTML = false, $mailSendTyp = 'mail', $singleTo = false, $dbProto = true)
   {
      $this->dbProto = $dbProto;
      parent::__construct(EMAIL_FROMADRESS, EMAIL_FROMUSER, HOST, $singleTo, $mailSendTyp, $isHTML);

      $this->setMessageId(md5(microtime()).'@'.HOSTNAME);
   }

   protected function callback($isSent, $to, $cc, $bcc, $subject, $body, $messageid, $hasAttachment)
   {
   }
}

?>
