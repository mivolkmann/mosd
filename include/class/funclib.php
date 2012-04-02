<?php

class Funclib
{
   public static function sprint_r($arr, $bezeichner = 'Array')
   {
      return self::_sprint_rRec($bezeichner, $arr, 0);
   }

   private static function _sprint_rRec($key, $val, $indent)
   {
      if(is_array($val))
      {
         $arr = sprintf('%'.($indent + 5 + strlen($key)).'s', '['.$key.'] =>')."\n";
         $arr .= sprintf('%'.($indent + 10 + strlen($key)).'s', 'Array (')."\n";

         foreach($val AS $k => $v)
         {
            $arr .= self::_sprint_rRec($k, $v, $indent + strlen($key) + 10);
         }

         return $arr.sprintf('%'.($indent + 10 + strlen($key)).'s', ')')."\n";;
      }
      else
      {
         return sprintf('%'.($indent + strlen($key) + strlen($val) + 6).'s', '['.$key.'] => '.$val)."\n";
      }
   }

   public static function trimValues($arr)
   {
      return self::_trimValuesRec($arr);
   }

   private static function _trimValuesRec($val)
   {
      if(is_array($val))
      {
         $ret = array();

         foreach($val AS $k => $v)
         {
            $ret[$k] = self::_trimValuesRec($v);
         }

         return $ret;
      }
      else
      {
         return trim($val);
      }
   }

   public static function checkLogin($name, $passwort)
   {
      DB::query('SELECT
                     `unternehmenid`,
                     `passwort`
                 FROM
                     `unternehmen`
                 WHERE
                     `login` = ?', 1);
      DB::setParam($name, 'str');
      DB::exec();

      if(DB::numRows() == 1)
      {
         DB::nextResult();

         if(crypt($passwort, DB::result('passwort')) == DB::result('passwort'))
         {
            return DB::result('unternehmenid');
         }
         else
         {
            return 0;
         }
      }
      else
      {
         return -1;
      }
   }

   public static function sendAlarmMail($mail, $name, $unternehmenid, $text)
   {
      $m = new Mail();
      $m->setSubject('Alarm @ Middleware for Open Source Devices');
      $m->addAddress($mail, $name);
      $m->setMailContent($text);
      $m->send();

      self::insertEvent($unternehmenid, 'sent an email to your email account "'.$name.'"');
   }

   public static function sendStreamMessage($host, $name, $authurl, $requesturl, $accessurl, $costumerkey, $costumersecret, $oauth_token, $oauth_token_secret, $maxlength, $unternehmenid, $text)
   {
      try
      {
         $connection = new StreamOAuth($host, $costumerkey, $costumersecret, $oauth_token, $oauth_token_secret);

         $connection->setURLs($authurl, $requesturl, $accessurl);
         $connection->post('statuses/update', array('status' => substr($text, 0, $maxlength)));

         if($connection->getStatusCode() != 200)
         {
            if($connection->getStatusCode() == 403)
            {
               $connection->get('account/verify_credentials');

               if($connection->getStatusCode() == 403)
               {
                  self::insertError($unternehmenid,
                                    'wrong stream account',
                                    'Could not establish a connection to microblog account "'.$name.'" via host "'.$host.'". Is your account expired?',
                                    1);
               }
            }
         }
         else
         {
             self::insertEvent($unternehmenid, 'sent a message with your microblog account "'.$name.'" via host "'.$host.'"');
         }
      }
      catch(Exception $e)
      {
         self::insertError($unternehmenid,
                           'stream connection failed',
                           'Could not establish a connection to "'.$host.'"',
                           1);
      }
   }

   public static function sendSMS($url, $nummer, $accname, $gatewayname, $maxlength, $unternehmenid, $text)
   {
      $search = array('#\[dest_number\]#', '#\[message\]#');
      $replace = array($nummer, urlencode(substr($text, 0, $maxlength)));

      $url = preg_replace($search, $replace, $url);

      $ci = curl_init();
      curl_setopt($ci, CURLOPT_USERAGENT, ''); //TODO: conf useragent
      curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
      curl_setopt($ci, CURLOPT_TIMEOUT, 30);
      curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
      curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ci, CURLOPT_HEADER, false);
      curl_setopt($ci, CURLOPT_URL, $url);

      $response = curl_exec($ci);
      $code = curl_getinfo($ci, CURLINFO_HTTP_CODE);

      if($code == 200)
      {
         self::insertEvent($unternehmenid, 'sent an sms to "'.$accname.'" ('.$nummer.') via gateway "'.$gatewayname.'"');
      }
      else
      {
         self::insertError($unternehmenid,
                           'sending sms failed',
                           'sending an sms to "'.$accname.'" ('.$nummer.') via gateway "'.$gatewayname.'" failed',
                           1);
      }
   }

