<?php

   $isSelected = false;

   if($this->_get('0'))
   {
      DB::query('SELECT
                     `sensorid`,
                     `name`
                 FROM
                     `sensoren`
                 WHERE
                     `sensorid` = ?
                 AND
                     `unternehmenid` = ?', 2);
      DB::setParam($this->_get('0'), 'int');
      DB::setParam($_SESSION['unternehmenid'], 'int');
      DB::exec();

      if(DB::numRows() == 1)
      {
         $data = DB::resultRow();

         $vars = array();

         DB::query('SELECT
                        DISTINCT `d3`.`name`
                    FROM
                        `werte_log` `d1`
                    LEFT JOIN
                        `werte_log_variablen` `d2`
                    ON
                        `d1`.`logid` = `d2`.`logid`
                    LEFT JOIN
                        `sensoren_variablen` `d3`
                    ON
                        `d2`.`variableid` = `d3`.`variableid`
                    WHERE
                        `d1`.`sensorid` = ?
                    ORDER BY
                        `name` ASC', 1);
         DB::setParam($this->_get('0'), 'int');
         DB::exec();

         while(DB::nextResult())
         {
            $vars[] = DB::result('name');
         }

         $varAnz = count($vars);

          DB::query('SELECT
                        COUNT(*) AS `gesamt`
                    FROM
                        `werte_log`
                    WHERE
                        `sensorid` = ?', 1);
         DB::setParam($this->_get('0'), 'int');
         DB::exec();

         DB::nextResult();

         $sw = new Seitenwechsel(HTTP_HOST.'/values/'.$this->_get('0'), '[?]', '<span>[?]</span>', DB::result('gesamt'), 25, is_numeric($this->_get('1')) ? $this->_get('1') : 0);

         DB::query('SELECT
                        `d1`.`logid`,
                        `d1`.`zeit`,
                        GROUP_CONCAT(CAST(`d2`.`wert` AS CHAR(32))) AS `werte`,
                        GROUP_CONCAT(`d3`.`name`) AS `variablen`
                    FROM
                        `werte_log` `d1`
                    LEFT JOIN
                        `werte_log_variablen` `d2`
                    ON
                        `d1`.`logid` = `d2`.`logid`
                    LEFT JOIN
                        `sensoren_variablen` `d3`
                    ON
                        `d2`.`variableid` = `d3`.`variableid`
                    WHERE
                        `d1`.`sensorid` = ?
                    GROUP BY
                        `logid`
                    ORDER BY
                        `logid` DESC
                    LIMIT '.($sw->a_s * $sw->e_p_s).','.$sw->e_p_s, 1);
         DB::setParam($this->_get('0'), 'int');
         DB::exec();
      }
      else
      {
         $this->_reload('/values');
      }

      $isSelected = true;
   }
   else
   {
      DB::query('SELECT
                     `d1`.`sensorid`,
                     `d1`.`name`,
                     `d2`.`gesamt`
                 FROM
                     `sensoren` `d1`
                 INNER JOIN
                     (SELECT
                           `d1`.`sensorid`,
                           COUNT(*) AS `gesamt`
                      FROM
                           `werte_log` `d1`
                      INNER JOIN
                           `sensoren` `d2`
                      ON
                           `d1`.`sensorid` = `d2`.`sensorid`
                      WHERE
                           `d2`.`unternehmenid` = ?
                      GROUP BY
                           `d1`.`sensorid`) `d2`
                 ON
                     `d1`.`sensorid` = `d2`.`sensorid`
                 ORDER BY
                     `name` ASC', 1);
      DB::setParam($_SESSION['unternehmenid'], 'int');
      DB::exec();
   }

?>