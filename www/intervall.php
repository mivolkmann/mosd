<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

	require('../include/constants.php');
   require(INCLUDE_PATH.'/class/roh/mailer.php');
   require(INCLUDE_PATH.'/class/funclib.php');
   require(INCLUDE_PATH.'/class/email.php');
   require(INCLUDE_PATH.'/class/querybuild.php');
   require(INCLUDE_PATH.'/class/database.php');
   require(INCLUDE_PATH.'/class/charhandling.php');

   DB::init(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);

   $url = Funclib::trimValues($_GET);

   DB::query('SELECT
                  `intervall`
              FROM
                  `sensoren`
              WHERE
                  `sensorids` = ?', 1);
   DB::setParam($url['id'], 'str');
   DB::exec();

   if(DB::numRows() == 1)
   {
      DB::nextResult();

      switch($url['method'])
      {
         case 'plain':
            print DB::result('intervall');
            break;

         case 'json':
            print '{"intervall":'.DB::result('intervall').'}';
            break;

         default:
            break;
      }
   }

?>
