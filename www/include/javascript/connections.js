/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
function ConnectionManagement(boxId)
{
   var deleteConnection, createMicroblogBox, createSMSBox, setType, editId, sample,
   updateData = new Object(),
   currentType = 0,
   types = ['microblog_stream', 'sms_gateway'],
   idParts = {'name' : '_name',
              'maxlength' : '_maxlength',
              'sms_gateway' : '_sms_gateway',
              'sms_url' : '_sms_url',
              'stream_baseurl' : '_stream_base',
              'stream_apibaseurl' : '_stream_apibase',
              'stream_authurl' : '_stream_authurl',
              'stream_requesturl' : '_stream_requesturl',
              'stream_accessurl' : '_stream_accessurl',
              'stream_searchurl' : '_stream_searchurl',
              'accountType' : '_accounttype',
              'options' : '_options'};

   this.setData = function(data, type)
   {
      currentType = type;
      updateData = data;
   }

   this.setEditId = function(id)
   {
      editId = id;
   }

   this.setSample = function(s)
   {
      sample = s;
   }

   setType = function(type)
   {
      $(boxId + idParts.options + '_' + currentType).style.display = 'none';
      $(boxId + idParts.options + '_' + type).style.display = 'block';
      $(boxId + idParts.accountType + '_' + type).checked = true;
      currentType = type;
   }

   this.init = function()
   {
      var fs, tmp;

      fs = cr('fieldset');
      fs.className = 'hl';

      tmp = cr('legend');
      tmp.appendChild(document.createTextNode(typeof updateData.name == 'undefined' ? 'add another connection' : 'edit a connection'));
      fs.appendChild(tmp);

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('connection name:'));
      fs.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'text';
      tmp.id = boxId + idParts.name;

      if(typeof updateData.name != 'undefined')
      {
         tmp.value = updateData.name;
      }

      fs.appendChild(tmp);

      fs.appendChild(cr('br'));

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('message maxlength:'));
      fs.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'text';
      tmp.id = boxId + idParts.maxlength;

      if(typeof updateData.maxlength != 'undefined')
      {
         tmp.value = updateData.maxlength;
      }

      fs.appendChild(tmp);
      fs.appendChild(document.createTextNode(' (supported maxlength of messages)'));

      fs.appendChild(cr('br'));

      if(typeof updateData.name == 'undefined')
      {
         tmp = cr('label');
         tmp.appendChild(document.createTextNode('connection type:'));
         fs.appendChild(tmp);

         tmp = cr('input');
         tmp.type = 'radio';
         tmp.name = 'stype';
         tmp.id = boxId + idParts.accountType + '_0';

         tmp.onclick = function()
         {
            if(this.checked)
            {
               setType(0);
            }
         }

         fs.appendChild(tmp);
         fs.appendChild(document.createTextNode('microblog'));

         tmp = cr('input');
         tmp.type = 'radio';
         tmp.name = 'stype';
         tmp.style.marginLeft = '15px';
         tmp.id = boxId + idParts.accountType + '_1';

         tmp.onclick = function()
         {
            if(this.checked)
            {
               setType(1);
            }
         }

         fs.appendChild(tmp);
         fs.appendChild(document.createTextNode('sms'));
      }

      if(typeof updateData.name == 'undefined' || typeof updateData.stream != 'undefined')
      {
         fs.appendChild(createMicroblogBox(typeof updateData.name == 'undefined' ? true : false));
      }

      if(typeof updateData.name == 'undefined' || typeof updateData.sms != 'undefined')
      {
         fs.appendChild(createSMSBox(typeof updateData.name == 'undefined' ? true : false));
      }

      if(typeof updateData.deletable != 'undefined')
      {
         tmp = cr('a');
         tmp.className = 'deleteaccount';
         tmp.href = '#';

         tmp.onclick = function()
         {
            deleteConnection();
            return false;
         }

         tmp.appendChild(document.createTextNode('delete this connection'));
         fs.appendChild(tmp);
      }

      tmp = cr('input');
      tmp.type = 'submit';
      tmp.className = 'submit';
      tmp.value = typeof updateData.name == 'undefined' ? 'create connection' : 'save changes';

      tmp.onclick = function()
      {
         createConnection();
         return false;
      }

      fs.appendChild(tmp);
      $(boxId).appendChild(fs);

      if(typeof updateData.name == 'undefined')
      {
         setType(currentType);
      }
   }

   createMicroblogBox = function(hide)
   {
      var cBox, tmp;

      cBox = cr('div');
      cBox.className = 'loginp';

      if(hide)
      {
         cBox.style.display = 'none';
      }

      cBox.id = boxId + idParts.options + '_0';

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('base url:'));
      cBox.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'text';
      tmp.id = boxId + idParts.stream_baseurl;

      if(typeof updateData.stream != 'undefined')
      {
         tmp.value = updateData.stream.baseurl;
      }

      cBox.appendChild(tmp);
      cBox.appendChild(document.createTextNode(' e.g. ' + sample.baseurl));
      cBox.appendChild(cr('br'));

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('api base url:'));
      cBox.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'text';
      tmp.id = boxId + idParts.stream_apibaseurl;

      if(typeof updateData.stream != 'undefined')
      {
         tmp.value = updateData.stream.apibase;
      }

      cBox.appendChild(tmp);
      cBox.appendChild(document.createTextNode(' e.g. ' + sample.apibase));
      cBox.appendChild(cr('br'));

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('authorization url:'));
      cBox.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'text';
      tmp.id = boxId + idParts.stream_authurl;

      if(typeof updateData.stream != 'undefined')
      {
         tmp.value = updateData.stream.authurl;
      }

      cBox.appendChild(tmp);
      cBox.appendChild(document.createTextNode(' e.g. ' + sample.authurl));
      cBox.appendChild(cr('br'));

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('request token url:'));
      cBox.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'text';
      tmp.id = boxId + idParts.stream_requesturl;

      if(typeof updateData.stream != 'undefined')
      {
         tmp.value = updateData.stream.requesturl;
      }

      cBox.appendChild(tmp);
      cBox.appendChild(document.createTextNode(' e.g. ' + sample.requesturl));
      cBox.appendChild(cr('br'));

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('access token url:'));
      cBox.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'text';
      tmp.id = boxId + idParts.stream_accessurl;

      if(typeof updateData.stream != 'undefined')
      {
         tmp.value = updateData.stream.accessurl;
      }

      cBox.appendChild(tmp);
      cBox.appendChild(document.createTextNode(' e.g. ' + sample.accessurl));
      cBox.appendChild(cr('br'));

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('search url:'));
      cBox.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'text';
      tmp.id = boxId + idParts.stream_searchurl;

      if(typeof updateData.stream != 'undefined')
      {
         tmp.value = updateData.stream.searchurl;
      }

      cBox.appendChild(tmp);
      cBox.appendChild(document.createTextNode(' e.g. ' + sample.searchurl));

      return cBox;
   }

   createSMSBox = function(hide)
   {
      var cBox, tmp;

      cBox = cr('div');
      cBox.className = 'loginp';

      if(hide)
      {
         cBox.style.display = 'none';
      }

      cBox.id = boxId + idParts.options + '_1';

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('gateway url:'));
      cBox.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'text';
      tmp.id = boxId + idParts.sms_url;

      if(typeof updateData.sms != 'undefined')
      {
         tmp.value = updateData.sms;
      }

      cBox.appendChild(tmp);
      cBox.appendChild(document.createTextNode(' variables: [dest_number], [message]'));

      return cBox;
   }

   createConnection = function()
   {
      var req = new request('/connections');

      if(typeof editId == 'undefined')
      {
         req.setParam('aktion', 'create_' + types[currentType]);
      }
      else
      {
         req.setParam('aktion', 'update_' + types[currentType]);
         req.setParam('id', editId);
      }

      req.setParam('name', $(boxId + idParts.name).value);
      req.setParam('maxlength', $(boxId + idParts.maxlength).value);

      switch(parseInt(currentType))
      {
         case 0:
            req.setParam('baseurl', $(boxId + idParts.stream_baseurl).value);
            req.setParam('apibase', $(boxId + idParts.stream_apibaseurl).value);
            req.setParam('authurl', $(boxId + idParts.stream_authurl).value);
            req.setParam('requesturl', $(boxId + idParts.stream_requesturl).value);
            req.setParam('accessurl', $(boxId + idParts.stream_accessurl).value);
            req.setParam('searchurl', $(boxId + idParts.stream_searchurl).value);
            break;

         case 1:
            req.setParam('gateway', $(boxId + idParts.sms_url).value);
            break;
      }

      req.setFunction(function()
      {
         location.reload();
      });
      req.send();

      return false;
   }

   deleteConnection = function()
   {
      if(confirm('are you sure to delete this connection?'))
      {
         var req = new request('/connections');
         req.setParam('aktion', 'delete_' + types[currentType]);
         req.setParam('id', editId);
         req.setFunction(function()
         {
            location.reload();
         });
         req.send();
      }
   }
}

function editConnection(type, id)
{
   var req = new request('/connections');
   req.setParam('aktion', 'edit');
   req.setParam('type', type);
   req.setParam('id', id);

   req.setFunction(function()
   {
      var fs,
      res = req.res();

      l = new layer('sclayerbg', 'sclayer');
      l.afterComplete(function()
      {
         var edit, i;

         edit = new ConnectionManagement('connection_edit');
         edit.setData(res, type);
         edit.setEditId(id);
         edit.setSample(gSample);
         edit.init();
      });
      l.init();

      fs = cr('form');
      fs.id = 'connection_edit';

      l.setDomTree(fs);
      l.setWH(873, 150);
   });

   req.send();
}
