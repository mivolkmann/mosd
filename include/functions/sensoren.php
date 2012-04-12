<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

   if($this->_get('sensor'))
   {
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
      DB::setParam($this->_get('sensor'), 'str');
      DB::setParam($_SESSION['unternehmenid'], 'int');
      DB::exec();

      if(DB::numRows() == 1)
      {
         $sensor = DB::resultRow();

         DB::query('SELECT
                        `name`,
                        `kommentar`,
                        `typ`
                    FROM
                        `sensoren_variablen`
                    WHERE
                        `sensorid` = ?
                    ORDER BY
                        `name` ASC', 1);
         DB::setParam($sensor['sensorid'], 'int');
         DB::exec();

         if($this->_getExists('editmode'))
         {
            $this->addJavascript('layer');
            $this->addJavascript('sensoren');
            $this->addJavascriptBlank('editOver();');
         }
      }
      else
      {
         $this->_reload('/start');
      }
   }
   else
   {
      $this->_reload('/start');
   }

?>
