<?php

   switch($this->_post('aktion'))
   {
      case 'update_microblog_stream':
         DB::query('SELECT
                        `streamurlid`
                    FROM
                        `streamurls`
                    WHERE
                        `streamurlid` = ?', 1);
         DB::setParam($this->_post('id'), 'int');
         DB::exec();

         if(DB::numRows() == 0)
         {
            $this->_jsonOutput(2, 'the microblog connection does not exists');
         }

      case 'create_microblog_stream':
         if($this->_post('name') && strlen($this->_post('name')) > 2)
         {
            DB::query('SELECT
                              `streamurlid`
                          FROM
                              `streamurls`
                          WHERE
                              `name` = ?
                          AND
                              (  `unternehmenid` = ?
                               OR
                                 `unternehmenid` = 0)', 2);
            DB::setParam($this->_post('name'), 'str');
            DB::setParam($_SESSION['unternehmenid'], 'int');
            DB::exec();

            if(DB::numRows() == 1)
            {
               if($this->_post('aktion') != 'update_microblog_stream')
               {
                  $this->_jsonOutput(2, 'there already exists a microblog connection with the same name');
               }
               else
               {
                  DB::nextResult();

                  if($this->_post('id') != DB::result('streamurlid'))
                  {
                     $this->_jsonOutput(2, 'there already exists a microblog connection with the same name');
                  }
               }
            }

            if($this->_post('maxlength') > 0 && $this->_post('maxlength') < 1025)
            {
               $urls = array();

               if(!filter_var($this->_post('baseurl'), FILTER_VALIDATE_URL))
               {
                  $this->_jsonOutput(2, 'wrong base url set');
               }
               else
               {
                  $urls['baseurl'] = substr($this->_post('baseurl'), -1) != '/' ? $this->_post('baseurl').'/' : $this->_post('baseurl');
               }

               if(!filter_var($this->_post('apibase'), FILTER_VALIDATE_URL))
               {
                  $this->_jsonOutput(2, 'wrong api base url set');
               }
               else
               {
                  $urls['apibase'] = substr($this->_post('apibase'), -1) != '/' ? $this->_post('apibase').'/' : $this->_post('apibase');
               }

               if(!filter_var($this->_post('authurl'), FILTER_VALIDATE_URL))
               {
                  $this->_jsonOutput(2, 'wrong authorization url set');
               }

               if(!filter_var($this->_post('requesturl'), FILTER_VALIDATE_URL))
               {
                  $this->_jsonOutput(2, 'wrong request url set');
               }

               if(!filter_var($this->_post('accessurl'), FILTER_VALIDATE_URL))
               {
                  $this->_jsonOutput(2, 'wrong access url set');
               }

               if(!filter_var($this->_post('searchurl'), FILTER_VALIDATE_URL))
               {
                  $this->_jsonOutput(2, 'wrong search url set');
               }
               else
               {
                  $urls['searchurl'] = substr($this->_post('searchurl'), -1) != '/' ? $this->_post('searchurl').'/' : $this->_post('searchurl');
               }

               if($this->_post('aktion') == 'create_microblog_stream')
               {
                  DB::query('INSERT INTO `streamurls`
                                       (`unternehmenid`,
                                        `name`,
                                        `baseurl`,
                                        `apibase`,
                                        `authurl`,
                                        `requesturl`,
                                        `accessurl`,
                                        `searchurl`,
                                        `maxlength`)
                                   VALUES
                                       (?, ?, ?, ?, ?, ?, ?, ?, ?)', 9);
                  DB::setParam($_SESSION['unternehmenid'], 'int');
                  DB::setParam($this->_post('name'), 'str');
                  DB::setParam($urls['baseurl'], 'str');
                  DB::setParam($urls['apibase'], 'str');
                  DB::setParam($this->_post('authurl'), 'str');
                  DB::setParam($this->_post('requesturl'), 'str');
                  DB::setParam($this->_post('accessurl'), 'str');
                  DB::setParam($urls['searchurl'], 'str');
                  DB::setParam($this->_post('maxlength'), 'str');
               }
               else
               {
                  DB::query('UPDATE
                                 `streamurls`
                             SET
                                 `name` = ?,
                                 `baseurl` = ?,
                                 `apibase` = ?,
                                 `authurl` = ?,
                                 `requesturl` = ?,
                                 `accessurl` = ?,
                                 `searchurl` = ?,
                                 `maxlength` = ?
                             WHERE
                                 `streamurlid` = ?', 9);
                  DB::setParam($this->_post('name'), 'str');
                  DB::setParam($urls['baseurl'], 'str');
                  DB::setParam($urls['apibase'], 'str');
                  DB::setParam($this->_post('authurl'), 'str');
                  DB::setParam($this->_post('requesturl'), 'str');
                  DB::setParam($this->_post('accessurl'), 'str');
                  DB::setParam($urls['searchurl'], 'str');
                  DB::setParam($this->_post('maxlength'), 'str');
                  DB::setParam($this->_post('id'), 'int');
               }

               DB::exec();
            }
            else
            {
               $this->_jsonOutput(2, 'the message maxlength must be a value between 1 and 1024 (characters)');
            }
         }
         else
         {
            $this->_jsonOutput(2, 'the name of the connection must consist of at least 3 characters');
         }
         break;

      case 'delete_microblog_stream':
         DB::query('SELECT
                        `d1`.`streamurlid`,
                        `d2`.`streamid`
                    FROM
                        `streamurls` `d1`
                    LEFT JOIN
                         `streaming` `d2`
                    ON
                        `d1`.`streamurlid` = `d2`.`streamurlid`
                    WHERE
                        `d1`.`streamurlid` = ?
                    AND
                        `d1`.`unternehmenid` = ?
                    LIMIT 1', 2);
         DB::setParam($this->_post('id'), 'int');
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         if(DB::numRows() == 1)
         {
            if(DB::resultIsEmpty('streamid'))
            {
               DB::query('DELETE FROM
                                 `streamurls`
                             WHERE
                                 `streamurlid` = ?', 1);
               DB::setParam($this->_post('id'), 'int');
               DB::exec();
            }
            else
            {
               $this->_jsonOutput(2, 'the microblog connection cannot be deleted, because it is used in some accounts');
            }
         }
         else
         {
            $this->_jsonOutput(2, 'the microblog connection does not exists');
         }
         break;

      case 'update_sms_gateway':
         DB::query('SELECT
                        `gatewayid`
                    FROM
                        `sms_gateways`
                    WHERE
                        `gatewayid` = ?', 1);
         DB::setParam($this->_post('id'), 'int');
         DB::exec();

         if(DB::numRows() == 0)
         {
            $this->_jsonOutput(2, 'the sms gateway does not exists');
         }

      case 'create_sms_gateway':
         if($this->_post('name') && strlen($this->_post('name')) > 2)
         {
            DB::query('SELECT
                           `gatewayid`
                       FROM
                           `sms_gateways`
                       WHERE
                           `name` = ?
                       AND
                           `unternehmenid` = ?', 2);
            DB::setParam($this->_post('name'), 'str');
            DB::setParam($_SESSION['unternehmenid'], 'int');
            DB::exec();

            if(DB::numRows() == 1)
            {
               if($this->_post('aktion') != 'update_sms_gateway')
               {
                  $this->_jsonOutput(2, 'there already exists a sms gateway with the same name');
               }
               else
               {
                  DB::nextResult();

                  if($this->_post('id') != DB::result('gatewayid'))
                  {
                     $this->_jsonOutput(2, 'there already exists a sms gateway with the same name');
                  }
               }
            }

            if($this->_post('maxlength') > 0 && $this->_post('maxlength') < 1025)
            {
               if(filter_var($this->_post('gateway'), FILTER_VALIDATE_URL))
               {
                  if($this->_post('aktion') == 'create_sms_gateway')
                  {
                     DB::query('INSERT INTO `sms_gateways`
                                       (`unternehmenid`,
                                        `name`,
                                        `url`,
                                        `maxlength`)
                                   VALUES
                                       (?, ?, ?, ?)', 4);
                     DB::setParam($_SESSION['unternehmenid'], 'int');
                     DB::setParam($this->_post('name'), 'str');
                     DB::setParam($this->_post('gateway'), 'str');
                     DB::setParam($this->_post('maxlength'), 'str');
                  }
                  else
                  {
                     DB::query('UPDATE
                                    `sms_gateways`
                                SET
                                    `name` = ?,
                                    `url` = ?,
                                    `maxlength` = ?
                                WHERE
                                    `gatewayid` = ?', 4);
                     DB::setParam($this->_post('name'), 'str');
                     DB::setParam($this->_post('gateway'), 'str');
                     DB::setParam($this->_post('maxlength'), 'str');
                     DB::setParam($this->_post('id'), 'int');
                  }

                  DB::exec();
               }
               else
               {
                  $this->_jsonOutput(2, 'there is a wrong gateway url set');
               }
            }
            else
            {
               $this->_jsonOutput(2, 'the message maxlength must be a value between 1 and 1024 (characters)');
            }
         }
         else
         {
            $this->_jsonOutput(2, 'the name of the connection must consist of at least 3 characters');
         }
         break;

      case 'delete_sms_gateway':
         DB::query('SELECT
                        `d1`.`gatewayid`,
                        `d2`.`nummerid`
                    FROM
                        `sms_gateways` `d1`
                    LEFT JOIN
                        `sms` `d2`
                    ON
                        `d1`.`gatewayid` = `d2`.`gatewayid`
                    WHERE
                        `d1`.`gatewayid` = ?
                    AND
                        `d1`.`unternehmenid` = ?
                    LIMIT 1', 2);
         DB::setParam($this->_post('id'), 'int');
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         if(DB::numRows() == 1)
         {
            DB::nextResult();

            if(DB::resultIsEmpty('nummerid'))
            {
               DB::query('DELETE FROM
                                 `sms_gateways`
                             WHERE
                                 `gatewayid` = ?', 1);
               DB::setParam($this->_post('id'), 'int');
               DB::exec();
            }
            else
            {
               $this->_jsonOutput(2, 'the sms gateway cannot be deleted, because it is used in some accounts');
            }
         }
         else
         {
            $this->_jsonOutput(2, 'the sms gateway does not exists');
         }
         break;

      case 'edit':
         switch($this->_post('type'))
         {
            case 0:
               DB::query('SELECT
                              `d1`.`name`,
                              `d1`.`baseurl`,
                              `d1`.`apibase`,
                              `d1`.`authurl`,
                              `d1`.`requesturl`,
                              `d1`.`accessurl`,
                              `d1`.`searchurl`,
                              `d1`.`maxlength`,
                              `d2`.`streamid`
                          FROM
                              `streamurls` `d1`
                          LEFT JOIN
                              `streaming` `d2`
                          ON
                              `d1`.`streamurlid` = `d2`.`streamurlid`
                          WHERE
                              `d1`.`streamurlid` = ?
                          AND
                              `d1`.`unternehmenid` = ?
                          LIMIT 1', 2);
               DB::setParam($this->_post('id'), 'int');
               DB::setParam($_SESSION['unternehmenid'], 'int');
               DB::exec();

               if(DB::numRows() == 1)
               {
                  DB::nextResult();

                  $res = array('name' => DB::result('name'),
                               'maxlength' => DB::result('maxlength'),
                               'stream' => array('baseurl' => DB::result('baseurl'),
                                                 'apibase' => DB::result('apibase'),
                                                 'authurl' => DB::result('authurl'),
                                                 'requesturl' => DB::result('requesturl'),
                                                 'accessurl' => DB::result('accessurl'),
                                                 'searchurl' => DB::result('searchurl')));

                  if(DB::resultIsEmpty('streamid'))
                  {
                     $res['deletable'] = 1;
                  }
               }
               else
               {
                  $this->_jsonOutput(2, 'the microblog connection does not exists');
               }
               break;

            case 1:
               DB::query('SELECT
                              `d1`.`name`,
                              `d1`.`url`,
                              `d1`.`maxlength`,
                              `d2`.`nummerid`
                          FROM
                              `sms_gateways` `d1`
                          LEFT JOIN
                              `sms` `d2`
                          ON
                              `d1`.`gatewayid` = `d2`.`gatewayid`
                          WHERE
                              `d1`.`gatewayid` = ?
                          AND
                              `d1`.`unternehmenid` = ?
                          LIMIT 1', 2);
               DB::setParam($this->_post('id'), 'int');
               DB::setParam($_SESSION['unternehmenid'], 'int');
               DB::exec();

               if(DB::numRows() == 1)
               {
                  DB::nextResult();

                  $res = array('name' => DB::result('name'),
                               'maxlength' => DB::result('maxlength'),
                               'sms' => DB::result('url'));

                  if(DB::resultIsEmpty('nummerid'))
                  {
                     $res['deletable'] = 1;
                  }
               }
               else
               {
                  $this->_jsonOutput(2, 'the sms gateway does not exists');
               }
               break;
         }

         $this->_jsonOutput(1, $res);
         break;
   }

   $this->_jsonOutput(1, 'succeed');

?>