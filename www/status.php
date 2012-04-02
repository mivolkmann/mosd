<?php

   //error_reporting(E_WARNING);

   require('/www/uis/include/constants.php');
   require(INCLUDE_PATH.'/class/roh/mailer.php');
   require(INCLUDE_PATH.'/class/funclib.php');
   require(INCLUDE_PATH.'/class/email.php');
   require(INCLUDE_PATH.'/class/querybuild.php');
   require(INCLUDE_PATH.'/class/database.php');
   require(INCLUDE_PATH.'/class/charhandling.php');
   require(INCLUDE_PATH.'/class/oauth.php');
   require(INCLUDE_PATH.'/class/streamoauth.php');

   DB::init(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);

   $url = Funclib::trimValues($_GET);

   DB::query('SELECT
                  `sensorid`,
                  `unternehmenid`,
                  `name`
              FROM
                  `sensoren`
              WHERE
                  `sensorids` = ?', 1);
   DB::setParam($url['id'], 'str');
   DB::exec();

   if(DB::numRows() == 1)
   {
      $data = DB::resultRow();
      $entryVars = array();
      $vars2 = explode('/', $url['vars']);
      $vars = array();

      for($i = 0; $i < count($vars2); $i += 2)
      {
         $vars[$vars2[$i]] = $vars2[$i + 1];
      }

      // internal log
      DB::query('INSERT INTO `werte_log`
                     (`sensorid`,
                      `zeit`)
                 VALUES
                     (?, NOW())', 1);
      DB::setParam($data['sensorid'], 'int');
      $id = DB::exec(true);

      DB::query('SELECT
                     `variableid`,
                     `name`
                 FROM
                     `sensoren_variablen`
                 WHERE
                     `sensorid` = ?', 1);
      DB::setParam($data['sensorid'], 'int');
      DB::exec();

      while(DB::nextResult())
      {
         $entryVars[DB::result('name')] = DB::result('variableid');
      }

      foreach($entryVars as $k => $v)
      {
         DB::query('INSERT INTO `werte_log_variablen`
                           (`logid`,
                            `variableid`,
                            `wert`)
                    VALUES
                           (?, ?, ?)', 3);
         DB::setParam($id, 'int');
         DB::setParam($v, 'str');
         DB::setParam($vars[$k], 'str');
         DB::exec();
      }

      // conditions
      $accounts = array(array(), array(), array());
      $signatur = $data['name'];

      // get required conditions
      DB::query('SELECT
                     GROUP_CONCAT(DISTINCT CAST(`d2`.`conditionid` AS CHAR(6))) AS `conditions`
                 FROM
                     `sensoren_variablen` `d1`
                 INNER JOIN
                     `condition_variablen` `d2`
                 ON
                     `d1`.`variableid` = `d2`.`variableid`
                 WHERE
                     `d1`.`sensorid` = ?', 1);
      DB::setParam($data['sensorid'], 'int');
      DB::exec();

      DB::nextResult();

      if(!DB::resultIsEmpty('conditions'))
      {
         $conditionids = DB::result('conditions');
         $vars = array();

         // get all variables in order
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
                        `d1`.`conditionid` IN ('.$conditionids.')
                    ORDER BY
                        `cvid` ASC', 0);
         DB::exec();

         while(DB::nextResult())
         {
            if(empty($vars[DB::result('conditionid')]))
            {
               $vars[DB::result('conditionid')] = array(array(), array(), array(), array());
            }

            $vars[DB::result('conditionid')][0][] = DB::result('variableid');
            $vars[DB::result('conditionid')][1][] = DB::result('operator');
            $vars[DB::result('conditionid')][2][] = DB::result('wert');
            $vars[DB::result('conditionid')][3][] = DB::result('connector');
         }

         // get all with this sensorid connected conditions
         DB::query('SELECT
                        `conditionid`,
                        `name`,
                        `dolock`,
                        `locked`
                    FROM
                        `condition`
                    WHERE
                        `conditionid` IN ('.$conditionids.')', 0);
         DB::exec();

         $actions = array(array(), array(), array(), array(), array());
         $conditions = array();

         while(DB::nextResult())
         {
            $conditions[DB::result('conditionid')] = array('name' => DB::result('name'),
                                                           'variablen' => &$vars[DB::result('conditionid')][0],
                                                           'operatoren' => &$vars[DB::result('conditionid')][1],
                                                           'werte' => &$vars[DB::result('conditionid')][2],
                                                           'connectoren' => &$vars[DB::result('conditionid')][3],
                                                           'dolock' => DB::result('dolock'),
                                                           'locked' => DB::result('locked'),
                                                           'text' => array());
         }

         DB::query('SELECT
                        `d1`.`textid`,
                        `d1`.`conditionid`,
                        `d1`.`text`,
                        GROUP_CONCAT(CAST(`d2`.`accountid` AS CHAR(5))) AS `accountids`,
                        GROUP_CONCAT(CAST(`d2`.`type` AS CHAR(1))) AS `types`
                    FROM
                        `condition_texts` `d1`
                    INNER JOIN
                        `condition_texts_accounts` `d2`
                    ON
                        `d1`.`textid` = `d2`.`textid`
                    WHERE
                        `d1`.`conditionid` IN ('.$conditionids.')
                    GROUP BY
                        `textid`', 0);
         DB::exec();

         while(DB::nextResult())
         {
            $conditions[DB::result('conditionid')]['text'][] = array('text' => DB::result('text'),
                                                                     'accountids' => explode(',', DB::result('accountids')),
                                                                     'types' => explode(',', DB::result('types')));
         }

         $varValues = array();

         // get sensorids

         DB::query('SELECT DISTINCT
                        GROUP_CONCAT(`d1`.`sensorid`) AS `sensorids`
                    FROM
                        `sensoren_variablen` `d1`
                    INNER JOIN
                        `condition_variablen` `d2`
                    ON
                        `d1`.`variableid` = `d2`.`variableid`
                    WHERE
                        `d2`.`conditionid` IN ('.$conditionids.')', 0);
         DB::exec();
         DB::nextResult();

         $sensorids = DB::result('sensorids');

         // required data from logfile
         DB::query('SELECT
                        `d1`.`variableid`,
                        `d1`.`wert`,
                        CONCAT(`d2`.`name`, \'.\', `d3`.`name`) AS `name`
                    FROM
                        (SELECT
                              `d1`.`logid`,
                              `d1`.`sensorid`,
                              `d2`.`variableid`,
                              `d2`.`wert`
                         FROM
                              `werte_log` `d1`
                         INNER JOIN
                              `werte_log_variablen` `d2`
                         ON
                              `d1`.`logid` = `d2`.`logid`
                         WHERE
                              `d1`.`sensorid` IN ('.$sensorids.')
                         ORDER BY
                              `logid` ASC) `d1`
                    INNER JOIN
                        `sensoren` `d2`
                    ON
                        `d1`.`sensorid` = `d2`.`sensorid`
                    INNER JOIN
                        `sensoren_variablen` `d3`
                    ON
                        `d1`.`variableid` = `d3`.`variableid`', 0);
         DB::exec();

         while(DB::nextResult())
         {
            $varValues[DB::result('variableid')] = array('wert' => DB::result('wert'),
                                                         'name' => DB::result('name'));
         }

         // notification data
         DB::query('SELECT
                        `emailid`,
                        `name`,
                        `email`
                    FROM
                        `email`
                    WHERE
                        `unternehmenid` = ?', 1);
         DB::setParam($data['unternehmenid'], 'int');
         DB::exec();

         while(DB::nextResult())
         {
            $accounts[0][DB::result('emailid')] = array('name' => DB::result('name'),
                                                        'email' => DB::result('email'));
         }

         DB::query('SELECT
                        `d1`.`streamid`,
                        `d1`.`name`,
                        `d1`.`costumerkey`,
                        `d1`.`costumersecret`,
                        `d1`.`oauth_token`,
                        `d1`.`oauth_token_secret`,
                        `d2`.`apibase`,
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
         DB::setParam($data['unternehmenid'], 'int');
         DB::exec();

         while(DB::nextResult())
         {
            $accounts[1][DB::result('streamid')] = DB::resultRow(true);
         }

         DB::query('SELECT
                        `d1`.`nummerid`,
                        `d1`.`nummer`,
                        `d1`.`name`,
                        `d2`.`url`,
                        `d2`.`name` as `gatewayname`,
                        `d2`.`maxlength`
                    FROM
                        `sms` `d1`
                    INNER JOIN
                        `sms_gateways` `d2`
                    ON
                        `d1`.`gatewayid` = `d2`.`gatewayid`
                    WHERE
                        `d2`.`unternehmenid` = ?', 1);
         DB::setParam($data['unternehmenid'], 'int');
         DB::exec();

         while(DB::nextResult())
         {
            $accounts[2][DB::result('nummerid')] = array('nummer' => DB::result('nummer'),
                                                         'url' => DB::result('url'),
                                                         'name' => DB::result('name'),
                                                         'gatewayname' => DB::result('gatewayname'),
                                                         'maxlength' => DB::result('maxlength'));
         }

         // walk conditions
         foreach($conditions as $k => $v)
         {
            $varAnz = count($v['variablen']);
            $search = array();
            $replace = array();

            for($i = 0; $i < $varAnz; $i++)
            {
               if(empty($varValues[$v['variablen'][$i]]))
               {
                  break;
               }

               $search[] = '#\['.preg_quote($varValues[$v['variablen'][$i]]['name']).'\]#';
               $replace[] = $varValues[$v['variablen'][$i]]['wert'];
            }

            if($i == $varAnz)
            {
               if(Funclib::checkConditionFullfilled($varValues,
                                                    $v['variablen'],
                                                    $v['werte'],
                                                    $v['operatoren'],
                                                    $v['connectoren']))
               {
                  if(!$v['locked'])
                  {
                     $textAnz = count($v['text']);

                     for($i = 0; $i < $textAnz; $i++)
                     {
                        $accAnz = count($v['text'][$i]['types']);
                        $accName = array();
                        $m = preg_replace($search, $replace, $v['text'][$i]['text']);

                        for($j = 0; $j < $accAnz; $j++)
                        {
                           $type = &$v['text'][$i]['types'][$j];
                           $accid = &$v['text'][$i]['accountids'][$j];
                           $acc = &$accounts[$type][$accid];
                           $accName[] = $acc['name'];

                           switch($type)
                           {
                              case 0:
                                 Funclib::sendAlarmMail($acc['email'],
                                                        $acc['name'],
                                                        $data['unternehmenid'],
                                                        $m);
                                 break;

                              case 1:
                                 Funclib::sendStreamMessage($acc['apibase'],
                                                            $acc['name'],
                                                            $acc['authurl'],
                                                            $acc['requesturl'],
                                                            $acc['accessurl'],
                                                            $acc['costumerkey'],
                                                            $acc['costumersecret'],
                                                            $acc['oauth_token'],
                                                            $acc['oauth_token_secret'],
                                                            $acc['maxlength'],
                                                            $data['unternehmenid'],
                                                            $m);
                                 break;

                              case 2:
                                 Funclib::sendSMS($acc['url'],
                                                  $acc['nummer'],
                                                  $acc['name'],
                                                  $acc['gatewayname'],
                                                  $acc['maxlength'],
                                                  $data['unternehmenid'],
                                                  $m);
                                 break;
                           }
                        }

                        Funclib::messageLog($data['unternehmenid'],
                                            $m,
                                            implode(', ', $accName));
                     }

                     if($v['dolock'])
                     {
                        Funclib::lockCondition($data['unternehmenid'],
                                               $k);
                        Funclib::insertError($data['unternehmenid'],
                                             $signatur,
                                             'now publish condition "'.$v['name'].'" is locked. Please delete this message to unlock this condition.',
                                             0,
                                             $k);
                     }
                  }
                  else
                  {
                     Funclib::insertError($data['unternehmenid'],
                                          $signatur,
                                          'couldn\'t execute publish condition "'.$v['name'].'". Reason: condition is locked',
                                          1);
                  }
               }
            }
            else
            {
               Funclib::insertError($data['unternehmenid'],
                                    $signatur,
                                    'couldn\'t execute publish condition "'.$v['name'].'". Reason: not all required variables are set',
                                    1);
            }
         }
      }
   }

?>