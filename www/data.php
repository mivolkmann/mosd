<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
   error_reporting(E_ALL ^ E_NOTICE);

   require('../include/constants.php');
   require(INCLUDE_PATH.'/class/funclib.php');
   require(INCLUDE_PATH.'/class/querybuild.php');
   require(INCLUDE_PATH.'/class/database.php');
   require(INCLUDE_PATH.'/class/charhandling.php');

   DB::init(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);

   $url = Funclib::trimValues($_GET);

   if(!empty($url['id']))
   {
      DB::query('SELECT
                     `d1`.`sensorids`,
                     `d1`.`name` AS `sensorname`,
                     GROUP_CONCAT(`d2`.`name`) AS `vars`,
                     GROUP_CONCAT(`d2`.`typ`) AS `vartype`
                 FROM
                     `sensoren` `d1`
                 INNER JOIN
                     `sensoren_variablen` `d2`
                 ON
                     `d1`.`sensorid` = `d2`.`sensorid`
                 WHERE
                     `d1`.`unternehmenid` = ?', 1);
      DB::setParam($url['id'], 'int');
      DB::exec();

      if(DB::numRows() > 0)
      {
         $values = array();
         $num = 0;

         while(DB::nextResult())
         {
            $values[$num] = array('ids' => DB::result('sensorids'),
                                  'name' => DB::result('sensorname'),
                                  'vars' => array());

            $vars = explode(',', DB::result('vars'));
            $types = explode(',', DB::result('vartype'));
            $c = count($vars);

            for($i = 0; $i < $c; $i++)
            {
               $values[$num]['vars'][] = array('name' => $vars[$i],
                                               'type' => $types[$i]);
            }

            $num++;
         }

         print json_encode($values);
      }
      else
      {
         header('HTTP/1.0 404 Not Found');
      }
   }
   else
   {
      header('HTTP/1.0 404 Not Found');
   }

?>
