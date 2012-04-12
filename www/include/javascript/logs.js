/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
 function deleteLog(id, type)
{
   var req = new request('/logs');
   req.setParam('aktion', type == 1 ? 'delete_fehler' : 'delete_event');
   req.setParam('logid', id);
   req.setFunction(function()
   {
      location.reload();
   });
   req.send();
}
