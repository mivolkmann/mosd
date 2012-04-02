<?php

   DB::query('SELECT
                  `name`,
                  `email`,
                  `login`,
                  `status`
              FROM
                  `unternehmen`
              WHERE
                  `status` != \'admin\'
              ORDER BY
                  `status` ASC,
                  `name` ASC', 0);
   DB::exec();

?>