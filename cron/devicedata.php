<?php

   require('/www/uis/include/constants.php');
   require(INCLUDE_PATH.'/class/roh/mailer.php');
   require(INCLUDE_PATH.'/class/funclib.php');
   require(INCLUDE_PATH.'/class/email.php');
   require(INCLUDE_PATH.'/class/querybuild.php');
   require(INCLUDE_PATH.'/class/database.php');
   require(INCLUDE_PATH.'/class/charhandling.php');

   DB::init(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);

   // Device Data löschen

   DB::query('SELECT
                  `sensorid`,
                  `maxage`
              FROM
                  `sensoren`', 0);
   DB::exec();

   $sensors = array();

   while(DB::nextResult())
   {
      $sensors[DB::result('sensorid')] = DB::result('maxage');
   }

   foreach($sensors AS $k => $v)
   {
      DB::query('DELETE
                     `d1`,
                     `d2`
                 FROM
                     `werte_log` `d1`
                 LEFT JOIN
                     `werte_log_variablen` `d2`
                 ON
                     `d1`.`logid` = `d2`.`logid`
                 WHERE
                     `d1`.`sensorid` = ?
                 AND
                     UNIX_TIMESTAMP(`d1`.`zeit`) <= '.(time() - $v), 1);
      DB::setParam($k, 'int');
      DB::exec();
   }

   // published messages löschen

   // publishes events löschen

?>