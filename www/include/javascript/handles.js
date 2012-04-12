/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
function insertData(typ)
{
   var akt, p, i, s, a, j;

   akt = document.getElementsByName('insert_' + typ);

   if(akt.length < gSizes[typ])
   {
      p = cr('div');
      p.style.position = 'relative';

      s = cr('select');
      s.name = 'insert_' + typ;

      for(i in gHandles[typ])
      {
         for(j = 0; j < akt.length; j++)
         {
            if(gHandles[typ][i] == akt[j].value)
            {
               j = -1;
               break;
            }
         }

         if(j != -1)
         {
            s.options[s.options.length] = new Option(gHandles[typ][i], i);
         }
      }

      p.appendChild(s);

      a = cr('a');
      a.href = '#';
      a.title = 'Typ entfernen';
      a.className = 'absdel';
      a.style.top = '2px';
      a.style.right = '25px';
      a.appendChild(document.createTextNode('[x]'));

      a.onclick = function()
      {
         remove(this.parentNode);
         return false;
      }

      p.appendChild(a)

      $('insert_' + typ).appendChild(p);
   }
   else
   {
      alert('Fehler: Für diesen Typ stehen keine Accounts mehr zur Auswahl');
   }

}

function createHandle()
{
   var req, i, j,
   ele = [document.getElementsByName('insert_email'),
          document.getElementsByName('insert_stream'),
          document.getElementsByName('insert_sms')],
   ges = 0;

   var req = new request('arduino/uis/ajax/handles');
   req.setParam('aktion', 'create_handle');
   req.setParam('name', $('handle_neu_name').value);
   req.setParam('text', $('handle_neu_text').value);

   for(i = 0; i < 3; i++)
   {
      for(j = 0; j < ele[i].length; j++)
      {
         req.setParam('var[' + ges + '][0]', i);
         req.setParam('var[' + ges + '][1]', ele[i][j].value);
         ges++;
      }
   }

   req.setFunction(function()
   {
      location.reload();
   });

   req.send();
}

function deleteHandle(id)
{
   var req = new request('arduino/uis/ajax/handles');
   req.setParam('aktion', 'delete_handle');
   req.setParam('id', id);
   req.setFunction(function()
   {
      location.reload();
   });
   req.send();
}
