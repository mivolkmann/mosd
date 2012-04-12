<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

   $keywords = $this->getGetVars();
   $qstr = '';
   $qstr2 = '';

   if(count($keywords) > 0)
   {
      foreach($keywords as $k => $v)
      {
         $keywordsEs[$k] = '"'.addslashes($v).'"';
      }

      DB::query('SELECT
                     `keywordid`,
                     `keyword`
                 FROM
                     `keywords`
                 WHERE
                     `keyword` IN ('.implode(',', $keywordsEs).')', 0);
      DB::exec();

      $keyTmp = array();
      $keywordAnz = 0;
      $qstr = ' AND (';

      while(DB::nextResult())
      {
         $qstr .= ($keywordAnz > 0 ? ' OR ' : ' ').'`d2`.`keywordid` = '.DB::result('keywordid');
         $keyTmp[] = DB::result('keyword');
         $keywordAnz++;
      }

      $qstr .= ')';
      $keywords = &$keyTmp;
      sort($keywords);

      if($keywordAnz > 0)
      {
         $qstr2 = ' HAVING COUNT(*) = '.$keywordAnz;
      }
      else
      {
         $qstr = '';
      }
   }

   DB::query('SELECT
                  `d1`.`messageid`,
                  COUNT(*) AS `gesamt`
              FROM
                  `messages_log` `d1`
              LEFT JOIN
                  `keywords_messages_rel` `d2`
              ON
                  `d1`.`messageid` = `d2`.`messageid`
              WHERE
                  `d1`.`unternehmenid` = ? '.$qstr.'
              GROUP BY
                  `messageid` '.$qstr2, 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

   $sw = new Seitenwechsel(HTTP_HOST.'/log/messages', '[?]', '<span>[?]</span>', DB::numRows(), 20, $this->_getContentParam('site') != null ? $this->_getContentParam('site') : 0);
   $sw->setUrlSuffix('/'.implode('/', $keywords));

   DB::query('SELECT
                  `d1`.`messageid`,
                  `d1`.`text`,
                  `d1`.`accounts`,
                  DATE_FORMAT(`d1`.`zeit`, \'%d.%m.%Y - %H:%i:%s\') AS `zeit`
              FROM
                  `messages_log` `d1`
              LEFT JOIN
                  `keywords_messages_rel` `d2`
              ON
                  `d1`.`messageid` = `d2`.`messageid`
              WHERE
                  `d1`.`unternehmenid` = ? '.$qstr.'
              GROUP BY
                  `messageid` '.$qstr2.'
              ORDER BY
                  `messageid` DESC
              LIMIT '.($sw->a_s * $sw->e_p_s).','.$sw->e_p_s, 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

?>
