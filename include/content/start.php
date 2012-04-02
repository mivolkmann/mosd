<div class="pvorlage1">
   <span class="pheading">Sensor status view <?php print $this->_getExists('editmode') ? '<a href="'.HTTP_HOST.'/start">→ switch to viewmode</a>' : '<a href="'.HTTP_HOST.'/start/editmode">→ switch to editmode</a>' ?></span>
<?php

   DB::moveBufferToCurrentResult('arduinos');

   if(DB::numRows() > 0)
   {

?>
      <table cellspacing="0">
         <colgroup>
            <col width="160"/>
            <col width="160"/>
            <col width="100"/>
            <col width="190"/>
            <col width="150"/>
            <col width="20"/>
            <th></th>
         </colgroup>
         <tr>
            <th>device name</th>
            <th>last message</th>
            <th>intervall</th>
            <th>device data max age</th>
            <th></th>
            <th></th>
         </tr>
<?php

      while(DB::nextResult())
      {

?>
         <tr>
            <td><?php print DB::result('name') ?></td>
            <td><?php print DB::resultIsEmpty('zeit') ? 'no message received' : DB::result('zeit') ?></td>
            <td class="center"><?php print Funclib::secondsToPeriods(DB::result('intervall')) ?></td>
            <td class="center"><?php print Funclib::secondsToPeriods(DB::result('maxage')) ?></td>
            <td><a href="<?php print HTTP_HOST.'/sensors/sensor/'.DB::result('sensorid').($this->_getExists('editmode') ? '/editmode' : '') ?>" title="edit settings">show details</a></td>
            <td>
<?php

         if($this->_getExists('editmode'))
         {

?>
            <img src="<?php print REL_PATH ?>/img/edit.png" name="edit" onclick="editSensor('<?php print DB::result('sensorid') ?>');return false;" title="edit Sensor" name="del"/>

<?php

         }

?>
            </td>
         </tr>
<?php

      }

?>
      </table>
<?php

   }
   else
   {

?>
   <p class="nonexist">Sie haben bisher noch keine Sensoren angelegt!</p>
<?php

   }

?>
</div>
<br />
<?php

   if($this->_getExists('editmode'))
   {

?>
<div class="pvorlage1">
   <span class="pheading">create a new device</span>
   <form method="post" action="" id="sensor_neu"></form>
</div>
<br />
<?php

   }

?>
<div class="pvorlage1" style="position: relative;">
   <span class="pheading">Message log (last 20 messages)</span>
<?php

   DB::moveBufferToCurrentResult('messages');

   if(DB::numRows() > 0)
   {
      $search = array('#'.date('d.m.Y').'#', '#'.date('d.m.Y', time() - 86400).'#');
      $replace = array('Today', 'Yesterday');

      while(DB::nextResult())
      {

?>
   <p class="listing">
      <span><?php print '['.preg_replace($search, $replace, DB::result('zeit')).']' ?> via <?php print DB::result('accounts') ?></span>
      <?php print Funclib::keywordUrl(array(), DB::result('text')) ?>
   </p>
<?php

      }
   }
   else
   {
      print '<p class="nonexist">there are still no messages available</p>';
   }

?>
</div>