<?php

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