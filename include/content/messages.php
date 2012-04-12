<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
?>
<div class="pvorlage1" style="position: relative;">
   <span class="pheading">Message log<?php

   print ($sw->gs > 1 ? ' (entries '.$sw->von().' to '.$sw->bis().' of ' : ' (').$sw->ge.' messages total)';
   //print DB::numRows() > 0 ? ($this->_getExists('editmode') ? '<a href="'.HTTP_HOST.'/log/messages/'.$sw->a_s.'">→ switch to viewmode</a>' : '<a href="'.HTTP_HOST.'/log/messages/'.$sw->a_s.'/editmode">→ switch to editmode</a>') : ''

?></span>
<?php

   if(DB::numRows() > 0)
   {
      /*
      if($this->_getExists('editmode'))
      {

   ?>
      <form action="" method="post">
         <input type="hidden" name="aktion" value="flush"/>
         <input type="submit" class="submit flush" value="flush event log"/>
      </form>
   <?php

      }
      */

      if($keywordAnz > 0)
      {
         print '<p class="filter"><strong>remove filter:</strong> ';

         for($i = 0; $i < $keywordAnz; $i++)
         {
            $tmp = $keywords;
            unset($tmp[$i]);

            print '<a href="'.REL_PATH.'/log/messages/0/'.implode('/', $tmp).'">'.$keywords[$i].'</a>'.($i < $keywordAnz - 1 ? ', ' : '');
         }

         print '</p>';
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
      <span><?php print '#'.$c.' ['.preg_replace($search, $replace, DB::result('zeit')).']' ?> via <?php print DB::result('accounts') ?></span>
      <?php print Funclib::keywordUrl($keywords, DB::result('text')) ?>
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
      print '<p class="nonexist">there are still no messages available</p>';
   }

?>
</div>
