<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
?><div class="pvorlage1">
   <span class="pheading">Client settings <?php print $this->_getExists('editmode') ? '<a href="'.HTTP_HOST.'/settings">→ switch to viewmode</a>' : '<a href="'.HTTP_HOST.'/settings/editmode">→ switch to editmode</a>' ?></span>
   <fieldset class="hl">
      <legend>account data</legend>
      <label>company name:</label>
      <?php print DB::result('name') ?>
      <br />
      <label>contact email:</label>
      <?php print DB::result('email') ?>
      <br />
      <label>login name:</label>
      <?php print DB::result('login') ?>
      <br />
   </fieldset>
</div>
