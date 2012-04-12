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
                           `name`
                       FROM
                           `sensoren`
                       WHERE
                           `sensorid` = ?
                       AND
                           `unternehmenid` = ?', 2);
         DB::setParam($this->_post('sensorid'), 'int');
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         if(DB::numRows() == 1)
         {
            DB::nextResult();

            $name = DB::result('name');
            $varids = array();
            $hist = array();

            DB::query('SELECT
                           `variableid`,
                           `name`
                       FROM
                           `sensoren_variablen`
                       WHERE
                           `sensorid` = ?', 1);
            DB::setParam($this->_post('sensorid'), 'int');
            DB::exec();

            while(DB::nextResult())
            {
               $varids[DB::result('name')] = DB::result('variableid');
               $hist[DB::result('name')] = 0;
            }
         }
         else
         {
            $this->_jsonOutput(2, 'the selected sensor does not exist');
         }

      case 'create':
         if(strlen($this->_post('name')) < 3)
         {
            $this->_jsonOutput(2, 'the device name must have at least 3 characters');
         }
         else
         {
            DB::query('SELECT
                             `sensorid`
                       FROM
                             `sensoren`
                       WHERE
                             `name` = ?
                       AND
                             `unternehmenid` = ?', 2);
            DB::setParam($this->_post('name'), 'str');
            DB::setParam($_SESSION['unternehmenid'], 'str');
            DB::exec();

            if($this->_post('aktion') == 'create' && DB::numRows() == 1)
            {
               if($this->_post('aktion') == 'create')
               {
                  $this->_jsonOutput(2, 'there already exists a device with the same name');
               }
               else
               {
                  DB::nextResult();

                  if(DB::result('sensorid') != $this->_post('sensorid'))
                  {
                     $this->_jsonOutput(2, 'there already exists a device with the same name');
                  }
               }
            }
         }

         if(strlen($this->_post('beschreibung')) < 3)
         {
            $this->_jsonOutput(2, 'the device description must have at least 3 characters');
         }

         if(!is_numeric($this->_post('intervall')) || $this->_post('intervall') < 30 || $this->_post('intervall') > 86400)
         {
            $this->_jsonOutput(2, 'the connection interval must be a numeric value between 30 and 86400');
         }

         if(!is_numeric($this->_post('maxage')) || $this->_post('maxage') < 24 || $this->_post('maxage') > 8760)
         {
            $this->_jsonOutput(2, 'the device data max age must be a numeric value between 24 and 8670');
         }

         $varsAnz = 0;

         if(!empty($this->postVars['var']))
         {
            $vars = $this->postVars['var'];
            $varsAnz = count($vars);
         }

         if($varsAnz > 0)
         {
            if($varsAnz > 5)
            {
               $this->_jsonOutput(2, 'there cannot be set more than 5 variables per device');
            }

            foreach($vars as $k => $v)
            {
               if(strlen($v[0]) == 0)
               {
                  $this->_jsonOutput(2, 'names of variables cannot be empty');
               }

               if(strlen($v[1]) < 3)
               {
                  $this->_jsonOutput(2, 'the variable description must have at least 3 characters');
               }

               if($v[2] != 1 && $v[2] != 2)
               {
                  $this->_jsonOutput(2, 'variables must be set as string or number');
               }

               if($this->_post('aktion') == 'update' && is_numeric($hist[$v[0]]))
               {
                  $hist[$v[0]]++;
               }
            }

            if($this->_post('aktion') == 'update')
            {
               $vAnz = count($hist);

               foreach($hist as $k => $v)
               {
                  if($v == 0)
                  {
                     DB::query('SELECT
                                    `variableid`
                                FROM
                                    `condition_variablen`
                                WHERE
                                    `variableid` = ?', 1);
                     DB::setParam($varids[$k], 'int');
                     DB::exec();

                     if(DB::numRows() > 0)
                     {
                        $this->_jsonOutput(2, 'variable '.$k.' cannot be deleted, because it is used in a condition');
                     }
                  }
               }
            }
         }
         else
         {
            $this->_jsonOutput(2, 'there must be set at least 1 variable');
         }

         if($this->_post('aktion') == 'create')
         {
            DB::query('INSERT INTO `sensoren`
                              (`unternehmenid`,
                               `sensorids`,
                               `name`,
                               `beschreibung`,
                               `intervall`,
                               `maxage`)
                       VALUES
                              (?, ?, ?, ?, ?, ?)', 6);
            DB::setParam($_SESSION['unternehmenid'], 'int');
            DB::setParam(sha1(microtime()), 'str');
            DB::setParam($this->_post('name'), 'str');
            DB::setParam($this->_post('beschreibung'), 'str');
            DB::setParam($this->_post('intervall'), 'int');
            DB::setParam($this->_post('maxage') * 3600, 'int');

            $sensorid = DB::exec(true);
         }
         else
         {
            DB::query('UPDATE
                           `sensoren`
                       SET
                           `name` = ?,
                           `beschreibung` = ?,
                           `intervall` = ?,
                           `maxage` = ?
                       WHERE
                           `sensorid` = ?', 5);
            DB::setParam($this->_post('name'), 'str');
            DB::setParam($this->_post('beschreibung'), 'str');
            DB::setParam($this->_post('intervall'), 'int');
            DB::setParam($this->_post('maxage') * 3600, 'int');
            DB::setParam($this->_post('sensorid'), 'int');
            DB::exec();

            foreach($hist as $k => $v)
            {
               if($v == 0)
               {
                  DB::query('DELETE FROM
                                 `sensoren_variablen`
                             WHERE
                                 `variableid` = ?', 1);
                  DB::setParam($varids[$k], 'int');
                  DB::exec();
               }
               else
               {
                  $key = '';

                  foreach($vars as $k2 => $v2)
                  {
                     if($v2[0] == $k)
                     {
                        $key = $k2;
                     }
                  }

                  // name is not updateable ...
                  DB::query('UPDATE
                                 `sensoren_variablen`
                             SET
                                 `kommentar` = ?,
                                 `typ` = ?
                             WHERE
                                 `variableid` = ?', 3);
                  DB::setParam($vars[$key][1], 'str');
                  DB::setParam($vars[$key][2], 'int');
                  DB::setParam($varids[$k], 'int');
                  DB::exec();

                  unset($vars[$key]);
               }
            }

            if($name != $this->_post('name'))
            {
               $texts = array();

               DB::query('SELECT DISTINCT
                              `d4`.`textid`,
                              `d4`.`text`
                          FROM
                              `sensoren_variablen` `d1`
                          INNER JOIN
                              `condition_variablen` `d2`
                          ON
                              `d1`.`variableid` = `d2`.`variableid`
                          INNER JOIN
                              `condition` `d3`
                          ON
                              `d2`.`conditionid` = `d3`.`conditionid`
                          INNER JOIN
                              `condition_texts` `d4`
                          ON
                              `d3`.`conditionid` = `d4`.`conditionid`
                          WHERE
                              `d1`.`sensorid` = ?', 1);
               DB::setParam($this->_post('sensorid'), 'int');
               DB::exec();

               while(DB::nextResult())
               {
                  $texts[DB::result('textid')] = DB::result('text');
               }

               foreach($texts as $k => $v)
               {
                  DB::query('UPDATE
                                 `condition_texts`
                             SET
                                 `text` = ?
                             WHERE
                                 `textid` = ?', 2);
                  DB::setParam(preg_replace('#\['.$name.'\.#', '['.$this->_post('name').'.', $texts[$k]), 'str');
                  DB::setParam($k, 'int');
                  DB::exec();
               }
            }

            $sensorid = $this->_post('sensorid');
         }

         foreach($vars as $k => $v)
         {
            DB::query('INSERT INTO `sensoren_variablen`
                              (`sensorid`,
                               `name`,
                               `kommentar`,
                               `typ`)
                       VALUES
                              (?, ?, ?, ?)', 4);
            DB::setParam($sensorid, 'int');
            DB::setParam($v[0], 'str');
            DB::setParam($v[1], 'str');
            DB::setParam($v[2], 'int');
            DB::exec();
         }
         break;

      case 'delete':
         DB::query('SELECT
                           `sensorid`
                       FROM
                           `sensoren`
                       WHERE
                           `sensorid` = ?
                       AND
                           `unternehmenid` = ?', 2);
         DB::setParam($this->_post('sensorid'), 'int');
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         if(DB::numRows() == 1)
         {
            $varids = array();

            DB::query('SELECT
                           `variableid`
                       FROM
                           `sensoren_variablen`
                       WHERE
                           `sensorid` = ?', 1);
            DB::setParam($this->_post('sensorid'), 'int');
            DB::exec();

            while(DB::nextResult())
            {
               $varids[] = DB::result('variableid');
            }

            foreach($varids as $k => $v)
            {
               DB::query('SELECT
                              `variableid`
                          FROM
                              `condition_variablen`
                          WHERE
                              `variableid` = ?', 1);
               DB::setParam($v, 'int');
               DB::exec();

               if(DB::numRows() > 0)
               {
                  $this->_jsonOutput(2, 'the sensor could not be deleted, because some variables are used in conditions');
               }
            }

            DB::query('DELETE
                           `d1`,
                           `d2`
                       FROM
                           `sensoren` `d1`
                       INNER JOIN
                           `sensoren_variablen` `d2`
                       ON
                           `d1`.`sensorid` = `d2`.`sensorid`
                       WHERE
                           `d1`.`sensorid` = ?', 1);
            DB::setParam($this->_post('sensorid'), 'int');
            DB::exec();
         }
         else
         {
            $this->_jsonOutput(2, 'the selected sensor does not exist');
         }
         break;

      case 'edit':
         DB::query('SELECT
                          `sensorid`,
                          `sensorids`,
                          `name`,
                          `beschreibung`,
                          `intervall`,
                          `maxage`
                     FROM
                          `sensoren`
                     WHERE
                          `sensorid` = ?
                     AND
                          `unternehmenid` = ?', 2);
         DB::setParam($this->_post('id'), 'str');
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         if(DB::numRows() == 1)
         {
            $res = array();
            $varids = array();
            DB::nextResult();

            $res = array('name' => DB::result('name'),
                         'description' => DB::result('beschreibung'),
                         'interval' => DB::result('intervall'),
                         'maxage' => DB::result('maxage') / 3600,
                         'vari' => array());

            DB::query('SELECT
                           `name`,
                           `kommentar`,
                           `typ`,
                           `variableid`
                       FROM
                           `sensoren_variablen`
                       WHERE
                           `sensorid` = ?
                       ORDER BY
                           `name` ASC', 1);
            DB::setParam($this->_post('id'), 'int');
            DB::exec();

            while(DB::nextResult())
            {
               $res['vari'][] = array('name' => DB::result('name'),
                                      'description' => DB::result('kommentar'),
                                      'type' => DB::result('typ'));
               $res['var'] .= ' '.DB::result('name');
               $varids[] = DB::result('variableid');
            }

            $vAnz = count($varids);
            $c = 0;

            foreach($varids as $k => $v)
            {
               DB::query('SELECT
                              `variableid`
                          FROM
                              `condition_variablen`
                          WHERE
                              `variableid` = ?', 1);
               DB::setParam($v, 'int');
               DB::exec();

               if(DB::numRows() > 0)
               {
                  break;
               }

               $c++;
            }

            if($vAnz == $c)
            {
               $res['deletable'] = 1;
            }

            $this->_jsonOutput(1, $res);
         }
         else
         {
            $this->_jsonOutput(2, 'the selected sensor does not exist');
         }
         break;
   }

   $this->_jsonOutput(1, 'succeed');

?>
