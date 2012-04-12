<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

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
