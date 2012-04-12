<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
?>
<div class="pvorlage1">
   <span class="pheading">Manage connect types <?php print $this->_getExists('editmode') ? '<a href="'.HTTP_HOST.'/connections">→ switch to viewmode</a>' : '<a href="'.HTTP_HOST.'/connections/editmode">→ switch to editmode</a>' ?></span>
   <fieldset class="anlegen">
      <h3>1. microblog connections</h3>
      <table cellspacing="0">
         <colgroup>
            <col width="180"/>
            <col width="245"/>
            <col width="245"/>
            <col width="80"/>
            <col width="20"/>
         </colgroup>
         <tr>
            <th>name</th>
            <th>base url</th>
            <th>api base url</th>
            <th>maxlength</th>
            <th></th>
         </tr>
<?php

   DB::moveBufferToCurrentResult('streams');

   while(DB::nextResult())
   {
?>
         <tr>
            <td><?php print DB::result('name') ?></td>
            <td><?php print DB::result('baseurl') ?></td>
            <td><?php print DB::result('apibase') ?></td>
            <td class="center"><?php print DB::result('maxlength') ?></td>
            <td>
<?php

         if($this->_getExists('editmode') && DB::result('unternehmenid') != 0)
         {

?>
<img src="<?php print REL_PATH ?>/img/edit.png" name="edit" onclick="editConnection(0, <?php print DB::result('streamurlid') ?>);return false;" title="edit microblog connection"/>
<?php

         }

?></td>
         </tr>
<?php

   }

?>
      </table>
   </fieldset>
   <fieldset class="anlegen">
      <h3>2. sms gateways</h3>
<?php

   DB::moveBufferToCurrentResult('sms');

   if(DB::numRows() > 0)
   {
?>
      <table cellspacing="0">
         <colgroup>
            <col width="180"/>
            <col width="490"/>
            <col width="80"/>
            <col width="20"/>
         </colgroup>
         <tr>
            <th>name</th>
            <th>gateway url</th>
            <th>maxlength</th>
            <th></th>
         </tr>
<?php

   while(DB::nextResult())
   {
?>
         <tr>
            <td><?php print DB::result('name') ?></td>
            <td title="<?php print DB::result('url') ?>"><?php print strlen(DB::result('url')) > 60 ? substr(DB::result('url'), 0, 54).' [...]' : DB::result('url') ?></td>
            <td class="center"><?php print DB::result('maxlength') ?></td>
            <td>
<?php

         if($this->_getExists('editmode') && DB::result('unternehmenid') != 0)
         {

?>
<img src="<?php print REL_PATH ?>/img/edit.png" name="edit" onclick="editConnection(1, <?php print DB::result('gatewayid') ?>);return false;" title="edit sms gateway"/>
<?php

         }

?></td>
         </tr>
<?php

   }

?>
      </table>
<?php
   }
   else
   {
      print '<p class="nonexist">there is no sms gateway available</p>';
   }

?>
   </fieldset>
</div>
<?php

   if($this->_getExists('editmode'))
   {

?>
<br />
<div class="pvorlage1">
   <span class="pheading">Add a new connection</span>
   <form method="post" action="" id="newconnection"></form>
</div>
<?php

   }

?>
