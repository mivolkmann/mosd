<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

   DB::query('SELECT
                  COUNT(*) AS `gesamt`
              FROM
                  `events_log`
              WHERE
                  `unternehmenid` = ?', 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();
   DB::nextResult();

   $sw = new Seitenwechsel(HTTP_HOST.'/log/events', '[?]', '<span>[?]</span>', DB::result('gesamt'), 15, $this->_getContentParam('site') != null ? $this->_getContentParam('site') : 0);

   DB::query('SELECT
                  `logid`,
                  `text`,
                  DATE_FORMAT(`zeit`, \'%d.%m.%Y - %H:%i:%s\') AS `zeit`
              FROM
                  `events_log`
              WHERE
                  `unternehmenid` = ?
              ORDER BY
                  `logid` DESC
              LIMIT '.($sw->a_s * $sw->e_p_s).','.$sw->e_p_s, 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

   $this->addJavascript('logs');

   if($this->_getExists('editmode'))
   {
      switch($this->_post('aktion'))
      {
         case 'flush':
            DB::query('DELETE FROM
                           `events_log`
                       WHERE
                           `unternehmenid` = ?', 1);
            DB::setParam($_SESSION['unternehmenid'], 'int');
            DB::exec();
            $this->_reload('/log/events');
            break;
      }

      $this->addJavascriptBlank('delOver();');
   }

?>
