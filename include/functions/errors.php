<?php

   $sw = new Seitenwechsel(HTTP_HOST.'/log/errors', '[?]', '<span>[?]</span>', $fehlerAnzahl, 15, $this->_getContentParam('site') != null ? $this->_getContentParam('site') : 0);

   DB::query('SELECT
                  `logid`,
                  `beschreibung`,
                  `text`,
                  `typ`,
                  DATE_FORMAT(`zeit`, \'%d.%m.%Y - %H:%i:%s\') AS `zeit`
              FROM
                  `fehler_log`
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
            DB::query('SELECT
                           GROUP_CONCAT(CAST(`conditionid` AS CHAR(5))) AS `ids`
                       FROM
                           `fehler_log`
                       WHERE
                           `unternehmenid` = ?', 1);
            DB::setParam($_SESSION['unternehmenid'], 'int');
            DB::exec();

            if(DB::numRows() == 1)
            {
               DB::nextResult();

               DB::query('UPDATE
                              `condition`
                          SET
                              `locked` = 0
                          WHERE
                              `conditionid` IN ('.DB::result('ids').')', 0);
               DB::exec();
            }

            DB::query('DELETE FROM
                           `fehler_log`
                       WHERE
                           `unternehmenid` = ?', 1);
            DB::setParam($_SESSION['unternehmenid'], 'int');
            DB::exec();
            $this->_reload('/log/errors');
            break;
      }

      $this->addJavascriptBlank('delOver();');
   }

?>