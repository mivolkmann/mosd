/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
function AccountManagement(boxId)
{
   var createAccount, mbType, setMicroblogType, microblogs, gateways, getOAuthURL, verifySecureCode, createSMSBox, createMicroblogBox, createEmailBox, setType, editId, deleteAccount,
   updateData = new Object(),
   currentType = 0,
   types = ['email', 'microblog', 'sms'],
   idParts = {'name' : '_name',
              'mail' : '_mail',
              'stream_type' : '_stream_type',
              'stream_costumerkey' : '_stream_costumerkey',
              'stream_costumersecret' : '_stream_costumersecret',
              'stream_options_token' : '_stream_options_token',
              'stream_options_url' : '_stream_options_url',
              'stream_options_switch' : '_stream_options_switch',
              'stream_options_mbtype' : '_stream_options_mbtype',
              'stream_oauth_token' : '_stream_oauth_token',
              'stream_oauth_token_secret' : '_stream_oauth_token_secret',
              'hidden' : '_hidden',
              'stream_authurl' : '_stream_authurl',
              'stream_authcode' : '_stream_authcode',
              'options' : '_options',
              'sms_type' : '_sms_type',
              'sms_number' : '_sms_number',
              'accountType' : '_accounttype'};

   this.setMicroblogs = function(mb)
   {
      microblogs = mb;
   }

   this.setGateways = function(gw)
   {
      gateways = gw;
   }

   this.setData = function(data, type)
   {
      currentType = type;
      updateData = data;
   }

   this.setEditId = function(id)
   {
      editId = id;
   }

   setType = function(type)
   {
      $(boxId + idParts.options + '_' + currentType).style.display = 'none';
      $(boxId + idParts.options + '_' + type).style.display = 'block';
      $(boxId + idParts.accountType + '_' + type).checked = true;
      currentType = type;
   }

   setMicroblogType = function(type)
   {
      $(boxId + idParts.stream_options_token).style.display = type == 0 ? 'none' : 'block';
      $(boxId + idParts.stream_options_url).style.display = type == 1 ? 'none' : 'block';
      $(boxId + idParts.stream_options_mbtype + '_' + type).checked = true;
      mbType = type;
   }

   getOAuthURL = function()
   {
      var req = new request('/accounts');
      req.setParam('aktion', 'get_oauth');
      req.setParam('streamurlid', $(boxId + idParts.stream_type).value);
      req.setParam('costumerkey', $(boxId + idParts.stream_costumerkey).value);
      req.setParam('costumersecret', $(boxId + idParts.stream_costumersecret).value);
      req.setFunction(function()
      {
         var res = req.res(),
         a = cr('a');
         a.href = res[2];
         a.target = '_blank';
         a.appendChild(document.createTextNode('click here to allow access to your account'));

         removeChilds($(boxId + idParts.stream_authurl));
         $(boxId + idParts.stream_authurl).appendChild(a);

         $(boxId + idParts.stream_oauth_token + idParts.hidden).value = res[0];
         $(boxId + idParts.stream_oauth_token_secret + idParts.hidden).value = res[1];
         window.open(res[2]);
      });
      req.send();
   }

   verifySecureCode = function()
   {
      var req = new request('/accounts');
      req.setParam('aktion', 'verify_securecode');
      req.setParam('streamurlid', $(boxId + idParts.stream_type).value);
      req.setParam('costumerkey', $(boxId + idParts.stream_costumerkey).value);
      req.setParam('costumersecret', $(boxId + idParts.stream_costumersecret).value);
      req.setParam('oauth_token', $(boxId + idParts.stream_oauth_token + idParts.hidden).value);
      req.setParam('oauth_token_secret', $(boxId + idParts.stream_oauth_token_secret + idParts.hidden).value);
      req.setParam('securecode', $(boxId + idParts.stream_authcode).value);
      req.setFunction(function()
      {
         var res = req.res();
         $(boxId + idParts.stream_oauth_token).value = res[0];
         $(boxId + idParts.stream_oauth_token_secret).value = res[1];

         setMicroblogType(1);

         remove($(boxId + idParts.stream_options_url));
         remove($(boxId + idParts.stream_options_switch));

         alert('you account was verified successfully - now you can create your account');
      });
      req.send();
   }

   this.init = function()
   {
      var fs, tmp;

      fs = cr('fieldset');
      fs.className = 'hl';

      tmp = cr('legend');
      tmp.appendChild(document.createTextNode(typeof updateData.name == 'undefined' ? 'add a notification account' : 'edit a notification account'));
      fs.appendChild(tmp);

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('account name:'));
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

      if(typeof updateData.name == 'undefined')
      {
         tmp = cr('label');
         tmp.appendChild(document.createTextNode('notification type:'));
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
         fs.appendChild(document.createTextNode('email'));

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
         fs.appendChild(document.createTextNode('microblog'));

         tmp = cr('input');
         tmp.type = 'radio';
         tmp.name = 'stype';
         tmp.style.marginLeft = '15px';
         tmp.id = boxId + idParts.accountType + '_2';

         tmp.onclick = function()
         {
            if(this.checked)
            {
               setType(2);
            }
         }

         fs.appendChild(tmp);
         fs.appendChild(document.createTextNode('sms'));
      }

      if(typeof updateData.name == 'undefined' || typeof updateData.email != 'undefined')
      {
         fs.appendChild(createEmailBox(typeof updateData.name == 'undefined' ? true : false));
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
            deleteAccount();
            return false;
         }

         tmp.appendChild(document.createTextNode('delete this account'));
         fs.appendChild(tmp);
      }

      tmp = cr('input');
      tmp.type = 'submit';
      tmp.className = 'submit';
      tmp.value = typeof updateData.name == 'undefined' ? 'create account' : 'save changes';

      tmp.onclick = function()
      {
         createAccount();
         return false;
      }

      fs.appendChild(tmp);

      $(boxId).appendChild(fs);

      if(typeof updateData.name == 'undefined')
      {
         setType(currentType);
      }

      if(typeof updateData.name == 'undefined' || typeof updateData.stream != 'undefined')
      {
         setMicroblogType(1);
      }
   }

   createEmailBox = function(hide)
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
      tmp.appendChild(document.createTextNode('email:'));
      cBox.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'text';
      tmp.id = boxId + idParts.email;

      if(typeof updateData.email != 'undefined')
      {
         tmp.value = updateData.email;
      }

      cBox.appendChild(tmp);

      return cBox;
   }

   createMicroblogBox = function(hide)
   {
      var cBox, tmp, tmp2, tmp3, i;

      cBox = cr('div');
      cBox.className = 'loginp';

      if(hide)
      {
         cBox.style.display = 'none';
      }

      cBox.id = boxId + idParts.options + '_1';

      tmp = cr('div');
      tmp.style.padding = '3px 0px';
      tmp.id = boxId + idParts.stream_options_switch;

      tmp2 = cr('input');
      tmp2.type = 'radio';
      tmp2.name = 'ctype';
      tmp2.id = boxId + idParts.stream_options_mbtype + '_0';
      tmp2.style.marginLeft = '10px';

      tmp2.onclick = function()
      {
         if(this.checked)
         {
            setMicroblogType(0);
         }
      }

      tmp.appendChild(tmp2);
      tmp.appendChild(document.createTextNode('i don\t know my access tokens'));

      tmp2 = cr('input');
      tmp2.type = 'radio';
      tmp2.name = 'ctype';
      tmp2.id = boxId + idParts.stream_options_mbtype + '_1';
      tmp2.style.marginLeft = '20px';

      tmp2.onclick = function()
      {
         if(this.checked)
         {
            setMicroblogType(1);
         }
      }

      tmp.appendChild(tmp2);
      tmp.appendChild(document.createTextNode('i know my access tokens'));

      cBox.appendChild(tmp);

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('connection type:'));
      cBox.appendChild(tmp);

      tmp = cr('select');
      tmp.id = boxId + idParts.stream_type;
      tmp.options[0] = new Option('[select a connection]');

      for(i in microblogs)
      {
         tmp.options[tmp.options.length] = new Option(microblogs[i], i, typeof updateData.stream != 'undefined' && updateData.stream.streamurlid == i ? true : false);
      }

      cBox.appendChild(tmp);
      cBox.appendChild(cr('br'));

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('costumer key:'));
      cBox.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'text';
      tmp.id = boxId + idParts.stream_costumerkey;

      if(typeof updateData.stream != 'undefined')
      {
         tmp.value = updateData.stream.costumerkey;
      }

      cBox.appendChild(tmp);
      cBox.appendChild(cr('br'));

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('costumer secret:'));
      cBox.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'text';
      tmp.id = boxId + idParts.stream_costumersecret;

      if(typeof updateData.stream != 'undefined')
      {
         tmp.value = updateData.stream.costumersecret;
      }

      cBox.appendChild(tmp);

      tmp = cr('div');
      tmp.id =  boxId + idParts.stream_options_token;
      tmp.style.display = 'none';

      tmp2 = cr('label');
      tmp2.appendChild(document.createTextNode('access token:'));
      tmp.appendChild(tmp2);

      tmp2 = cr('input');
      tmp2.type = 'text';
      tmp2.id = boxId + idParts.stream_oauth_token;

      if(typeof updateData.stream != 'undefined')
      {
         tmp2.value = updateData.stream.oauth_token;
      }

      tmp.appendChild(tmp2);
      tmp.appendChild(cr('br'));

      tmp2 = cr('label');
      tmp2.appendChild(document.createTextNode('access token secret:'));
      tmp.appendChild(tmp2);

      tmp2 = cr('input');
      tmp2.type = 'text';
      tmp2.id = boxId + idParts.stream_oauth_token_secret;

      if(typeof updateData.stream != 'undefined')
      {
         tmp2.value = updateData.stream.oauth_token_secret;
      }

      tmp.appendChild(tmp2);

      cBox.appendChild(tmp);

      tmp = cr('div');
      tmp.id =  boxId + idParts.stream_options_url;
      tmp.style.display = 'none';

      tmp2 = cr('label');
      tmp2.appendChild(document.createTextNode('authorization url:'));
      tmp.appendChild(tmp2);

      tmp2 = cr('span');
      tmp2.id = boxId + idParts.stream_authurl;

      tmp3 = cr('a');
      tmp3.href = '#';

      tmp3.onclick = function()
      {
         getOAuthURL();
         return false;
      }

      tmp3.appendChild(document.createTextNode('click here to get your authorization url'));
      tmp2.appendChild(tmp3);
      tmp.appendChild(tmp2);

      tmp2 = cr('input');
      tmp2.type = 'hidden';
      tmp2.id = boxId + idParts.stream_oauth_token + idParts.hidden;
      tmp.appendChild(tmp2);

      tmp2 = cr('input');
      tmp2.type = 'hidden';
      tmp2.id = boxId + idParts.stream_oauth_token_secret + idParts.hidden;
      tmp.appendChild(tmp2);

      tmp.appendChild(cr('br'));

      tmp2 = cr('label');
      tmp2.appendChild(document.createTextNode('secure code:'));
      tmp.appendChild(tmp2);

      tmp2 = cr('input');
      tmp2.type = 'text';
      tmp2.id = boxId + idParts.stream_authcode;
      tmp.appendChild(tmp2);

      tmp.appendChild(document.createTextNode(' (optional '));

      tmp2 = cr('a');
      tmp2.href = '#';

      tmp2.onclick = function()
      {
         verifySecureCode();
         return false;
      }

      tmp2.appendChild(document.createTextNode('verify secure code'));
      tmp.appendChild(tmp2);
      tmp.appendChild(document.createTextNode(')'));

      cBox.appendChild(tmp);

      return cBox;
   }

   createSMSBox = function(hide)
   {
      var cBox, tmp, i;

      cBox = cr('div');
      cBox.className = 'loginp';

      if(hide)
      {
         cBox.style.display = 'none';
      }

      cBox.id = boxId + idParts.options + '_2';

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('gateway:'));
      cBox.appendChild(tmp);

      tmp = cr('select');
      tmp.id = boxId + idParts.sms_type;
      tmp.options[0] = new Option('[select a gateway]');

      for(i in gateways)
      {
         tmp.options[tmp.options.length] = new Option(gateways[i], i, typeof updateData.sms != 'undefined' && updateData.sms.gatewayid == i ? true : false);
      }

      cBox.appendChild(tmp);
      cBox.appendChild(cr('br'));

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('mobile number:'));
      cBox.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'text';
      tmp.id = boxId + idParts.sms_number;

      if(typeof updateData.sms != 'undefined')
      {
         tmp.value = updateData.sms.number;
      }

      cBox.appendChild(tmp);

      return cBox;
   }

   createAccount = function()
   {
      var req = new request('/accounts');

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

      switch(parseInt(currentType))
      {
         case 0:
            req.setParam('mail', $(boxId + idParts.email).value);
            break;

         case 1:
            req.setParam('streamurlid', $(boxId + idParts.stream_type).value);
            req.setParam('costumerkey', $(boxId + idParts.stream_costumerkey).value);
            req.setParam('costumersecret', $(boxId + idParts.stream_costumersecret).value);
            req.setParam('oauth_token', $(boxId + idParts.stream_oauth_token).value);
            req.setParam('oauth_token_secret', $(boxId + idParts.stream_oauth_token_secret).value);
            break;

         case 2:
            req.setParam('gatewayid', $(boxId + idParts.sms_type).value);
            req.setParam('number', $(boxId + idParts.sms_number).value);
            break;
      }

      req.setFunction(function()
      {
         location.reload();
      });
      req.send();

      return false;
   }

   deleteAccount = function()
   {
      if(confirm('are you sure to delete this account?'))
      {
         var req = new request('/accounts');
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

function editAccount(type, id)
{
   var req = new request('/accounts');
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

         edit = new AccountManagement('account_edit');
         edit.setMicroblogs(gStreams);
         edit.setGateways(gGateways);
         edit.setData(res, type);
         edit.setEditId(id);
         edit.init();
      });
      l.init();

      fs = cr('form');
      fs.id = 'account_edit';

      l.setDomTree(fs);
      l.setWH(873, 150);
   });

   req.send();
}
