<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

   $data = array();

   DB::query('SELECT
                  `emailid`,
                  `name`,
                  `email`
              FROM
                  `email`
              WHERE
                  `unternehmenid` = ?
              ORDER BY
                  `name` ASC', 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

   while(DB::nextResult())
   {
      $data[DB::result('name')] = array('id' => DB::result('emailid'),
                                        'type' => '0',
                                        'connection' => DB::result('email'));
   }

   DB::query('SELECT
                  `d1`.`streamid`,
                  `d1`.`name`,
                  `d2`.`name` AS `streamname`
              FROM
                  `streaming` `d1`
              INNER JOIN
                  `streamurls` `d2`
              ON
                  `d1`.`streamurlid` = `d2`.`streamurlid`
              WHERE
                  `d1`.`unternehmenid` = ?
              ORDER BY
                  `name` ASC', 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

   while(DB::nextResult())
   {
      $data[DB::result('name')] = array('id' => DB::result('streamid'),
                                        'type' => '1',
                                        'connection' => DB::result('streamname'));
   }

   DB::query('SELECT
                  `d1`.`nummerid`,
                  `d1`.`name`,
                  `d1`.`nummer`,
                  `d2`.`name` AS `gatewayname`
              FROM
                  `sms` `d1`
              INNER JOIN
                  `sms_gateways` `d2`
              ON
                  `d1`.`gatewayid` = `d2`.`gatewayid`
              WHERE
                  `d2`.`unternehmenid` = ?
              ORDER BY
                  `name` ASC', 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

   while(DB::nextResult())
   {
      $data[DB::result('name')] = array('id' => DB::result('nummerid'),
                                        'type' => '2',
                                        'connection' => DB::result('gatewayname'),
                                        'more' => DB::result('nummer'));
   }

   $this->addJavascript('accounts');

   if($this->_getExists('editmode'))
   {
      $streamurls = array();
      $smsurls = array();

      DB::query('SELECT
                     `name`,
                     `streamurlid`
                 FROM
                     `streamurls`
                 WHERE
                     `unternehmenid` = 0
                 OR
                     `unternehmenid` = ?', 1);
      DB::setParam($_SESSION['unternehmenid'], 'int');
      DB::exec();

      while(DB::nextResult())
      {
         $streamurls[DB::result('streamurlid')] = DB::result('name');
      }

      DB::query('SELECT
                     `name`,
                     `gatewayid`
                 FROM
                     `sms_gateways`
                 WHERE
                     `unternehmenid` = ?', 1);
      DB::setParam($_SESSION['unternehmenid'], 'int');
      DB::exec();

      while(DB::nextResult())
      {
         $smsurls[DB::result('gatewayid')] = DB::result('name');
      }

      $this->addJavascript('layer');

      $this->addJavascriptBlank('var gAccounts = new AccountManagement(\'newaccount\');');
      $this->addJavascriptBlank('var gStreams = '.json_encode($streamurls).';');
      $this->addJavascriptBlank('var gGateways = '.json_encode($smsurls).';');
      $this->addJavascriptBlank('gAccounts.setMicroblogs(gStreams);');
      $this->addJavascriptBlank('gAccounts.setGateways(gGateways);');
      $this->addJavascriptBlank('gAccounts.init();');
      $this->addJavascriptBlank('editOver();');
   }

?>
