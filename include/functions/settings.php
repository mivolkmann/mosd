<?php

   DB::query('SELECT
                  `name`,
                  `email`,
                  `login`
              FROM
                  `unternehmen`
              WHERE
                  `unternehmenid` = ?', 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

   DB::nextResult();

?>