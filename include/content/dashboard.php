<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
?>
<div class="pvorlage1">
   <span class="pheading">Dashboard<?php print $this->_getExists('editmode') ? '<a href="'.HTTP_HOST.'/dashboard">→ switch to viewmode</a>' : '<a href="'.HTTP_HOST.'/dashboard/editmode">→ switch to editmode</a>' ?></span>
   <form id="streams" method="post" action=""></form>
   <div style="margin: 5px 10px;">
      <strong>current existing devices:</strong><br />
<?php

   DB::query('SELECT
                  GROUP_CONCAT(CONCAT(\' \', `name`)) AS `name`
              FROM
                  `sensoren`
              WHERE
                  `unternehmenid` = ?', 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

   DB::nextResult();
   print DB::result('name');

?>
   </div>
</div>
