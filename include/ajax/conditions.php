<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
   switch($this->_post('aktion'))
   {
      case 'update':
         DB::query('SELECT

                        GROUP_CONCAT(CAST(`d2`.`operator` AS CHAR(1))) AS `operatoren`,
                        GROUP_CONCAT(`d2`.`wert`) AS `werte`,
                        GROUP_CONCAT(CAST(`d2`.`connector` AS CHAR(2))) AS `connectoren`,
                        GROUP_CONCAT(`d3`.`name`) AS `varnames`
                    FROM
                        `condition` `d1`
                    INNER JOIN
                        `condition_variablen` `d2`
                    ON
                        `d1`.`conditionid` = `d2`.`conditionid`
                    INNER JOIN
                        `sensoren_variablen` `d3`
                    ON
                        `d2`.`variableid` = `d3`.`variableid`
                    WHERE
                        `d1`.`unternehmenid` = ?
                    AND
                        `d1`.`conditionid` = ?', 2);
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::setParam($this->_post('conditionid'), 'int');
         DB::exec();

         if(DB::numRows() == 0)
         {
            $this->_jsonOutput(2, 'the selected condition does not exist');
         }

         DB::nextResult();

         $oldVars = array(explode(',', DB::result('varnames')),
                          explode(',', DB::result('operatoren')),
                          explode(',', DB::result('werte')),
                          explode(',', DB::result('connectoren')));

      case 'anlegen':
         $statusid = array();
         $variables = array();
         $options = array('variableids' => array(),
                          'operatoren' => array(),
                          'werte' => array());

         // check variables guilty
         foreach($this->postVars['var'] as $k => $v)
         {
            foreach($v as $k2 => $v2)
            {
               DB::query('SELECT
                              `d1`.`typ`,
                              CONCAT(`d2`.`name`, \'.\', `d1`.`name`) AS `name`
                          FROM
                              `sensoren_variablen` `d1`
                          INNER JOIN
                              `sensoren` `d2`
                          ON
                              `d1`.`sensorid` = `d2`.`sensorid`
                          WHERE
                              `d1`.`variableid` = ?
                          AND
                              `d2`.`unternehmenid` = ?', 2);
               DB::setParam($v2[0], 'int');
               DB::setParam($_SESSION['unternehmenid'], 'int');
               DB::exec();

               if(DB::numRows() == 1)
               {
                  DB::nextResult();
                  $options['variableids'][] = $v2[0];
                  $options['operatoren'][] = $v2[1];
                  $options['werte'][] = $v2[2];
                  $variables[$v2[0]] = DB::result('name');

                  if(!(DB::result('typ') == 1 && is_numeric($v2[2]) && ($v2[1] == 0 || $v2[1] == 1 || $v2[1] == 2 || $v2[1] == 3)) &&
                     !(DB::result('typ') == 2 && ($v2[1] == 1 || $v2[1] == 3)))
                  {
                     $this->_jsonOutput(2, 'wrong operators oder values set');
                  }
               }
               else
               {
                  $this->_jsonOutput(2, 'wrong variable set');
               }
            }
         }

         if(count($options['variableids']) > 6)
         {
            $this->_jsonOutput(2, 'there cannot be set more than 6 rules per condition');
         }

         // check connectors guilty
         foreach($this->postVars['connectors'] as $k => $v)
         {
            if($v != 0 && $v != 1)
            {
               $this->_jsonOutput(2, 'wrong connectors set');
            }
         }

         if(count($options['variableids']) - 1 != count($this->postVars['connectors']))
         {
            $this->_jsonOutput(2, 'number of connectors does not compare to number of variables');
         }

         // read notification methods
         $data = array(array(), array(), array());
         $microblogCondition = array();

         DB::query('SELECT
                        `emailid`
                    FROM
                        `email`
                    WHERE
                        `unternehmenid` = ?', 1);
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         while(DB::nextResult())
         {
            $data[0][DB::result('emailid')] = true;
         }

         DB::query('SELECT
                        `d1`.`streamid`,
                        `d1`.`name`,
                        `d1`.`costumerkey`,
                        `d1`.`costumersecret`,
                        `d1`.`oauth_token`,
                        `d1`.`oauth_token_secret`,
                        `d2`.`baseurl`,
                        `d2`.`authurl`,
                        `d2`.`requesturl`,
                        `d2`.`accessurl`,
                        `d2`.`maxlength`
                    FROM
                        `streaming` `d1`
                    INNER JOIN
                        `streamurls` `d2`
                    ON
                        `d1`.`streamurlid` = `d2`.`streamurlid`
                    WHERE
                        `d1`.`unternehmenid` = ?', 1);
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         while(DB::nextResult())
         {
            $data[1][DB::result('streamid')] = array('name' => DB::result('name'),
                                                     'costumerkey' => DB::result('costumerkey'),
                                                     'costumersecret' => DB::result('costumersecret'),
                                                     'oauth_token' => DB::result('oauth_token'),
                                                     'oauth_token_secret' => DB::result('oauth_token_secret'),
                                                     'baseurl' => DB::result('baseurl'),
                                                     'authurl' => DB::result('authurl'),
                                                     'requesturl' => DB::result('requesturl'),
                                                     'accessurl' => DB::result('accessurl'),
                                                     'maxlength' => DB::result('maxlength'));
         }

         DB::query('SELECT
                        `d1`.`nummerid`
                    FROM
                        `sms` `d1`
                    INNER JOIN
                        `sms_gateways` `d2`
                    ON
                        `d1`.`gatewayid` = `d2`.`gatewayid`
                    WHERE
                        `d2`.`unternehmenid` = ?', 1);
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         while(DB::nextResult())
         {
            $data[2][DB::result('nummerid')] = true;
         }

         // check messages guilty
         foreach($this->postVars['messages'] as $k => $v)
         {
            $moreThanOne = false;

            if(!empty($v[0]) && strlen($v[0]) > 2)
            {
               foreach($v[1] as $k2 => $v2)
               {
                  if(empty($data[$v2[0]][$v2[1]]))
                  {
                     $this->_jsonOutput(2, 'wrong account set');
                  }

                  if($v2[0] == 1)
                  {
                     $microblogCondition[] = $v2[1];
                  }

                  $moreThanOne = true;
               }
            }
            else
            {
               $this->_jsonOutput(2, 'the text of the notification message must have at least 3 characters');
            }

            if(!$moreThanOne)
            {
               $this->_jsonOutput(2, 'there must be set one or more accounts each notification message');
            }
         }

         $microblogCondition = array_unique($microblogCondition);

         if($this->_postExists('name') && strlen($this->_post('name')) > 2)
         {
            DB::query('SELECT
                           `conditionid`
                       FROM
                           `condition`
                       WHERE
                           `name` = ?
                       AND
                           `unternehmenid` = ?', 2);
               DB::setParam($this->_post('name'), 'str');
               DB::setParam($_SESSION['unternehmenid'], 'int');
               DB::exec();

            if(DB::numRows() == 1)
            {
               DB::nextResult();

               if($this->_post('aktion') == 'anlegen' || ($this->_post('aktion') == 'update' && DB::result('conditionid') != $this->_post('conditionid')))
               {
                  $this->_jsonOutput(2, 'there already exists a condition with the same name');
               }
            }
         }
         else
         {
            $this->_jsonOutput(2, 'the name of the condition must have at least 3 characters');
         }

         if(!$this->_postExists('description') || strlen($this->_post('description')) < 3)
         {
            $this->_jsonOutput(2, 'the description of the condition must have at least 3 characters');
         }

         // all checks passed ... insert into db
         if($this->_post('aktion') == 'update')
         {
            DB::query('DELETE
                           `d1`,
                           `d2`,
                           `d3`,
                           `d4`
                       FROM
                           `condition` `d1`
                       INNER JOIN
                           `condition_variablen` `d2`
                       ON
                           `d1`.`conditionid` = `d2`.`conditionid`
                       INNER JOIN
                           `condition_texts` `d3`
                       ON
                           `d1`.`conditionid` = `d3`.`conditionid`
                       INNER JOIN
                           `condition_texts_accounts` `d4`
                       ON
                           `d3`.`textid` = `d4`.`textid`
                       WHERE
                           `d1`.`conditionid` = ?', 1);
            DB::setParam($this->_post('conditionid'), 'int');
            DB::exec();

            DB::query('INSERT INTO `condition`
                           (`conditionid`,
                            `unternehmenid`,
                            `name`,
                            `beschreibung`,
                            `critical`,
                            `dolock`)
                       VALUES
                           (?, ?, ?, ?, ?, ?)', 6);
            DB::setParam($this->_post('conditionid'), 'int');
            DB::setParam($_SESSION['unternehmenid'], 'int');
            DB::setParam($this->_post('name'), 'str');
            DB::setParam($this->_post('description'), 'str');
            DB::setParam($this->_post('critical'), 'int');
            DB::setParam($this->_post('dolock'), 'int');
            DB::exec();

            $id = $this->_post('conditionid');
         }
         else
         {
            DB::query('INSERT INTO `condition`
                           (`unternehmenid`,
                            `name`,
                            `beschreibung`,
                            `critical`,
                            `dolock`)
                       VALUES
                           (?, ?, ?, ?, ?)', 5);
            DB::setParam($_SESSION['unternehmenid'], 'int');
            DB::setParam($this->_post('name'), 'str');
            DB::setParam($this->_post('description'), 'str');
            DB::setParam($this->_post('critical'), 'int');
            DB::setParam($this->_post('dolock'), 'int');
            $id = DB::exec(true);
         }

         $settings = '#settings: ';
         $operators = array('<', '=', '>', '!=');
         $connectors = array('and', 'or');

         foreach($options['variableids'] as $k => $v)
         {
            DB::query('INSERT INTO `condition_variablen`
                           (`conditionid`,
                            `variableid`,
                            `operator`,
                            `wert`,
                            `connector`)
                       VALUES
                           (?, ?, ?, ?, ?)', 5);
            DB::setParam($id, 'int');
            DB::setParam($options['variableids'][$k], 'int');
            DB::setParam($options['operatoren'][$k], 'int');
            DB::setParam($options['werte'][$k], 'str');
            DB::setParam(is_numeric($this->postVars['connectors'][$k]) ? $this->postVars['connectors'][$k] : '-1', 'str');
            DB::exec();

            $settings .= '#'.$variables[$options['variableids'][$k]].' '.$operators[$options['operatoren'][$k]].' '.$options['werte'][$k].' '.(is_numeric($this->postVars['connectors'][$k]) ? $connectors[$this->postVars['connectors'][$k]] : '').' ';
         }

         if($this->postVars['microblogCondition'] == 1 && count($microblogCondition) > 0)
         {
            require INCLUDE_PATH.'/class/oauth.php';
            require INCLUDE_PATH.'/class/streamoauth.php';

            foreach($microblogCondition as $k => $v)
            {
               Funclib::sendStreamMessage($data[1][$v]['baseurl'],
                                          $data[1][$v]['name'],
                                          $data[1][$v]['authurl'],
                                          $data[1][$v]['requesturl'],
                                          $data[1][$v]['accessurl'],
                                          $data[1][$v]['costumerkey'],
                                          $data[1][$v]['costumersecret'],
                                          $data[1][$v]['oauth_token'],
                                          $data[1][$v]['oauth_token_secret'],
                                          $data[1][$v]['maxlength'],
                                          $_SESSION['unternehmenid'],
                                          $settings);
            }
         }

         foreach($this->postVars['messages'] as $k => $v)
         {
            DB::query('INSERT INTO `condition_texts`
                           (`conditionid`,
                            `text`)
                       VALUES
                           (?, ?)', 2);
            DB::setParam($id, 'int');
            DB::setParam($v[0], 'str');
            $id2 = DB::exec(true);

            foreach($v[1] as $k2 => $v2)
            {
               DB::query('INSERT INTO `condition_texts_accounts`
                              (`textid`,
                               `accountid`,
                               `type`)
                          VALUES
                              (?, ?, ?)', 3);
               DB::setParam($id2, 'int');
               DB::setParam($v2[1], 'int');
               DB::setParam($v2[0], 'int');
               DB::exec();
            }
         }
         break;

      case 'edit':
         DB::query('SELECT
                        `conditionid`
                    FROM
                        `condition`
                    WHERE
                        `conditionid` = ?
                    AND
                        `unternehmenid` = ?', 2);
         DB::setParam($this->_post('conditionid'), 'int');
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         if(DB::numRows() == 1)
         {
            $res = array();

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
                           `d1`.`conditionid` = ?
                       GROUP BY
                           `textid`', 1);
            DB::setParam($this->_post('conditionid'), 'int');
            DB::exec();

            while(DB::nextResult())
            {
               $notificationTexts[DB::result('textid')] = array('text' => DB::result('text'),
                                                                'accountids' => explode(',', DB::result('accountids')),
                                                                'accounttypes' => explode(',', DB::result('accounttypes')));
            }

            DB::query('SELECT
                           `d1`.`conditionid`,
                           `d1`.`name`,
                           `d1`.`beschreibung`,
                           `d1`.`critical`,
                           `d1`.`dolock`,
                           GROUP_CONCAT(CAST(`d2`.`textid` AS CHAR(5))) AS `textids`
                      FROM
                           `condition` `d1`
                      INNER JOIN
                           `condition_texts` `d2`
                      ON
                           `d1`.`conditionid` = `d2`.`conditionid`
                      WHERE
                           `d1`.`conditionid` = ?
                      GROUP BY
                           `conditionid`
                      ORDER BY
                           `critical` ASC,
                           `name` ASC', 1);
            DB::setParam($this->_post('conditionid'), 'int');
            DB::exec();

            DB::nextResult();

            $res = array('name' => DB::result('name'),
                         'description' => DB::result('beschreibung'),
                         'critical' => DB::result('critical'),
                         'dolock' => DB::result('dolock'),
                         'texts' => explode(',', DB::result('textids')),
                         'variableids' => array(),
                         'operatoren' => array(),
                         'werte' => array(),
                         'connectoren' => array(),
                         'sensorids' => array());

            foreach($res['texts'] as $k => $v)
            {
               $res['texts'][$k] = &$notificationTexts[$v];
            }

            DB::query('SELECT
                           `d1`.`conditionid`,
                           `d2`.`variableid`,
                           `d2`.`operator`,
                           `d2`.`wert`,
                           `d2`.`connector`,
                           `d3`.`sensorid`
                       FROM
                           `condition` `d1`
                       INNER JOIN
                           `condition_variablen` `d2`
                       ON
                           `d1`.`conditionid` = `d2`.`conditionid`
                       INNER JOIN
                           `sensoren_variablen` `d3`
                       ON
                           `d2`.`variableid` = `d3`.`variableid`
                       WHERE
                           `d1`.`conditionid` = ?
                       ORDER BY
                           `cvid` ASC', 1);
            DB::setParam($this->_post('conditionid'), 'int');
            DB::exec();

            while(DB::nextResult())
            {
               $res['variableids'][] = DB::result('variableid');
               $res['operatoren'][] = DB::result('operator');
               $res['connectoren'][] = DB::result('connector');
               $res['werte'][] = DB::result('wert');
               $res['sensorids'][] = DB::result('sensorid');
            }

            $this->_jsonOutput(1, $res);
         }
         else
         {
            $this->_jsonOutput(2, 'the selected condition does not exist');
         }
         break;

      case 'delete':
         DB::query('SELECT
                        `conditionid`
                    FROM
                        `condition`
                    WHERE
                        `conditionid` = ?
                    AND
                        `unternehmenid` = ?', 2);
         DB::setParam($this->_post('conditionid'), 'int');
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         if(DB::numRows() == 1)
         {
            DB::query('DELETE
                           `d1`,
                           `d2`,
                           `d3`,
                           `d4`,
                           `d5`
                       FROM
                           `condition` `d1`
                       INNER JOIN
                           `condition_variablen` `d2`
                       ON
                           `d1`.`conditionid` = `d2`.`conditionid`
                       INNER JOIN
                           `condition_texts` `d3`
                       ON
                           `d1`.`conditionid` = `d3`.`conditionid`
                       INNER JOIN
                           `condition_texts_accounts` `d4`
                       ON
                           `d3`.`textid` = `d4`.`textid`
                       LEFT JOIN
                           `fehler_log` `d5`
                       ON
                           `d1`.`conditionid` = `d5`.`conditionid`
                       WHERE
                           `d1`.`conditionid` = ?', 1);
            DB::setParam($this->_post('conditionid'), 'int');
            DB::exec();
         }
         else
         {
            $this->_jsonOutput(2, 'the selected condition does not exist');
         }
         break;

      default:
         break;
   }

   $this->_jsonOutput(1, 'succeed');

?>
