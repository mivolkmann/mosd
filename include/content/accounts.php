<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
?>
<div class="pvorlage1">
   <span class="pheading">Manage notification accounts <?php print $this->_getExists('editmode') ? '<a href="'.HTTP_HOST.'/accounts">→ switch to viewmode</a>' : '<a href="'.HTTP_HOST.'/accounts/editmode">→ switch to editmode</a>' ?></span>
   <div class="account">
      <table cellspacing="0">
         <colgroup>
            <col width="200"/>
            <col width="80"/>
            <col width="260"/>
            <col width="200"/>
            <col width="20"/>
         </colgroup>
         <tr>
            <th>account name</th>
            <th>type</th>
            <th>used connection</th>
            <th colspan="2"></th>
         </tr>
<?php

   foreach($data as $k => $v)
   {

?>
         <tr>
            <td><?php print $k ?></td>
            <td><?php print $v['type'] == 0 ? 'email' : ($v['type'] == 1 ? 'microblog' : 'sms') ?></td>
            <td><?php print $v['connection'] ?></td>
            <td><?php print !empty($v['more']) ? $v['more'] : '' ?></td>
            <td>
<?php

   if($this->_getExists('editmode'))
   {

?>
               <img src="<?php print REL_PATH ?>/img/edit.png" name="edit" onclick="editAccount(<?php print '\''.$v['type'].'\','.$v['id'] ?>);return false;" title="edit this account"/>
<?php

   }

?></td>
         </tr>
<?php

   }

?>
      </table>
   </div>
</div>
<?php

   if($this->_getExists('editmode'))
   {

?>
<br />
<div class="pvorlage1">
   <span class="pheading">Add a new account</span>
   <form method="post" action="" id="newaccount"></form>
</div>
<?php

   }

?>
