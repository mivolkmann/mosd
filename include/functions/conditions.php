<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

   $data = array();
   $data2 = array(array(), array(), array());
   $sensordata = array();

   DB::query('SELECT
                  `emailid`,
                  `name`
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
      $data[] = array('id' => DB::result('emailid'),
                      'name' => DB::result('name'),
                      'typ' => 0);
      $data2[0][DB::result('emailid')] = DB::result('name').' (email)';
   }

   DB::query('SELECT
                  `streamid`,
                  `name`
              FROM
                  `streaming`
              WHERE
                  `unternehmenid` = ?
              ORDER BY
                  `name` ASC', 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

   while(DB::nextResult())
   {
      $data[] = array('id' => DB::result('streamid'),
                      'name' => DB::result('name'),
                      'typ' => 1);
      $data2[1][DB::result('streamid')] = DB::result('name').' (microblog)';
   }

   DB::query('SELECT
                  `d1`.`nummerid`,
                  `d1`.`name`
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
      $data[] = array('id' => DB::result('nummerid'),
                      'name' => DB::result('name'),
                      'typ' => 2);
      $data2[2][DB::result('nummerid')] = DB::result('name').' (sms)';
   }

   DB::query('SELECT
                  `d1`.`sensorid`,
                  `d1`.`name` AS `sensorname`,
                  `d2`.`name` AS `varname`,
                  `d2`.`typ`,
                  `d2`.`variableid`,
                  `d2`.`kommentar`
              FROM
                  `sensoren` `d1`
              INNER JOIN
                  `sensoren_variablen` `d2`
              ON
                  `d1`.`sensorid` = `d2`.`sensorid`
              WHERE
                  `d1`.`unternehmenid` = ?
              ORDER BY
                  `sensorname` ASC,
                  `varname` ASC', 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

   $currStat = '';
   $currVar = '';
   $variablePath = array();

   while(DB::nextResult())
   {
      if(empty($sensordata[DB::result('sensorid')]))
      {
         $sensordata[DB::result('sensorid')] = array('name' => DB::result('sensorname'),
                                                     'vars' => array());
      }

      $sensordata[DB::result('sensorid')]['vars'][DB::result('variableid')] = array('name' => DB::result('varname'),
                                                                                    'typ' => DB::result('typ'),
                                                                                    'kommentar' => DB::result('kommentar'));
      $variablePath[DB::result('variableid')] = DB::result('sensorname').'.'.DB::result('varname');
   }

   if($this->_getExists('editmode'))
   {
      $this->addJavascript('layer');
      $this->addJavascript('conditions');
      $this->addJavascriptBlank('var gAccounts = '.json_encode($data));
      $this->addJavascriptBlank('var gSensorVars = '.json_encode($sensordata));
      $this->addJavascriptBlank('var gConditions = new Conditions(\'newcond\');');
      $this->addJavascriptBlank('gConditions.setAccounts(gAccounts);');
      $this->addJavascriptBlank('gConditions.setSensorVars(gSensorVars);');
      $this->addJavascriptBlank('gConditions.init();');
      $this->addJavascriptBlank('editOver();');
   }

   $notificationTexts = array();
   $conditionVars = array();

   DB::query('SELECT
                  `d2`.`textid`,
                  `d2`.`text`,
                  GROUP_CONCAT(CAST(`d3`.`accountid` AS CHAR(5))) AS `accountids`,
                  GROUP_CONCAT(CAST(`d3`.`type` AS CHAR(1))) AS `accounttypes`
              FROM
                  `condition` `d1`
              INNER JOIN
                  `condition_texts` `d2`
              ON
                  `d1`.`conditionid` = `d2`.`conditionid`
              INNER JOIN
                  `condition_texts_accounts` `d3`
              ON
                  `d2`.`textid` = `d3`.`textid`
              WHERE
                  `d1`.`unternehmenid` = ?
              GROUP BY
                  `textid`', 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

   while(DB::nextResult())
   {
      $notificationTexts[DB::result('textid')] = array(DB::result('text'),
                                                       explode(',', DB::result('accountids')),
                                                       explode(',', DB::result('accounttypes')));
   }

   DB::query('SELECT
                  `d1`.`conditionid`,
                  `d2`.`variableid`,
                  `d2`.`operator`,
                  `d2`.`wert`,
                  `d2`.`connector`
              FROM
                  `condition` `d1`
              INNER JOIN
                  `condition_variablen` `d2`
              ON
                  `d1`.`conditionid` = `d2`.`conditionid`
              WHERE
                  `d1`.`unternehmenid` = ?
              ORDER BY
                  `conditionid` ASC,
                  `cvid` ASC', 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

   while(DB::nextResult())
   {
      if(empty($conditionVars[DB::result('conditionid')]))
      {
         $conditionVars[DB::result('conditionid')] = array(array(), array(), array(), array());
      }

      $conditionVars[DB::result('conditionid')][0][] = DB::result('variableid');
      $conditionVars[DB::result('conditionid')][1][] = DB::result('operator');
      $conditionVars[DB::result('conditionid')][2][] = DB::result('wert');
      $conditionVars[DB::result('conditionid')][3][] = DB::result('connector');
   }

   DB::query('SELECT
                  `d1`.`conditionid`,
                  `d1`.`name`,
                  `d1`.`beschreibung`,
                  `d1`.`critical`,
                  `d1`.`locked`,
                  GROUP_CONCAT(CAST(`d2`.`textid` AS CHAR(5))) AS `textids`
             FROM
                  `condition` `d1`
             INNER JOIN
                  `condition_texts` `d2`
             ON
                  `d1`.`conditionid` = `d2`.`conditionid`
             WHERE
                  `d1`.`unternehmenid` = ?
             GROUP BY
                  `conditionid`
             ORDER BY
                  `critical` ASC,
                  `name` ASC', 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

?>
