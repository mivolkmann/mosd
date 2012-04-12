<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
?>
<div class="pvorlage1" style="position: relative;">
   <span class="pheading"><?php print $sensor['name'] ?> <?php print $this->_getExists('editmode') ? '<a href="'.HTTP_HOST.'/sensors/sensor/'.$this->_get('sensor').'">→ switch to viewmode</a>' : '<a href="'.HTTP_HOST.'/sensors/sensor/'.$this->_get('sensor').'/editmode">→ switch to editmode</a>' ?></span>
<?php

   if(!$this->_getExists('editmode'))
   {

?>
   <a href="<?php print HTTP_HOST.'/csv/'.$sensor['sensorid'] ?>">
      <img src="<?php print REL_PATH ?>/img/csv.gif" title="export all messages from this sensor as CSV" class="absdel" style="top: 35px; right: 13px;"/>
   </a>
<?php

   }
   else
   {

?>
   <img src="<?php print REL_PATH ?>/img/edit.png" name="edit" title="edit this sensor" class="absdel" onclick="editSensor(<?php print $sensor['sensorid'] ?>)" style="top: 35px; right: 13px;"/>
<?php

   }

?>
   <fieldset class="anlegen">
      <label>device name:</label>
      <?php print $sensor['name'] ?>
      <br />
      <label>device description:</label>
      <?php print $sensor['beschreibung'] ?>
      <br />
      <label>connect interval:</label>
       <?php print Funclib::secondsToPeriods($sensor['intervall']) ?>
       <br />
      <label>device data max age:</label>
       <?php print Funclib::secondsToPeriods($sensor['maxage']) ?>
      <div class="var">
         <table cellpadding="0">
            <colgroup>
               <col width="120"/>
               <col width="110"/>
               <col width="300"/>
            </colgroup>
            <thead>
               <tr>
                  <th>name of variable</th>
                  <th>type of value</th>
                  <th>variable description</th>
               </tr>
            </thead>
<?php

      $url = 'http://'.IP.REL_PATH.'/sensor/'.$sensor['sensorids'];

      while(DB::nextResult())
      {
         $url .= '/'.DB::result('name').'/['.DB::result('name').']';
?>
            <tr>
               <td class="center"><?php print DB::result('name') ?></td>
               <td class="center"><?php print DB::result('typ') == 1 ? 'number' : 'string' ?></td>
               <td><?php print DB::result('kommentar') ?></td>
            </tr>
<?php

      }

?>
         </table>
      </div>
      <div class="var">
         <table cellpadding="0">
            <colgroup>
               <col width="110"/>
               <col width="590"/>
            </colgroup>
            <thead>
               <tr>
                  <th>url description</th>
                  <th>url</th>
               </tr>
            </thead>
            <tr>
               <td>variable url</td>
               <td>
                  <textarea onclick="this.select()" onkeyup="return false" onkeydown="return false" title="click to select this url"><?php print $url ?></textarea>
                  important: replace the values in brackets with the device values!
               </td>
            </tr>
            <tr>
               <td rowspan="2">intervall url</td>
               <td><strong>plaintext: </strong><input type="text" class="trans" value="<?php print 'http://'.IP.REL_PATH.'/intervall/'.$sensor['sensorids']  ?>" onclick="this.select()" onkeyup="return false" onkeydown="return false" title="click to select this url"/></td>
            </tr>
            <tr>
               <td><strong>json: </strong><input type="text" class="trans" value="<?php print 'http://'.IP.REL_PATH.'/intervall/'.$sensor['sensorids'].'/json' ?>" onclick="this.select()" onkeyup="return false" onkeydown="return false" title="click to select this url"/></td>
            </tr>
         </table>
      </div>
   </fieldset>
</div>
