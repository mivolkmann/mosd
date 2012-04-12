<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
?><div class="pvorlage1">
   <span class="pheading">Error log<?php print $sw->gs > 1 ? ' (entries '.$sw->von().' to '.$sw->bis().' of '.$sw->ge.')' : '' ?> <?php print DB::numRows() > 0 ? ($this->_getExists('editmode') ? '<a href="'.HTTP_HOST.'/log/errors/'.$sw->a_s.'">→ switch to viewmode</a>' : '<a href="'.HTTP_HOST.'/log/errors/'.$sw->a_s.'/editmode">→ switch to editmode</a>') : '' ?></span>
<?php

   if(DB::numRows() > 0)
   {
      if($this->_getExists('editmode'))
      {

?>
   <form action="" method="post">
      <input type="hidden" name="aktion" value="flush"/>
      <input type="submit" class="submit flush" value="flush error log"/>
   </form>
<?php

      }

      if($sw->gs > 1)
      {
         print '<p class="seitenwechsel"><strong>page:</strong> '.$sw->siteChange().'</p>';
      }

      $sw->reset();
      $c = $sw->ge - ($sw->a_s * $sw->e_p_s);

      $search = array('#'.date('d.m.Y').'#', '#'.date('d.m.Y', time() - 86400).'#');
      $replace = array('Today', 'Yesterday');

      while(DB::nextResult())
      {
?>
   <p class="listing">
      <span class="<?php print DB::result('typ') == 0 ? 'warning' : 'fatal' ?>"><?php print '#'.$c.' ['.preg_replace($search, $replace, DB::result('zeit')).'] '.DB::result('beschreibung') ?></span>
<?php

         if($this->_getExists('editmode'))
         {

?>
      <img src="<?php print REL_PATH ?>/img/abort.png" onclick="deleteLog(<?php print DB::result('logid') ?>, 1);return false" name="del" title="delete this error message" class="absdel"/>
<?php

         }

?>
      <?php print DB::result('text') ?>
   </p>
<?php
         $c--;
      }

      if($sw->gs > 1)
      {
         print '<p class="seitenwechsel"><strong>page:</strong> '.$sw->siteChange().'</p>';
      }
   }
   else
   {
      print '<p class="nonexist">there is no error logged</p>';
   }

?>
</div>
