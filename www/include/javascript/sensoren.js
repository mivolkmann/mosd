/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
function SensorData(form)
{
   var createVarField, syntaxParsing, save, switchVarType, editId, updateData,
   detectedVars = [],
   idParts = {'insert'     : '_insert',
              'vari'       : '_var',
              'handletyp'  : '_handletyp',
              'sensorid'   : '_sensorid',
              'sensorname' : '_sensorname',
              'beschreibung' : '_beschreibung',
              'intervall' : '_intervall',
              'maxage' : '_maxage',
              'variablen' : '_variablen'};

   this.setSensorData = function(id, data)
   {
      editId = id;
      updateData = data;
   }

   syntaxParsing = function(value)
   {
      var vAnz, vars, keys, i, hist;

      if(value.match(/([a-zA-Z0-9\_]+)/g) != null)
      {
         vars = value.match(/([a-zA-Z0-9\_]+)/g);
         hist = new Object();

         for(i = 0; i < detectedVars.length; i++)
         {
            hist[detectedVars[i]] = 0;
         }

         for(i = 0; i < vars.length; i++)
         {
            if($(form + idParts.vari + '_' + vars[i]) == null)
            {
               createVarField(form + idParts.vari + '_' + vars[i], vars[i]);
               hist[vars[i]] = 0;
            }

            hist[vars[i]] = 1;
         }

         detectedVars = [];

         for(i in hist)
         {
            if(hist[i] == 0)
            {
               remove($(form + idParts.vari + '_' + i));
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
            remove($(form + idParts.vari + '_' + detectedVars.pop()));
         }
      }
   }

   this.init = function()
   {
      var fs, tmp;

      fs = cr('fieldset');
      fs.className = 'hl';

      tmp = cr('legend');
      tmp.appendChild(document.createTextNode(typeof updateData == 'undefined' ? 'create a new device' : 'update an existing device'));
      fs.appendChild(tmp);

      tmp = cr('label');
      tmp.for = form + idParts.sensorname;
      tmp.appendChild(document.createTextNode('device name:'));
      fs.appendChild(tmp);

      tmp = cr('input');
      tmp.id = form + idParts.sensorname;
      tmp.type = 'text';

      if(typeof updateData != 'undefined')
      {
         tmp.value = updateData.name;
      }

      fs.appendChild(tmp);

      fs.appendChild(cr('br'));

      tmp = cr('label');
      tmp.for = form + idParts.beschreibung;
      tmp.appendChild(document.createTextNode('device description:'));
      fs.appendChild(tmp);

      tmp = cr('input');
      tmp.id = form + idParts.beschreibung;
      tmp.type = 'text';

      if(typeof updateData != 'undefined')
      {
         tmp.value = updateData.description;
      }

      fs.appendChild(tmp);

      fs.appendChild(cr('br'));

      tmp = cr('label');
      tmp.for = form + idParts.intervall;
      tmp.appendChild(document.createTextNode('connection interval:'));
      fs.appendChild(tmp);

      tmp = cr('input');
      tmp.id = form + idParts.intervall;
      tmp.type = 'text';

      if(typeof updateData != 'undefined')
      {
         tmp.value = updateData.interval;
      }

      fs.appendChild(tmp);

      fs.appendChild(document.createTextNode(' (in seconds between 30 and 86400 (1 day)))'));
      fs.appendChild(cr('br'));

      tmp = cr('label');
      tmp.for = form + idParts.intervall;
      tmp.appendChild(document.createTextNode('device data max age:'));
      fs.appendChild(tmp);

      tmp = cr('input');
      tmp.id = form + idParts.maxage;
      tmp.type = 'text';

      if(typeof updateData != 'undefined')
      {
         tmp.value = updateData.maxage;
      }

      fs.appendChild(tmp);

      fs.appendChild(document.createTextNode(' (in hours between 24 and 8760 (1 year))'));
      fs.appendChild(cr('br'));

      tmp = cr('label');
      tmp.for = form + idParts.variablen;
      tmp.appendChild(document.createTextNode('variables:'));
      fs.appendChild(tmp);

      tmp = cr('input');
      tmp.id = form + idParts.variablen;
      tmp.type = 'text';
      tmp.style.width = '400px';

      if(typeof updateData != 'undefined')
      {
         tmp.value = updateData.var;
      }

      tmp.onkeyup = function()
      {
         syntaxParsing(this.value);
      }

      fs.appendChild(tmp);

      fs.appendChild(document.createTextNode(' (seperate by space)'));

      if(typeof updateData != 'undefined' && typeof updateData.deletable != 'undefined')
      {
         tmp = cr('a');
         tmp.className = 'deleteaccount';
         tmp.href = '#';
         tmp.id = form + idParts.insert;

         tmp.onclick = function()
         {
            deleteSensor();
            return false;
         }

         tmp.appendChild(document.createTextNode('delete this device'));
         fs.appendChild(tmp);
      }

      tmp = cr('input');
      tmp.type = 'submit';

      if(typeof updateData == 'undefined' || typeof updateData.deletable == 'undefined')
      {
         tmp.id = form + idParts.insert;
      }

      tmp.className = 'submit';
      tmp.value = typeof updateData == 'undefined' ? 'create device' : 'update device';

      tmp.onclick = function()
      {
         save();
         return false;
      }

      fs.appendChild(tmp);

      $(form).appendChild(fs);

      if(typeof updateData != 'undefined')
      {
         $(form + idParts.variablen).onkeyup();

         for(i in updateData.vari)
         {
            $(form + idParts.vari + '_' + updateData.vari[i].name + idParts.beschreibung).value = updateData.vari[i].description;
            switchVarType(updateData.vari[i].type, form + idParts.vari + '_' + updateData.vari[i].name);
         }
      }
   }

   switchVarType = function(type, id)
   {
      $(id + idParts.handletyp).value = type;
      $(id + idParts.handletyp + type).checked = true;
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
      i.id = id + idParts.beschreibung;
      p.appendChild(i);

      p.appendChild(cr('br'));

      i = cr('input');
      i.type = 'hidden';
      i.value = '1';
      i.id = id + idParts.handletyp;
      p.appendChild(i);

      l = cr('label');
      l.appendChild(document.createTextNode('type of value:'));
      p.appendChild(l);

      i = cr('input');
      i.type = 'radio';
      i.name = id + idParts.typid + '_' + name;
      i.id = id + idParts.handletyp + 1;
      i.checked = true;
      i.onclick = function()
      {
         switchVarType(1, id);
      }

      p.appendChild(i);
      p.appendChild(document.createTextNode(' number (integer or float) '));

      i = cr('input');
      i.type = 'radio';
      i.style.marginLeft = '15px';
      i.name = id + idParts.typid + '_' + name;
      i.id = id + idParts.handletyp + 2;
      i.onclick = function()
      {
         switchVarType(2, id);
      }

      p.appendChild(i);
      p.appendChild(document.createTextNode(' string'));

      $(form + idParts.insert).parentNode.insertBefore(p, $(form + idParts.insert));
   }

   save = function()
   {
      var req, i, j, k, cvar;

      req = new request('/sensoren');
      req.setParam('name', $(form + idParts.sensorname).value);
      req.setParam('beschreibung', $(form + idParts.beschreibung).value);
      req.setParam('intervall', $(form + idParts.intervall).value);
      req.setParam('maxage', $(form + idParts.maxage).value);

      if(typeof updateData == 'undefined')
      {
         req.setParam('aktion', 'create');

      }
      else
      {
         req.setParam('sensorid', editId);
         req.setParam('aktion', 'update');
      }

      for(i = 0; i < detectedVars.length; i++)
      {
         cvar = form + idParts.vari + '_' + detectedVars[i];

         req.setParam('var[' + i + '][0]', detectedVars[i]);
         req.setParam('var[' + i + '][1]', $(cvar + idParts.beschreibung).value);
         req.setParam('var[' + i + '][2]', $(cvar + idParts.handletyp).value);
      }

      req.setFunction(function()
      {
         location.reload();
      });

      req.send();
   }

   deleteSensor = function()
   {
      if(confirm('Warning!\nThis sensor will be deleted if you continue.\nContinue?'))
      {
         var req = new request('/sensoren/');
         req.setParam('sensorid', editId);
         req.setParam('aktion', 'delete');
         req.setFunction(function()
         {
            location.reload();
         });
         req.send();
      }
   }
}

function editSensor(id)
{
   var req = new request('/sensoren');
   req.setParam('aktion', 'edit');
   req.setParam('id', id);

   req.setFunction(function()
   {
      var fs,
      res = req.res();

      l = new layer('sclayerbg', 'sclayer');
      l.afterComplete(function()
      {
         var edit, i;

         edit = new SensorData('sensor_edit');
         edit.setSensorData(id, res);
         edit.init();
      });
      l.init();

      fs = cr('form');
      fs.id = 'sensor_edit';

      l.setDomTree(fs);
      l.setWH(873, 150);
   });

   req.send();
}
