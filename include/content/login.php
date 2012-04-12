<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
?><div id="login">
   <img src="<?php print IMAGE_PATH ?>uis.jpg" alt="Logo"/>
   <p>
      You need to login to use all functions of our system.
   </p>
   <form method="post" action="">
      <fieldset>
         <label>company:</label>
         <input type="text" name="unternehmen" id="focusele"/>
         <label>password:</label>
         <input type="password" name="passwort"/>
         <input type="hidden" name="aktion" value="login"/>
         <input type="submit" value="login ►" class="submit" style="margin: 10px 0px 0px 500px;"/>
      </fieldset>
   </form>
</div>
<div id="loginlogo">
   <img src="<?php print IMAGE_PATH ?>esf.jpg" alt="sponsored by ESF"/><br />
   The first version is being developed within the scope of the project <a href="http://ireko.tu-chemnitz.de/index.html.en" target="_blank" title="IREKO">IREKO</a> which is<br/>
   funded by the European Social Fund and the Free State of Saxony.
</div>