   public static function insertError($unternehmenid, $beschreibung, $text, $typ, $conditionid = 0)
   {
      DB::query('INSERT INTO `fehler_log`
                     (`unternehmenid`,
                      `beschreibung`,
                      `text`,
                      `typ`,
                      `conditionid`,
                      `zeit`)
                 VALUES
                     (?, ?, ?, ?, ?, NOW())', 5);
      DB::setParam($unternehmenid, 'int');
      DB::setParam($beschreibung, 'str');
      DB::setParam($text, 'str');
      DB::setParam($typ, 'int');
      DB::setParam($conditionid, 'int');
      DB::exec();
   }

   public static function insertEvent($unternehmenid, $beschreibung)
   {
      DB::query('INSERT INTO `events_log`
                     (`unternehmenid`,
                      `text`,
                      `zeit`)
                 VALUES
                     (?, ?, NOW())', 2);
      DB::setParam($unternehmenid, 'int');
      DB::setParam($beschreibung, 'str');
      DB::exec();
   }

   public static function matchTags($text)
   {
      preg_match_all('#\#[A-Za-z0-9\_üäöÜÄÖß]+#', $text, $tags);
      $tagAnz = count($tags[0]);

      if($tagAnz > 0)
      {
         for($i = 0; $i < $tagAnz; $i++)
         {
            $tags[0][$i] = strtolower(substr($tags[0][$i], 1));
         }

         return array_unique($tags[0]);
      }
      else
      {
         return false;
      }
   }

   public static function messageLog($unternehmenid, $text, $accounts)
   {
      DB::query('INSERT INTO `messages_log`
                     (`unternehmenid`,
                      `text`,
                      `accounts`,
                      `zeit`)
                 VALUES
                     (?, ?, ?, NOW())', 3);
      DB::setParam($unternehmenid, 'int');
      DB::setParam($text, 'str');
      DB::setParam($accounts, 'str');
      $mid = DB::exec(true);

      if(($tags = self::matchTags($text)))
      {
         $tagAnz = count($tags);

         for($i = 0; $i < $tagAnz; $i++)
         {
            DB::query('SELECT
                           `keywordid`
                       FROM
                           `keywords`
                       WHERE
                           `keyword` = ?', 1);
            DB::setParam($tags[$i], 'str');
            DB::exec();

            if(DB::numRows() == 1)
            {
               DB::nextResult();
               $kid = DB::result('keywordid');
            }
            else
            {
               DB::query('INSERT INTO `keywords`
                              (`keyword`)
                          VALUES
                              (?)', 1);
               DB::setParam($tags[$i], 'str');
               $kid = DB::exec(true);
            }

            DB::query('INSERT INTO `keywords_messages_rel`
                           (`keywordid`,
                            `messageid`)
                       VALUES
                           (?, ?)', 2);
            DB::setParam($kid, 'int');
            DB::setParam($mid, 'int');
            DB::exec();
         }
      }
   }

   public static function lockCondition($unternehmenid, $conditionid, $unlock = false)
   {
      DB::query('UPDATE
                     `condition`
                 SET
                     `locked` = ?
                 WHERE
                     `conditionid` = ?
                 AND
                     `unternehmenid` = ?', 3);
      DB::setParam($unlock ? 0 : 1, 'int');
      DB::setParam($conditionid, 'int');
      DB::setParam($unternehmenid, 'int');
      DB::exec();
   }

   public static function checkAccountName($name, $allowontype = '', $allowonid = '')
   {
      DB::query('SELECT
                     `emailid`
                 FROM
                     `email`
                 WHERE
                     `name` = ?
                 AND
                     `unternehmenid` = ?', 2);
      DB::setParam($name, 'str');
      DB::setParam($_SESSION['unternehmenid'], 'int');
      DB::exec();

      if(DB::numRows() == 1)
      {
         DB::nextResult();

         if($allowontype == 1 && $allowonid == DB::result('emailid'))
         {
            return true;
         }
         else
         {
            return false;
         }
      }

      DB::query('SELECT
                    `streamid`
                 FROM
                    `streaming`
                 WHERE
                    `name` = ?
                 AND
                    `unternehmenid` = ?', 2);
      DB::setParam($name, 'str');
      DB::setParam($_SESSION['unternehmenid'], 'int');
      DB::exec();

      if(DB::numRows() == 1)
      {
         DB::nextResult();

         if($allowontype == 0 && $allowonid == DB::result('streamid'))
         {
            return true;
         }
         else
         {
            return false;
         }
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
                     `d1`.`name` = ?
                 AND
                     `d2`.`unternehmenid` = ?', 2);
       DB::setParam($name, 'str');
       DB::setParam($_SESSION['unternehmenid'], 'int');
       DB::exec();

      if(DB::numRows() == 1)
      {
         DB::nextResult();

         if($allowontype == 2 && $allowonid == DB::result('nummerid'))
         {
            return true;
         }
         else
         {
            return false;
         }
      }

      return true;
   }

   public function keywordUrl($currentwords, $text)
   {
      if(($tags = self::matchTags($text)))
      {
         $tagAnz = count($tags);
         $search = array();
         $replace = array();

         for($i = 0; $i < $tagAnz; $i++)
         {
            $search[] = '#(\#'.$tags[$i].')#i';

            if(!in_array($tags[$i], $currentwords))
            {
               $replace[] = '<a href="'.REL_PATH.'/log/messages/0/'.implode('/', array_merge($currentwords, (array)$tags[$i])).'">$1</a>';
            }
            else
            {
               $replace[] = '<strong>$1</strong>';
            }
         }

         $text = preg_replace($search, $replace, $text);
      }

      return $text;
   }

   public function checkConditionFullfilled($res, $var, $vals, $ops, $cons)
   {
      $bool = array();
      $varAnz = count($var);

      for($i = 0; $i < $varAnz; $i++)
      {
         if(($ops[$i] == 0 && $res[$var[$i]]['wert'] < $vals[$i]) ||
            ($ops[$i] == 1 && $res[$var[$i]]['wert'] == $vals[$i]) ||
            ($ops[$i] == 2 && $res[$var[$i]]['wert'] > $vals[$i]) ||
            ($ops[$i] == 3 && $res[$var[$i]]['wert'] != $vals[$i]))
         {
            $bool[$i] = true;
         }
         else
         {
            $bool[$i] = false;
         }
      }

      $and = array($bool[0]);
      $c = 0;

      for($i = 1; $i < $varAnz; $i++)
      {
         if($cons[$i - 1] == 0)
         {
            $and[$c] = $and[$c] && $bool[$i];
         }
         else
         {
            $and[$c + 1] = $bool[$i];
            $c++;
         }
      }

      $andAnz = count($and);

      for($i = 0; $i < $andAnz; $i++)
      {
         if($and[$i])
         {
            return true;
         }
      }

      return false;
   }

   public function secondsToPeriods($sec)
   {
      $periods = array();

      // days
      if($sec / 86400 >= 1)
      {
         $d = ($sec - ($sec % 86400)) / 86400;
         $periods[] = $d.' day'.($d != 1 ? 's' : '');
         $sec %= 86400;
      }

      // hours
      if($sec / 3600 >= 1)
      {
         $h = ($sec - ($sec % 3600)) / 3600;
         $periods[] = $h.' hour'.($h != 1 ? 's' : '');
         $sec %= 3600;
      }

      // minutes
      if($sec / 60 >= 1)
      {
         $m = ($sec - ($sec % 60)) / 60;
         $periods[] = $m.' minute'.($m != 1 ? 's' : '');
         $sec %= 60;
      }

      // seconds
      if($sec > 0)
      {
         $periods[] = $sec.' second'.($sec != 1 ? 's' : '');
      }

      return implode(', ', $periods);
   }
}

?>
