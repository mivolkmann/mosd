<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

   DB::query('SELECT
                  `unternehmenid`,
                  `streamurlid`,
                  `name`,
                  `apibase`,
                  `baseurl`,
                  `maxlength`
              FROM
                  `streamurls`
              WHERE
                  `unternehmenid` = ?
              OR
                  `unternehmenid` = 0
              ORDER BY
                  `unternehmenid` ASC,
                  `name` ASC', 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

   DB::moveCurrentResultToBuffer('streams');

   DB::query('SELECT
                  `d1`.`unternehmenid`,
                  `d1`.`gatewayid`,
                  `d1`.`name`,
                  `d1`.`url`,
                  `d1`.`maxlength`
              FROM
                  `sms_gateways` `d1`
              WHERE
                  `d1`.`unternehmenid` = ?
              ORDER BY
                  `unternehmenid` ASC,
                  `name` ASC', 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

   DB::moveCurrentResultToBuffer('sms');

   if($this->_getExists('editmode'))
   {
      DB::query('SELECT
                     `baseurl`,
                     `apibase`,
                     `authurl`,
                     `requesturl`,
                     `accessurl`,
                     `searchurl`
                 FROM
                     `streamurls`
                 WHERE
                     `streamurlid` = 1', 0);
      DB::exec();
      DB::nextResult();

      $twitter = array('baseurl' => DB::result('baseurl'),
                       'apibase' => DB::result('apibase'),
                       'authurl' => DB::result('authurl'),
                       'requesturl' => DB::result('requesturl'),
                       'accessurl' => DB::result('accessurl'),
                       'searchurl' => DB::result('searchurl'));

      $this->addJavascript('connections');
      $this->addJavascript('layer');

      $this->addJavascriptBlank('var gConnections = new ConnectionManagement(\'newconnection\');');
      $this->addJavascriptBlank('gSample = '.json_encode($twitter).';');
      $this->addJavascriptBlank('gConnections.setSample(gSample);');
      $this->addJavascriptBlank('gConnections.init();');
      $this->addJavascriptBlank('editOver();');
   }

?>
