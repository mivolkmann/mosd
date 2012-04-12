/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
function statusMessage(boxId)
{
   var createVarField, syntaxParsing, createNumberHandle, createNumberHandleLine, createStringHandle, save, insertPoint, varBox, keyBox, switchVarTyp,
   isUpdate = false,
   detectedVars = [],
   idParts = {'insert'     : '_insert',
              'vari'       : '_var',
              'comment'    : '_kommentar',
              'handletyp'  : '_handletyp',
              'typidname'  : '_typ',
              'operator'   : '_operator',
              'wert'       : '_wert',
              'handle'     : '_handle',
              'status'     : '_statuscode',
              'update'     : '_update',
              'belongsto'  : '_id',
              'vars'       : '_vars',
              'keywords'   : '_keywords',
              'stream'     : '_streaming',
              'statusid'   : '_statusid'};

   varBox = $(boxId + idParts.vars),
   keyBox = $(boxId + idParts.keywords),
   insertPoint = $(boxId + idParts.insert);

   $(boxId).onkeyup = function()
   {
      syntaxParsing(this.value);
   }

   insertPoint.onclick = function()
   {
      save();
   }

   this.setUpdate = function()
   {
      isUpdate = true;
   }

   syntaxParsing = function(value)
   {
      var vAnz, vars, keys, i, hist;

      if(value.match(/([a-zA-Z0-9\_]+)/g).length > 0)
      {
         vars = value.match(/([a-zA-Z0-9\_]+)/g);
         hist = new Object();

         for(i = 0; i < detectedVars.length; i++)
         {
            hist[detectedVars[i]] = 0;
         }

         for(i = 0; i < vars.length; i++)
         {
            if($(boxId + idParts.vari + '_' + vars[i]) == null)
            {
               createVarField(boxId + idParts.vari + '_' + vars[i], vars[i]);
               hist[vars[i]] = 0;
            }

            hist[vars[i]] = 1;
         }

         detectedVars = [];

         for(i in hist)
         {
            if(hist[i] == 0)
            {
               remove($(boxId + idParts.vari + '_' + i));
            }
            else
            {
               detectedVars[detectedVars.length] = i;
            }
         }
      }
      else if(0 < detectedVars.length)
      {
         while(0 < detectedVars.length)
         {
            remove($(boxId + idParts.vari + '_' + detectedVars.pop()));
         }

         handleList = new Object();
         handleAnz = new Object();
      }
   }

   createVarField = function(id, name)
   {
      var p, l, i;

      p = cr('div');
      p.id = id;
      p.className = 'var';

      l = cr('label');
      l.appendChild(document.createTextNode('name of variable:'));
      p.appendChild(l);

      i = cr('input');
      i.type = 'text';
      i.value = name;
      i.disabled = 'disabled';
      p.appendChild(i);

      p.appendChild(cr('br'));

      l = cr('label');
      l.appendChild(document.createTextNode('variable description:'));
      p.appendChild(l);

      i = cr('input');
      i.type = 'text';
      i.id = id + idParts.comment;
      p.appendChild(i);

      p.appendChild(cr('br'));

      i = cr('input');
      i.type = 'hidden';
      i.id = id + idParts.handletyp;
      i.value = '1';
      p.appendChild(i);

      l = cr('label');
      l.appendChild(document.createTextNode('type of value:'));
      p.appendChild(l);

      l = cr('p');

      i = cr('input');
      i.type = 'radio';
      i.name = id + idParts.typidname;
      i.id = id + idParts.handletyp + 1;
      i.value = '1';
      i.checked = true;

      l.appendChild(i);
      l.appendChild(document.createTextNode('number (integer or float)'));

      i = cr('input');
      i.type = 'radio';
      i.name = id + idParts.typidname;
      i.id = id + idParts.handletyp + 2;
      i.value = '2'

      l.appendChild(i);
      l.appendChild(document.createTextNode('string'));

      p.appendChild(l);

      insertPoint.parentNode.insertBefore(p, insertPoint);
   }

   this.setComment = function(name, value)
   {
      $(boxId + idParts.vari + '_' + name + idParts.comment).value = value;
   }

   save = function()
   {
      var req, i, j, k, cvar;

      req = new request('/statusmeldung');
      req.setParam('id', $(boxId + idParts.belongsto).value);

      if(!isUpdate)
      {
         req.setParam('aktion', 'anlegen');
         req.setParam('statuscode', $(boxId + idParts.status).value);
         req.setParam('kommentar', $(boxId + idParts.comment).value);
      }
      else
      {
         req.setParam('aktion', 'update');
         req.setParam('statusid', $(boxId + idParts.statusid).value);
      }

      for(i = 0; i < detectedVars.length; i++)
      {
         cvar = boxId + idParts.vari + '_' + detectedVars[i];

         req.setParam('var[' + i + '][0]', detectedVars[i]);
         req.setParam('var[' + i + '][1]', $(cvar + idParts.comment).value);
         req.setParam('var[' + i + '][2]', $(cvar + idParts.handletyp).value);
      }

      req.setFunction(function()
      {
         location.reload();
      });

      req.send();
   }
}

