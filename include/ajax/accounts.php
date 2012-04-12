<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
   switch($this->_post('aktion'))
   {
      case 'update_email':
         DB::query('SELECT
                        `emailid`
                    FROM
                        `email`
                    WHERE
                        `emailid` = ?
                    AND
                        `unternehmenid` = ?', 2);
         DB::setParam($this->_post('id'), 'int');
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         if(DB::numRows() == 0)
         {
            $this->_jsonOutput(2, 'the email account does not exists');
         }

         $allowonid = $this->_post('id');

      case 'create_email':
         if($this->_post('name') && strlen($this->_post('name')) > 0)
         {
            if($this->_post('aktion') == 'create_email')
            {
               $allowonid = '';
            }

            if(Funclib::checkAccountName($this->_post('name'), 1, $allowonid))
            {
               if($this->_post('mail') && filter_var($this->_post('mail'), FILTER_VALIDATE_EMAIL))
               {
                  if($this->_post('aktion') == 'create_email')
                  {
                     DB::query('INSERT INTO `email`
                                    (`unternehmenid`,
                                     `email`,
                                     `name`)
                                VALUES
                                    (?, ?, ?)', 3);
                     DB::setParam($_SESSION['unternehmenid'], 'int');
                     DB::setParam($this->_post('mail'), 'str');
                     DB::setParam($this->_post('name'), 'str');
                  }
                  else
                  {
                     DB::query('UPDATE
                                    `email`
                                SET
                                    `email` = ?,
                                    `name` = ?
                                WHERE
                                    `emailid` = ?', 3);
                     DB::setParam($this->_post('mail'), 'str');
                     DB::setParam($this->_post('name'), 'str');
                     DB::setParam($this->_post('id'), 'int');
                  }

                  DB::exec();
               }
               else
               {
                  $this->_jsonOutput(2, 'there is a wrong email adress set');
               }
            }
            else
            {
               $this->_jsonOutput(2, 'there exists already an account with the same name');
            }
         }
         else
         {
            $this->_jsonOutput(2, 'the account name must be set');
         }
         break;

      case 'delete_email':
         DB::query('SELECT
                        `name`
                    FROM
                        `email`
                    WHERE
                        `emailid` = ?
                    AND
                        `unternehmenid` = ?', 2);
         DB::setParam($this->_post('id'), 'int');
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         if(DB::numRows() == 1)
         {
            DB::query('SELECT
                           `d2`.`accountid`
                       FROM
                           `condition_texts_accounts` `d1`
                       LEFT JOIN
                           `condition_texts_accounts` `d2`
                       ON
                           (   `d1`.`textid` = `d2`.`textid`
                            AND
                               `d1`.`accountid` != `d2`.`accountid`
                           )
                       WHERE
                           `d1`.`accountid` = ?
                       AND
                           `d1`.`type` = 0', 1);
            DB::setParam($this->_post('id'), 'int');
            DB::exec();

            if(DB::numRows() > 0)
            {
               while(DB::nextResult())
               {
                  if(DB::resultIsEmpty('accountid'))
                  {
                     $this->_jsonOutput(2, 'the email account could not be delete, because it is the only nofitification account in one or more of the notification messages');
                  }
               }
            }

            DB::query('DELETE
                           `d1`,
                           `d2`
                       FROM
                           `email` `d1`
                       LEFT JOIN
                           `condition_texts_accounts` `d2`
                       ON
                           (  `d1`.`emailid` = `d2`.`accountid`
                            AND
                              `d2`.`type` = 0)
                       WHERE
                           `d1`.`emailid` = ?', 1);
            DB::setParam($this->_post('id'), 'int');
            DB::exec();
         }
         else
         {
            $this->_jsonOutput(2, 'the email account does not exist');
         }
         break;

      case 'update_microblog':
         DB::query('SELECT
                        `streamid`
                    FROM
                        `streaming`
                    WHERE
                        `streamid` = ?
                    AND
                        `unternehmenid` = ?', 2);
         DB::setParam($this->_post('id'), 'int');
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         if(DB::numRows() == 0)
         {
            $this->_jsonOutput(2, 'the microblog account does not exist');
         }

         $allowonid = $this->_post('id');

      case 'create_microblog':
         if($this->_post('name') && strlen($this->_post('name')) > 0)
         {
            if($this->_post('aktion') == 'create_microblog')
            {
               $allowonid = '';
            }

            if(Funclib::checkAccountName($this->_post('name'), 0, $allowonid))
            {
               if($this->_post('costumerkey') && $this->_post('costumersecret'))
               {
                  $verified = false;

                  if(is_numeric($this->_post('streamurlid')))
                  {
                     include(INCLUDE_PATH.'/class/oauth.php');
                     include(INCLUDE_PATH.'/class/streamoauth.php');

                     DB::query('SELECT
                                    `apibase`,
                                    `authurl`,
                                    `requesturl`,
                                    `accessurl`
                                FROM
                                    `streamurls`
                                WHERE
                                    `streamurlid` = ?
                                AND
                                    (`unternehmenid` = ?
                                OR
                                    `unternehmenid` = 0)', 2);
                     DB::setParam($this->_post('streamurlid'), 'int');
                     DB::setParam($_SESSION['unternehmenid'], 'int');
                     DB::exec();

                     if(DB::numRows() == 1)
                     {
                        try
                        {
                           $connection = new StreamOAuth(DB::result('apibase'),
                                                         $this->_post('costumerkey'),
                                                         $this->_post('costumersecret'),
                                                         $this->_post('oauth_token'),
                                                         $this->_post('oauth_token_secret'));

                           $connection->setURLs(DB::result('authurl'), DB::result('requesturl'), DB::result('accessurl'));
                           $connection->get('account/verify_credentials');

                           if($connection->getStatusCode() != 200)
                           {
                              $this->_jsonOutput(2, 'the account could not be verified');
                           }
                        }
                        catch(Exception $e)
                        {
                           $this->_jsonOutput(2, 'there could be no connection to your connection type established');
                        }
                     }
                     else
                     {
                        $this->_jsonOutput(2, 'there is a wrong connection type set');
                     }
                  }
                  else
                  {
                     $this->_jsonOutput(2, 'please choose a connection type');
                  }

                  if($this->_post('aktion') == 'create_microblog')
                  {
                     DB::query('INSERT INTO `streaming`
                                       (`unternehmenid`,
                                        `name`,
                                        `streamurlid`,
                                        `costumerkey`,
                                        `costumersecret`,
                                        `oauth_token`,
                                        `oauth_token_secret`)
                                VALUES
                                       (?, ?, ?, ?, ?, ?, ?)', 7);
                     DB::setParam($_SESSION['unternehmenid'], 'int');
                     DB::setParam($this->_post('name'), 'str');
                     DB::setParam($this->_post('streamurlid'), 'str');
                     DB::setParam($this->_post('costumerkey'), 'str');
                     DB::setParam($this->_post('costumersecret'), 'str');
                     DB::setParam($this->_post('oauth_token'), 'str');
                     DB::setParam($this->_post('oauth_token_secret'), 'str');
                  }
                  else
                  {
                     DB::query('UPDATE
                                    `streaming`
                                SET
                                    `name` = ?,
                                    `streamurlid` = ?,
                                    `costumerkey` = ?,
                                    `costumersecret` = ?,
                                    `oauth_token` = ?,
                                    `oauth_token_secret` = ?
                                WHERE
                                    `streamid` = ?', 7);
                     DB::setParam($this->_post('name'), 'str');
                     DB::setParam($this->_post('streamurlid'), 'str');
                     DB::setParam($this->_post('costumerkey'), 'str');
                     DB::setParam($this->_post('costumersecret'), 'str');
                     DB::setParam($this->_post('oauth_token'), 'str');
                     DB::setParam($this->_post('oauth_token_secret'), 'str');
                     DB::setParam($this->_post('id'), 'int');
                  }

                  DB::exec();
               }
               else
               {
                  $this->_jsonOutput(2, 'costumer key and costumer secret must be set');
               }
            }
            else
            {
               $this->_jsonOutput(2, 'there exists already an account with the same name');
            }
         }
         else
         {
            $this->_jsonOutput(2, 'the account name must be set');
         }
         break;

      case 'verify_securecode':
         $verify = true;

      case 'get_oauth':
         if(is_numeric($this->_post('streamurlid')))
         {
            include(INCLUDE_PATH.'/class/oauth.php');
            include(INCLUDE_PATH.'/class/streamoauth.php');
            DB::query('SELECT
                           `apibase`,
                           `authurl`,
                           `requesturl`,
                           `accessurl`
                       FROM
                           `streamurls`
                       WHERE
                           `streamurlid` = ?
                       AND
                           (`unternehmenid` = ?
                       OR
                           `unternehmenid` = 0)', 2);
            DB::setParam($this->_post('streamurlid'), 'int');
            DB::setParam($_SESSION['unternehmenid'], 'int');
            DB::exec();

            if(DB::numRows() == 1)
            {
               DB::nextResult();

               if(!empty($verify))
               {
                  $connection = new StreamOAuth(DB::result('apibase'),
                                               $this->_post('costumerkey'),
                                               $this->_post('costumersecret'),
                                               $this->_post('oauth_token'),
                                               $this->_post('oauth_token_secret'));
                  $connection->setURLs(DB::result('authurl'), DB::result('requesturl'), DB::result('accessurl'));

                  $token = $connection->getAccessToken($this->_post('securecode'));

                  if(!empty($token['oauth_token']))
                  {
                     $this->_jsonOutput(1, array($token['oauth_token'], $token['oauth_token_secret']));
                  }
                  else
                  {
                     $this->_jsonOutput(2, 'your costumer key und your costumer secret could not be verified');
                  }
               }
               else
               {
                  $connection = new StreamOAuth(DB::result('apibase'),
                                               $this->_post('costumerkey'),
                                               $this->_post('costumersecret'));
                  $connection->setURLs(DB::result('authurl'), DB::result('requesturl'), DB::result('accessurl'));

                  $token = $connection->getRequestToken('oob');

                  if(!empty($token['oauth_token']))
                  {
                     $this->_jsonOutput(1, array($token['oauth_token'], $token['oauth_token_secret'], $connection->getAuthorizeURL($token['oauth_token'])));
                  }
                  else
                  {
                     $this->_jsonOutput(2, 'your costumer key und your costumer secret could not be verified');
                  }
               }
            }
            else
            {
               $this->_jsonOutput(2, 'there is a wrong connection type set');
            }
         }
         else
         {
            $this->_jsonOutput(2, 'please choose a connection type');
         }
         break;

      case 'delete_microblog':
         DB::query('SELECT
                        `name`
                    FROM
                        `streaming`
                    WHERE
                        `streamid` = ?
                    AND
                        `unternehmenid` = ?', 2);
         DB::setParam($this->_post('id'), 'int');
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         if(DB::numRows() == 1)
         {
            DB::query('SELECT
                           `d2`.`accountid`
                       FROM
                           `condition_texts_accounts` `d1`
                       LEFT JOIN
                           `condition_texts_accounts` `d2`
                       ON
                           (   `d1`.`textid` = `d2`.`textid`
                            AND
                               `d1`.`accountid` != `d2`.`accountid`
                           )
                       WHERE
                           `d1`.`accountid` = ?
                       AND
                           `d1`.`type` = 1', 1);
            DB::setParam($this->_post('id'), 'int');
            DB::exec();

            if(DB::numRows() > 0)
            {
               while(DB::nextResult())
               {
                  if(DB::resultIsEmpty('accountid'))
                  {
                     $this->_jsonOutput(2, 'the microblog account could not be delete, because it is the only nofitification account in one or more of the notification messages');
                  }
               }
            }

            DB::query('DELETE
                           `d1`,
                           `d2`
                       FROM
                           `streaming` `d1`
                       LEFT JOIN
                           `condition_texts_accounts` `d2`
                       ON
                           (  `d1`.`streamid` = `d2`.`accountid`
                            AND
                              `d2`.`type` = 1)
                       WHERE
                           `d1`.`streamid` = ?', 1);
            DB::setParam($this->_post('id'), 'int');
            DB::exec();
         }
         else
         {
            $this->_jsonOutput(2, 'the connection type does not exist');
         }
         break;

      case 'update_sms':
         DB::query('SELECT
                        `d1`.`name`,
                        `d1`.`gatewayid`,
                        `d1`.`nummer`
                    FROM
                        `sms` `d1`
                    INNER JOIN
                        `sms_gateways` `d2`
                    ON
                        `d1`.`gatewayid` = `d2`.`gatewayid`
                    WHERE
                        `d1`.`nummerid` = ?
                    AND
                        `d2`.`unternehmenid` = ?', 2);
         DB::setParam($this->_post('id'), 'int');
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         if(DB::numRows() == 0)
         {
            $this->_jsonOutput(2, 'the sms account does not exist');
         }

         $allowonid = $this->_post('id');

      case 'create_sms':
         if($this->_post('name') && strlen($this->_post('name')) > 0)
         {
            if($this->_post('aktion') == 'create_sms')
            {
               $allowonid = '';
            }

            if(Funclib::checkAccountName($this->_post('name'), 2, $allowonid))
            {
               if(is_numeric($this->_post('number')))
               {
                  DB::query('SELECT
                                 `gatewayid`
                             FROM
                                 `sms_gateways`
                             WHERE
                                 `gatewayid` = ?
                             AND
                                 `unternehmenid` = ?', 2);
                  DB::setParam($this->_post('gatewayid'), 'int');
                  DB::setParam($_SESSION['unternehmenid'], 'int');
                  DB::exec();

                  if(DB::numRows() == 1)
                  {
                     if($this->_post('aktion') == 'create_sms')
                     {
                        DB::query('INSERT INTO `sms`
                                       (`gatewayid`,
                                        `nummer`,
                                        `name`)
                                   VALUES
                                       (?, ?, ?)', 3);
                        DB::setParam($this->_post('gatewayid'), 'int');
                        DB::setParam($this->_post('number'), 'str');
                        DB::setParam($this->_post('name'), 'str');
                     }
                     else
                     {
                        DB::query('UPDATE
                                       `sms`
                                   SET
                                       `gatewayid` = ?,
                                       `nummer` = ?,
                                       `name` = ?
                                   WHERE
                                       `nummerid` = ?', 4);
                        DB::setParam($this->_post('gatewayid'), 'int');
                        DB::setParam($this->_post('number'), 'str');
                        DB::setParam($this->_post('name'), 'str');
                        DB::setParam($this->_post('id'), 'str');
                     }

                     DB::exec();
                  }
                  else
                  {
                     $this->_jsonOutput(2, 'the chosen gateway does not exists');
                  }
               }
               else
               {
                  $this->_jsonOutput(2, 'there is a wrong mobile number set');
               }
            }
            else
            {
               $this->_jsonOutput(2, 'there exists already an account with the same name');
            }
         }
         else
         {
            $this->_jsonOutput(2, 'the account name must be set');
         }
         break;

      case 'delete_sms':
         DB::query('SELECT
                        `d1`.`name`
                    FROM
                        `sms` `d1`
                    INNER JOIN
                        `sms_gateways` `d2`
                    ON
                        `d1`.`gatewayid` = `d2`.`gatewayid`
                    WHERE
                        `d1`.`nummerid` = ?
                    AND
                        `d2`.`unternehmenid` = ?', 2);
         DB::setParam($this->_post('id'), 'int');
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         if(DB::numRows() == 1)
         {
            DB::query('SELECT
                           `d2`.`accountid`
                       FROM
                           `condition_texts_accounts` `d1`
                       LEFT JOIN
                           `condition_texts_accounts` `d2`
                       ON
                           (   `d1`.`textid` = `d2`.`textid`
                            AND
                               `d1`.`accountid` != `d2`.`accountid`
                           )
                       WHERE
                           `d1`.`accountid` = ?
                       AND
                           `d1`.`type` = 2', 1);
            DB::setParam($this->_post('id'), 'int');
            DB::exec();

            if(DB::numRows() > 0)
            {
               while(DB::nextResult())
               {
                  if(DB::resultIsEmpty('accountid'))
                  {
                     $this->_jsonOutput(2, 'the sms account could not be delete, because it is the only nofitification account in one or more of the notification messages');
                  }
               }
            }

            DB::query('DELETE
                           `d1`,
                           `d2`
                       FROM
                           `sms` `d1`
                       LEFT JOIN
                           `condition_texts_accounts` `d2`
                       ON
                           (  `d1`.`nummerid` = `d2`.`accountid`
                            AND
                              `d2`.`type` = 2)
                       WHERE
                           `d1`.`nummerid` = ?', 1);
            DB::setParam($this->_post('id'), 'int');
            DB::exec();
         }
         else
         {
            $this->_jsonOutput(2, 'the sms account does not exist');
         }
         break;

      case 'edit':
         switch($this->_post('type'))
         {
            case 0:
               DB::query('SELECT
                              `name`,
                              `email`
                          FROM
                              `email`
                          WHERE
                              `emailid` = ?
                          AND
                              `unternehmenid` = ?', 2);
               DB::setParam($this->_post('id'), 'int');
               DB::setParam($_SESSION['unternehmenid'], 'int');
               DB::exec();

               if(DB::numRows() == 1)
               {
                  DB::nextResult();

                  $res = array('name' => DB::result('name'),
                               'email' => DB::result('email'));
               }
               else
               {
                  $this->_jsonOutput(2, 'the email account does not exists');
               }
               break;

            case 1:
               DB::query('SELECT
                              `name`,
                              `streamurlid`,
                              `costumerkey`,
                              `costumersecret`,
                              `oauth_token`,
                              `oauth_token_secret`
                          FROM
                              `streaming`
                          WHERE
                              `streamid` = ?
                          AND
                              `unternehmenid` = ?', 2);
               DB::setParam($this->_post('id'), 'int');
               DB::setParam($_SESSION['unternehmenid'], 'int');
               DB::exec();

               if(DB::numRows() == 1)
               {
                  DB::nextResult();

                  $res = array('name' => DB::result('name'),
                               'stream' => array('streamurlid' => DB::result('streamurlid'),
                                                 'costumerkey' => DB::result('costumerkey'),
                                                 'costumersecret' => DB::result('costumersecret'),
                                                 'oauth_token' => DB::result('oauth_token'),
                                                 'oauth_token_secret' => DB::result('oauth_token_secret')));
               }
               else
               {
                  $this->_jsonOutput(2, 'the microblog account does not exists');
               }
               break;

            case 2:
               DB::query('SELECT
                              `d1`.`name`,
                              `d1`.`gatewayid`,
                              `d1`.`nummer`
                          FROM
                              `sms` `d1`
                          INNER JOIN
                              `sms_gateways` `d2`
                          ON
                              `d1`.`gatewayid` = `d2`.`gatewayid`
                          WHERE
                              `d1`.`nummerid` = ?
                          AND
                              `d2`.`unternehmenid` = ?', 2);
               DB::setParam($this->_post('id'), 'int');
               DB::setParam($_SESSION['unternehmenid'], 'int');
               DB::exec();

               if(DB::numRows() == 1)
               {
                  DB::nextResult();

                  $res = array('name' => DB::result('name'),
                               'sms' => array('gatewayid' => DB::result('gatewayid'),
                                              'number' => DB::result('nummer')));
               }
               else
               {
                  $this->_jsonOutput(2, 'the microblog account does not exists');
               }
               break;

            default:
               $this->_jsonOutput(2, 'the selected account does not exist');
         }

         DB::query('SELECT
                        `d2`.`accountid`
                    FROM
                        `condition_texts_accounts` `d1`
                    LEFT JOIN
                        `condition_texts_accounts` `d2`
                    ON
                        (   `d1`.`textid` = `d2`.`textid`
                         AND
                            `d1`.`accountid` != `d2`.`accountid`
                        )
                    WHERE
                        `d1`.`accountid` = ?
                    AND
                        `d1`.`type` = ?', 2);
         DB::setParam($this->_post('id'), 'int');
         DB::setParam($this->_post('type'), 'int');
         DB::exec();

         if(DB::numRows() > 0)
         {
            $c = 0;

            while(DB::nextResult())
            {
               if(!DB::resultIsEmpty('accountid'))
               {
                  break;
               }

               $c++;
            }

            if($c == DB::numRows())
            {
               $res['deletable'] = 1;
            }
         }
         else
         {
            $res['deletable'] = 1;
         }

         $this->_jsonOutput(1, $res);
         break;

   }

   $this->_jsonOutput(1, 'succeed');

?>
