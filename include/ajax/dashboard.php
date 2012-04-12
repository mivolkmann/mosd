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
                        `default`,
                        `name`
                    FROM
                        `dashboard`
                    WHERE
                        `dashboardid` = ?
                    AND
                        `unternehmenid` = ?', 2);
         DB::setParam($this->_post('dashboardid'), 'int');
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         if(DB::numRows() == 0)
         {
            $this->_jsonOutput(2, 'the select dashboard does not exist');
         }

         DB::nextResult();
         $isDefault = DB::result('default') == 1 ? true : false;
         $name = DB::result('name');

      case 'anlegen':
         if(strlen($this->_post('name')) > 2)
         {
            DB::query('SELECT
                           `dashboardid`
                       FROM
                           `dashboard`
                       WHERE
                           `name` = ?
                       AND
                           `unternehmenid` = ?', 2);
            DB::setParam($this->_post('name'), 'str');
            DB::setParam($_SESSION['unternehmenid'], 'int');
            DB::exec();

            if(DB::numRows() == 1)
            {
               if($this->_post('aktion') == 'anlegen')
               {
                  $this->_jsonOutput(2, 'the given name does already exist');
               }
               else
               {
                  DB::nextResult();

                  if(DB::result('dashboardid') != $this->_post('dashboardid'))
                  {
                     $this->_jsonOutput(2, 'the given name does already exist');
                  }
               }
            }
         }
         else
         {
            $this->_jsonOutput(2, 'the dashboard name must consist of at least 3 characters');
         }

         if($this->_post('aktion') == 'update' && $isDefault && $this->_post('default') == 0)
         {
            $this->_jsonOutput(2, 'cannot set dashboard as not default');
         }

         if(!empty($this->postVars['window']))
         {
            if(count($this->postVars['window']) > 6)
            {
               $this->_jsonOutput(2, 'there cannot be more than 6 windows per dashboard');
            }

            foreach($this->postVars['window'] as $k => $v)
            {
               if(!is_numeric($v[3]))
               {
                  $this->_jsonOutput(2, 'the result limit must be a number');
               }
               else if($v[3] < 1 || $v[3] > 20)
               {
                  $this->_jsonOutput(2, 'each result limit must be a value between 1 and 20 results');
               }

               if(!is_numeric($v[2]))
               {
                  $this->_jsonOutput(2, 'the reload time must be a number');
               }
               else if($v[2] < 8 || $v[2] > 60)
               {
                  $this->_jsonOutput(2, 'each reload time must be a value between 8 and 60 seconds');
               }

               if(strlen($v[1]) < 3)
               {
                  $this->_jsonOutput(2, 'each search value must consist of at least 3 characters');
               }

               if(is_numeric($v[0]))
               {
                  DB::query('SELECT
                                 `streamurlid`
                             FROM
                                 `streamurls`
                             WHERE
                                 `streamurlid` = ?
                             AND
                                 `unternehmenid` = ?
                             OR
                                 `unternehmenid` = 0', 2);
                  DB::setParam($v[0], 'int');
                  DB::setParam($_SESSION['unternehmenid'], 'int');
                  DB::exec();

                  if(DB::numRows() == 0)
                  {
                     $this->_jsonOutput(2, 'the selected microblog does not exist');
                  }
               }
               else
               {
                  $this->_jsonOutput(2, 'the selected microblog does not exist');
               }
            }
         }
         else
         {
            $this->_jsonOutput(2, 'there must be selected at least 1 window');
         }

         if($this->_post('default') == 1)
         {
            DB::query('UPDATE
                           `dashboard`
                       SET
                           `default` = 0
                       WHERE
                           `unternehmenid` = ?
                       AND
                           `default` = 1', 1);
            DB::setParam($_SESSION['unternehmenid'], 'int');
            DB::exec();
         }

         if($this->_post('aktion') == 'update')
         {
            DB::query('DELETE
                           `d1`,
                           `d2`
                       FROM
                           `dashboard` `d1`
                       INNER JOIN
                           `dashboard_windows` `d2`
                       ON
                           `d1`.`dashboardid` = `d2`.`dashboardid`
                       WHERE
                           `d1`.`dashboardid` = ?', 1);
            DB::setParam($this->_post('dashboardid'), 'int');
            DB::exec();

            DB::query('INSERT INTO `dashboard`
                           (`dashboardid`,
                            `unternehmenid`,
                            `name`,
                            `default`)
                       VALUES
                           (?, ?, ?, ?)', 4);
            DB::setParam($this->_post('dashboardid'), 'int');
            DB::setParam($_SESSION['unternehmenid'], 'int');
            DB::setParam($this->_post('name'), 'str');
            DB::setParam($this->_post('default') == 1 ? 1 : 0, 'int');
            DB::exec();

            $dashboardid = $this->_post('dashboardid');
         }
         else
         {
            DB::query('INSERT INTO `dashboard`
                           (`unternehmenid`,
                            `name`,
                            `default`)
                       VALUES
                           (?, ?, ?)', 3);
            DB::setParam($_SESSION['unternehmenid'], 'int');
            DB::setParam($this->_post('name'), 'str');
            DB::setParam($this->_post('default') == 1 ? 1 : 0, 'int');
            $dashboardid = DB::exec(true);
         }

         foreach($this->postVars['window'] as $k => $v)
         {
            DB::query('INSERT INTO `dashboard_windows`
                           (`dashboardid`,
                            `streamurlid`,
                            `keyword`,
                            `reload`,
                            `results`)
                       VALUES
                           (?, ?, ?, ?, ?)', 5);
            DB::setParam($dashboardid, 'int');
            DB::setParam($v[0], 'int');
            DB::setParam($v[1], 'str');
            DB::setParam($v[2], 'int');
            DB::setParam($v[3], 'int');
            DB::exec();
         }
         break;

      case 'loeschen':
         DB::query('SELECT
                        `default`
                    FROM
                        `dashboard`
                    WHERE
                        `dashboardid` = ?
                    AND
                        `unternehmenid` = ?', 2);
         DB::setParam($this->_post('dashboardid'), 'int');
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         if(DB::numRows() == 1)
         {
            DB::nextResult();

            if(DB::result('default') == 0)
            {
               DB::query('DELETE
                              `d1`,
                              `d2`
                          FROM
                              `dashboard` `d1`
                          INNER JOIN
                              `dashboard_windows` `d2`
                          ON
                              `d1`.`dashboardid` = `d2`.`dashboardid`
                          WHERE
                              `d1`.`dashboardid` = ?', 1);
               DB::setParam($this->_post('dashboardid'), 'int');
               DB::exec();
            }
            else
            {
               $this->_jsonOutput(2, 'the select dashboard could not be deleted, because it is the default dashboard');
            }
         }
         else
         {
            $this->_jsonOutput(2, 'the select dashboard does not exist');
         }
   }

   $this->_jsonOutput(1, 'succeed');

?>