function deleteStatusMessage(id, sid)
{
   if(confirm('Achtung! Sie sind im Begriff, diesen Statuscode, sowie die zugehörigen Variablen, zu löschen. Eventuell angegebene Handles bleiben erhalten. Ob dieser Statuscode noch von einem Sensor verwendet wird, können Sie im Fehler-Log einsehen.\nStatuscode löschen?'))
   {
      var req = new request('/statusmeldung');
      req.setParam('aktion', 'loeschen');
      req.setParam('id', id);
      req.setParam('statusid', sid);
      req.setFunction(function()
      {
         location.reload();
      })
      req.send();
   }
}

function editStatusMessage(id, sid)
{
   var req = new request('/statusmeldung');
   req.setParam('aktion', 'editieren');
   req.setParam('id', id);
   req.setParam('statusid', sid);
   req.setFunction(function()
   {
      var fs,
      res = req.res();
      l = new layer('sclayerbg', 'sclayer');
      l.afterComplete(function()
      {
         var i;

         gEditStatusMessage = new statusMessage('meldung_edit');
         gEditStatusMessage.setUpdate();
         $('meldung_edit').onkeyup();

         for(i = 0; i < res[1].length; i++)
         {
            gEditStatusMessage.setComment(res[1][i][0], res[1][i][1]);
            gEditStatusMessage.switchVarType(res[1][i][0], !res[1][i][2] ? 2 : 1, res[1][i][2]);
         }

      });
      l.init();

      fs = cr('fieldset');
      fs.className = 'hl';
      fs.innerHTML = res[0];

      l.setWH(873, 300);
      l.setDomTree(fs);
   })
   req.send();
}

function addStreamLine(id, name)
{
   var d, s, a, i, j,
   akt = document.getElementsByName(name),
   insertPoint = $(id);

   if(akt.length < gSizes)
   {
      d = cr('div');
      d.appendChild(cr('br'));
      d.appendChild(cr('label'));

      s = cr('select');
      s.name = name;

      for(i in gStreams)
      {
         for(j = 0; j < akt.length; j++)
         {
            if(gStreams[i] == akt[j].value)
            {
               j = -1;
               break;
            }
         }

         if(j != -1)
         {
            s.options[s.options.length] = new Option(gStreams[i], i);
         }
      }

      d.appendChild(s);

      a = cr('a');
      a.href = '#';
      a.title = 'Typ entfernen';
      a.className = 'del';
      a.appendChild(document.createTextNode('[x]'));

      a.onclick = function()
      {
         remove(this.parentNode);
         return false;
      }

      d.appendChild(a)

      insertPoint.parentNode.insertBefore(d, insertPoint);
   }
   else
   {
      alert('Fehler: Sie können keine weiteren Streaming-Accounts hinzufügen');
   }
}
