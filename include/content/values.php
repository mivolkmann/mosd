<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
?>
<div class="pvorlage1">
   <span class="pheading">Logged value data<?php print $this->_get('0') ? (($sw->gs > 1 ? ' (values '.$sw->von().' to '.$sw->bis().' of ' : ' (').$sw->ge.' values total)') : ''; ?></span>
<?php

   if($isSelected)
   {

?>
   <a href="<?php print HTTP_HOST.'/csv/'.$data['sensorid']?>">
      <img src="<?php print REL_PATH ?>/img/csv.gif" title="export all messages with this statuscode from this sensor as CSV" class="absdel" style="top: 35px; right: 13px;"/>
   </a>
   <h3 class="margin"><?php print $data['name'] ?></h3>
<?php

      if($sw->gs > 1)
      {
         print '<p class="seitenwechsel"><strong>page:</strong> '.$sw->siteChange().'</p>';
      }

      $sw->reset();

?>
   <table cellspacing="0">
      <colgroup>
         <col width="170"/>
<?php

      for($i = 0; $i < $varAnz; $i++)
      {
         print '<col width="70"/>';
      }

?>
      </colgroup>
      <tr>
         <th>time</th>
<?php

      for($i = 0; $i < $varAnz; $i++)
      {
         print '<th>'.$vars[$i].'</th>';
      }

?>
      </tr>
<?php

      while(DB::nextResult())
      {
         print '<tr><td><strong>'.DB::result('zeit').'</strong></td>';

         $w = explode(',', DB::result('werte'));
         $v = explode(',', DB::result('variablen'));
         $m = array();
         $vAnz = count($v);

         for($i = 0; $i < $vAnz; $i++)
         {
            $m[$v[$i]] = $w[$i];
         }

         for($i = 0; $i < $varAnz; $i++)
         {
            print '<td>'.(!empty($m[$vars[$i]]) ? $m[$vars[$i]] : '').'</td>';
         }

         print '</tr>';
      }

?>
   </table>
<?php

      if($sw->gs > 1)
      {
         print '<p class="seitenwechsel"><strong>page:</strong> '.$sw->siteChange().'</p>';
      }
   }
   else
   {
      if(DB::numRows() > 0)
      {
         print '<ul class="choose">';

         while(DB::nextResult())
         {
            print '<li><a href="'.REL_PATH.'/values/'.DB::result('sensorid').'">'.DB::result('name').'</a> ('.DB::result('gesamt').' values total)</li>';
         }

         print '</ul>';
      }
      else
      {
         print '<p class="nonexist">none of your sensors sent any message until now</p>';
      }
   }

?>
</div>
