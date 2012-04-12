<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

?>
<div class="pvorlage1">
   <span class="pheading">Notification conditions <?php print $this->_getExists('editmode') ? '<a href="'.HTTP_HOST.'/conditions">→ switch to viewmode</a>' : '<a href="'.HTTP_HOST.'/conditions/editmode">→ switch to editmode</a>' ?></span>
<?php

   if(DB::numRows() > 0)
   {
      $conditions = array('smaller than', 'equal', 'bigger than', 'not');
      $connectors = array('and', 'or');
      $num = 1;

      $search = array('#(\[[A-Za-z0-9\_]{2,}\.[a-z0-9\_]{2,}\])#', '#(\#[a-zA-Z0-9\_üäöÜÄÖß]+)#');
      $replace = array('<span class="variable">$1</span>', '<span class="keyword">$1</span>');

      while(DB::nextResult())
      {
         $cond = '';
         $vars = &$conditionVars[DB::result('conditionid')];
         $varAnz = count($vars[0]);

         for($i = 0; $i < $varAnz; $i++)
         {
            if($i % 2 == 0)
            {
               $cond .= '<tr>';
            }

            $cond .= '<td><strong>'.$variablePath[$vars[0][$i]].'</strong></td><td style="color: #100061">'.$conditions[$vars[1][$i]].'</td><td><strong>'.$vars[2][$i].'</strong></td>'.($i < $varAnz - 1 ? '<td style="color: #006120">'.$connectors[$vars[3][$i]].'</td>' : '');

            if($i % 2 != 0)
            {
               $cond .= '</tr>';
            }
         }

         $cond .= '</tr>';

?>
   <h3 title="click to view details" class="margin<?php print DB::result('critical') == 1 ? ' critical' : '' ?>" onclick="$('condition_<?php print $num ?>').style.display=$('condition_<?php print $num ?>').style.display=='block'?'none':'block'">#<?php print $num ?> - notification condition "<?php print DB::result('name') ?>"<?php

         if($this->_getExists('editmode'))
         {

?>
      <img src="<?php print REL_PATH ?>/img/edit.png" name="edit" onclick="editCondition(<?php print DB::result('conditionid') ?>);return false;" title="edit this condition" class="absdel"/>
<?php

         }

?></h3>
   <div class="description"><strong>description: </strong><?php print DB::result('beschreibung') ?></div>
   <div class="showconditions" id="condition_<?php print $num ?>" style="display: none">
      <table cellspacing="0">
         <colgroup>
            <col width="200"/>
            <col width="90"/>
            <col width="60"/>
            <col width="30"/>
            <col width="200"/>
            <col width="90"/>
            <col width="60"/>
            <col width="30"/>
         </colgroup>
         <?php print $cond ?>
      </table>
<?php

         $texts = explode(',', DB::result('textids'));
         $textAnz = count($texts);

         for($i = 0; $i < $textAnz; $i++)
         {
            $tmp = &$notificationTexts[$texts[$i]];

?>
      <div>
         <div class="message2">
            <p>notification message #<?php print $i + 1 ?></p>
            <?php print preg_replace($search, $replace, $tmp[0]) ?>
         </div>
         <div class="accounts2">
            <p>notification accounts</p>
<?php

            $accAnz = count($tmp[1]);

            for($j = 0; $j < $accAnz; $j++)
            {
               print '<span>'.$data2[$tmp[2][$j]][$tmp[1][$j]].'</span>';
            }

?>
         </div>
      </div>
<?php
         }

?>
   </div>
<?php

         $num++;
      }
   }
   else
   {
      print '<p class="nonexist">there are no condition rules defined</p>';
   }

?>
</div>
<?php

   if($this->_getExists('editmode'))
   {

?>
<br />
<div class="pvorlage1">
   <span class="pheading">Add another condition rule</span>
   <form method="post" action="" id="newcond"></form>
</div>
<?php

   }

?>
