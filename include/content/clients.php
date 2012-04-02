<div class="pvorlage1">
   <span class="pheading">Clients <?php print $this->_getExists('editmode') ? '<a href="'.HTTP_HOST.'/settings">→ switch to viewmode</a>' : '<a href="'.HTTP_HOST.'/settings/editmode">→ switch to editmode</a>' ?></span>
<?php

   if(DB::numRows() > 0)
   {

   }
   else
   {
      print '<p class="nonexist">there are no clients existing</p>';
   }

?>
</div>