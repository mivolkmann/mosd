<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
   switch($this->_post('aktion'))
   {
      case 'delete_fehler':
         DB::query('SELECT
                        `conditionid`
                    FROM
                        `fehler_log`
                    WHERE
                        `logid` = ?
                    AND
                        `unternehmenid` = ?', 2);
         DB::setParam($this->_post('logid'), 'int');
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         if(DB::numRows() == 1)
         {
            DB::nextResult();
            Funclib::lockCondition($_SESSION['unternehmenid'], DB::result('conditionid'), true);

            DB::query('DELETE FROM
                           `fehler_log`
                       WHERE
                           `logid` = ?', 1);
            DB::setParam($this->_post('logid'), 'int');
            DB::exec();
         }
         else
         {
            $this->_jsonOutput(2, 'the chosen error message does not exist');
         }
         break;

      case 'delete_event':
         DB::query('SELECT
                        `logid`
                    FROM
                        `events_log`
                    WHERE
                        `logid` = ?
                    AND
                        `unternehmenid` = ?', 2);
         DB::setParam($this->_post('logid'), 'int');
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();

         if(DB::numRows() == 1)
         {
            DB::query('DELETE FROM
                           `events_log`
                       WHERE
                           `logid` = ?', 1);
            DB::setParam($this->_post('logid'), 'int');
            DB::exec();
         }
         else
         {
            $this->_jsonOutput(2, 'the chosen error message does not exist');
         }
         break;

   }

   $this->_jsonOutput(1, 'succeed');

?>
