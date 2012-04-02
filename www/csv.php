<?php

   session_start();

   if(!empty($_SESSION['unternehmenid']))
   {
      require('/www/uis/include/constants.php');
      require(INCLUDE_PATH.'/class/roh/mailer.php');
      require(INCLUDE_PATH.'/class/funclib.php');
      require(INCLUDE_PATH.'/class/email.php');
      require(INCLUDE_PATH.'/class/querybuild.php');
      require(INCLUDE_PATH.'/class/database.php');
      require(INCLUDE_PATH.'/class/charhandling.php');

      DB::init(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);

      $url = Funclib::trimValues($_GET);

      if(!empty($url['id']))
      {
         $filename = 'all-statusmessages-from-sensor';
         $typ = 1;

         DB::query('SELECT
                        `sensorid`
                    FROM
                        `sensoren`
                    WHERE
                        `unternehmenid` = ?
                    AND
                        `sensorid` = ?', 2);
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::setParam($url['id'], 'str');
         DB::exec();
      }
      else
      {
         $filename = 'all-messages-from-all-sensors';
         $typ = 2;

         DB::query('SELECT
                        `sensorid`
                    FROM
                        `sensoren`
                    WHERE
                        `unternehmenid` = ?', 1);
         DB::setParam($_SESSION['unternehmenid'], 'int');
         DB::exec();
      }

      if(DB::numRows() > 0)
      {
         header('Content-Type: application/x-www-form-urlencoded');
         header('Content-Disposition: attachment; filename="'.$filename.'.csv"');
         $ids = array();

         while(DB::nextResult())
         {
            $ids[] = DB::result('sensorid');
         }

         $vars = array();

         DB::query('SELECT DISTINCT
                        `d3`.`name` AS `name`
                    FROM
                        `werte_log` `d1`
                    INNER JOIN
                        `werte_log_variablen` `d2`
                    ON
                        `d1`.`logid` = `d2`.`logid`
                    INNER JOIN
                        `sensoren_variablen` `d3`
                    ON
                        `d2`.`variableid` = `d3`.`variableid`
                    WHERE
                        `d1`.`sensorid` IN ('.implode(',', $ids).')
                    ORDER BY
                        `name` ASC', 0);
         DB::exec();

         while(DB::nextResult())
         {
            $vars[] = DB::result('name');
         }

         $varAnz = count($vars);

         DB::query('SELECT
                        `d1`.`logid`,
                        `d1`.`zeit`,
                        GROUP_CONCAT(CAST(`d2`.`wert` AS CHAR(32))) AS `werte`,
                        GROUP_CONCAT(`d3`.`name`) AS `variablen`,
                        `d4`.`name`
                    FROM
                        `werte_log` `d1`
                    INNER JOIN
                        `werte_log_variablen` `d2`
                    ON
                        `d1`.`logid` = `d2`.`logid`
                    INNER JOIN
                        `sensoren_variablen` `d3`
                    ON
                        `d2`.`variableid` = `d3`.`variableid`
                    INNER JOIN
                        `sensoren` `d4`
                    ON
                        `d3`.`sensorid` = `d4`.`sensorid`
                    WHERE
                        `d1`.`sensorid` IN ('.implode(',', $ids).')
                    GROUP BY
                        `logid`
                    ORDER BY
                        `logid` DESC', 0);
         DB::exec();

         $k = array('"time"'.($typ == 2 ? ';"device"' : ''));

         for($i = 0; $i < $varAnz; $i++)
         {
            $k[] = '"'.$vars[$i].'"';
         }

         print implode(';', $k)."\n";

         while(DB::nextResult())
         {
            $k = array('"'.DB::result('zeit').'"'.($typ == 2 ? ';"'.DB::result('name').'"' : ''));

            $w = explode(',', DB::result('werte'));
            $v = explode(',', DB::result('variablen'));
            $m = array();
            $vAnz = count($v);

            for($i = 0; $i < $vAnz; $i++)
            {
               $m[$v[$i]] = $w[$i];
             }

            for($i = 0; $i < $varAnz; $i++)
            {
               $k[] = !empty($m[$vars[$i]]) ? '"'.$m[$vars[$i]].'"' : '""';
            }

            print implode(';', $k)."\n";
         }

      }
      else
      {
         print 'the selected code does not exist';
      }
   }

?>