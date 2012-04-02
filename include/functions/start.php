<?php

   DB::query('SELECT
                  `d1`.`sensorid`,
                  `d1`.`sensorids`,
                  `d1`.`name`,
                  `d1`.`intervall`,
                  `d1`.`maxage`,
                  DATE_FORMAT(`d2`.`zeit`, \'%d.%m.%Y - %H:%i:%s\') AS `zeit`
              FROM
                  `sensoren` `d1`
              LEFT JOIN
                  (SELECT
                        `d1`.`zeit`,
                        `d1`.`sensorid`
                   FROM
                        `werte_log` `d1`
                   INNER JOIN
                        `sensoren` `d2`
                   ON
                        `d1`.`sensorid` = `d2`.`sensorid`
                   WHERE
                        `d2`.`unternehmenid` = ?
                   ORDER BY
                        `zeit` DESC) `d2`
              ON
                  `d1`.`sensorid` = `d2`.`sensorid`
              WHERE
                  `d1`.`unternehmenid` = ?
              GROUP BY
                  `sensorid`', 2);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

   DB::moveCurrentResultToBuffer('arduinos');

   DB::query('SELECT
                  `messageid`,
                  `text`,
                  `accounts`,
                  DATE_FORMAT(`zeit`, \'%d.%m.%Y - %H:%i:%s\') AS `zeit`
              FROM
                  `messages_log`
              WHERE
                  `unternehmenid` = ?
              ORDER BY
                  `messageid` DESC
              LIMIT 20', 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

   DB::moveCurrentResultToBuffer('messages');

   if($this->_getExists('editmode'))
   {
      $this->addJavascript('layer');
      $this->addJavascript('sensoren');
      $this->addJavascriptBlank('gSensor = new SensorData(\'sensor_neu\');');
      $this->addJavascriptBlank('gSensor.init();');
      $this->addJavascriptBlank('editOver();');
   }


?>