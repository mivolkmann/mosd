/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
function Conditions(form)
{
   var accounts, sensorvars, buildSelect, addSelectBox, ruleCounter, messageCounter, ruleCounterAkt, messageCounterAkt, removeVariable, save, showAvailableVars,
   addMessageBox, buildMessageMenu, removeAccount, save, selectVariable, setValue, deleteCondition, editId, addConnectExpression, addConditionRow, microblogCheck,
   someSelected = false,
   usedHandles = new Object(),
   messageNotifications = new Object(),
   available = new Object(),
   messageNotificationsAkt = new Object(),
   ruleVariables = new Object(),
   idParts = {'actions' : '_actions',
              'rules' : '_rules',
              'operators' : '_operators',
              'values' : '_values',
              'select' : '_select',
              'vari' : '_variable',
              'text' : '_text',
              'condition' : '_condition',
              'messageText' : '_messagetext',
              'notText' : '_nottext',
              'accounts' : '_accounts',
              'availableVars' : 'availableVars',
              'messageBox' : '_messagebox',
              'statusType' : '_statustype',
              'operator' : '_operator',
              'grenzwert' : '_grenzwert',
              'connectoperator' : '_connectoperator',
              'lockType' : '_lockType',
              'conditionName' : '_conditionName',
              'conditionDescription' : '_conditionDescription',
              'conditionid' : '_conditionId',
              'microblogCondition' : '_microblogCondition'},
   grafiken = {'plus' : ENV.codeBase + '/img/plus.png',
               'plusOver' : ENV.codeBase + '/img/plus2.png',
               'abort' : ENV.codeBase + '/img/abort.png',
               'abortOver' : ENV.codeBase + '/img/abort2.png'};

   this.setAccounts = function(acc)
   {
      accounts = acc;
   }

   this.setEditId = function(id)
   {
      editId = id;
   }

   this.setSensorVars = function(vars)
   {
      sensorvars = vars;
   }

   this.setData = function(name, description, iscritical, dolock)
   {
      $(form + idParts.conditionName).value = name;
      $(form + idParts.conditionDescription).value = description;
      $(form + idParts.statusType).checked = iscritical == 1 ? true : false;
      $(form + idParts.lockType).checked = dolock == 1 ? true : false;
   }

   this.addSelectBox = function(sensorid, varid, operator, value)
   {
      addSelectBox(sensorid, varid, operator, value);
   }

   addSelectBox = function(sensorid, varid, operator, value)
   {
      var id, box, inp, currVal, img;
      ruleVariables[ruleCounter] = true;

      box = cr('div');
      box.className = 'condition';
      box.id = form + idParts.rules + '_' + ruleCounter;

      inp = cr('input');
      inp.type = 'hidden';
      inp.id = form + idParts.rules + '_' + ruleCounter + idParts.vari;

      box.appendChild(inp);

      currVal = cr('span');
      currVal.title = ruleCounter;
      currVal.id = form + idParts.rules + '_' + ruleCounter + idParts.text;
      currVal.appendChild(document.createTextNode('[click to select a variable]'));

      currVal.onclick = function()
      {
         var ta,
         sel = $(form + idParts.rules + '_' + this.title + idParts.select);
         sel.style.display = 'block';

         sel.onmouseout = function()
         {
            var f = this;
            ta = setTimeout(function()
            {
               f.style.display = 'none';
            }, 50);
         }

         sel.onmouseover = function()
         {
            clearTimeout(ta);
         }
      }

      box.appendChild(currVal);

      img = cr('img');
      img.src = grafiken.abort;
      img.title = 'remove this variable';
      img.alt = ruleCounter;

      img.onmouseover = function()
      {
         this.src = grafiken.abortOver;
      }

      img.onmouseout = function()
      {
         this.src = grafiken.abort;
      }

      img.onclick = function()
      {
         removeVariable(this.alt);
      }

      box.appendChild(img);
      box.appendChild(buildSelect());

      $(form + idParts.rules).appendChild(box);

      if(typeof operator != 'undefined')
      {
         selectVariable(sensorid, varid, ruleCounter, operator, value);
      }

      ruleCounter++;
      ruleCounterAkt++;
   }

   buildSelect = function()
   {
      var sen, type, vari, senli, varili, i, j,
      rC = ruleCounter;

      sen = cr('ul');
      sen.className = 'select';
      sen.id = form + idParts.rules + '_' + ruleCounter + idParts.select;

      for(i in sensorvars)
      {
         senli = cr('li');
         senli.appendChild(document.createTextNode(sensorvars[i].name));

         vari = cr('ul');

         for(j in sensorvars[i].vars)
         {
            varili = cr('li');
            varili.className = 'choose';
            varili.appendChild(document.createTextNode(sensorvars[i].vars[j].name));
            varili.title = i + '_' + j;

            varili.onclick = function()
            {
               var v = this.title.split('_');
               selectVariable(v[0], v[1], rC);
               $(form + idParts.rules + '_' + rC + idParts.select).style.display = 'none';
            }

            vari.appendChild(varili);
         }

         senli.appendChild(vari);
         sen.appendChild(senli);
      }

      return sen;
   }

   selectVariable = function(sensorid, varid, rC, operator, value)
   {
      if($(form + idParts.rules + '_' + rC + idParts.condition) != null)
      {
         available[$(form + idParts.rules + '_' + rC + idParts.vari).value.split('_')[0]]--;
         remove($(form + idParts.rules + '_' + rC + idParts.condition));
      }

      setValue(rC, sensorvars[sensorid].name + '.' + sensorvars[sensorid].vars[varid].name, sensorid, varid);

      if(!someSelected && typeof editId == 'undefined')
      {
         buildMessageMenu();
         someSelected = true;
      }

      addConditionRow(rC, sensorvars[sensorid].vars[varid].typ, operator, value)
   }

   setValue = function(rC, text, sensorid, varid)
   {
      var span = $(form + idParts.rules + '_' + rC + idParts.text),
      value = sensorid + '_' + varid;

      removeChilds(span);
      span.appendChild(document.createTextNode(text));

      if(typeof available[sensorid] == 'undefined')
      {
         available[sensorid] = 1;
      }
      else
      {
         available[sensorid]++;
      }

      $(form + idParts.rules + '_' + rC + idParts.vari).value = value;
   }

   addConditionRow = function(rC, type, operator, value)
   {
      var p, select, input;

      p = cr('p');
      p.id = form + idParts.rules + '_' + rC + idParts.condition;

      select = cr('select');
      select.id = form + idParts.rules + '_' + rC + idParts.operator;
      select.options[0] = new Option('[condition]', '');

      if(type == 1)
      {
         select.options[1] = new Option('smaller than', 0, typeof operator != 'undefined' && operator == 0 ? true : false);
         select.options[2] = new Option('equal', 1, typeof operator != 'undefined' && operator == 1 ? true : false);
         select.options[3] = new Option('bigger than', 2, typeof operator != 'undefined' && operator == 2 ? true : false);
         select.options[4] = new Option('not', 3, typeof operator != 'undefined' && operator == 3 ? true : false);
      }
      else
      {
         select.options[1] = new Option('equal', 1, typeof operator != 'undefined' && operator == 1 ? true : false);
         select.options[2] = new Option('not', 3, typeof operator != 'undefined' && operator == 3 ? true : false);
      }

      p.appendChild(select);

      input = cr('input');
      input.type = 'text';
      input.id = form + idParts.rules + '_' + rC + idParts.grenzwert;

      if(typeof value != 'undefined')
      {
         input.value = value;
      }

      p.appendChild(input);

      $(form + idParts.rules + '_' + rC).appendChild(p);
   }

   this.addConnectExpression = function(value)
   {
      addConnectExpression(value);
   }

   addConnectExpression = function(value)
   {
      var select,
      box = cr('div');
      box.className = 'operator';
      box.id = form + idParts.rules + '_' + ruleCounter + idParts.operators;

      select = cr('select');
      select.id = form + idParts.rules + '_' + ruleCounter + idParts.connectoperator;
      select.options[0] = new Option('and', 0, typeof editId != 'undefined' && value == 0 ? true : false);
      select.options[1] = new Option('or', 1, typeof editId != 'undefined' && value == 1 ? true : false)
      box.appendChild(select);

      $(form + idParts.rules).appendChild(box);
   }

   removeVariable = function(rC)
   {
      var i, num;

      if(ruleCounterAkt > 1)
      {
         num = 0;

         for(i in ruleVariables)
         {
            if(ruleVariables[i] != false)
            {
               if(i != rC)
               {
                  num++;
               }
               else
               {
                  break;
               }
            }
         }

         if(num == 0)
         {
            for(i in ruleVariables)
            {
               if(ruleVariables[i] != false && i != rC)
               {
                  break;
               }
            }

            remove($(form + idParts.rules + '_' + i + idParts.operators));
         }
         else
         {
            remove($(form + idParts.rules + '_' + rC + idParts.operators));
         }

         available[$(form + idParts.rules + '_' + rC + idParts.vari).value.split('_')[0]]--;

         remove($(form + idParts.rules + '_' + rC));
         ruleVariables[rC] = false;
         ruleCounterAkt--;
      }
      else
      {
         alert('this element is not removable, because a publish condition requires one or more conditions');
      }
   }

   this.addMessageBox = function(value, accountids, accounttypes)
   {
      addMessageBox(value, accountids, accounttypes);
   }

   addMessageBox = function(value, accountids, accounttypes)
   {
      var box, p, input, select, img, i;

      messageNotifications[messageCounter] = 0;
      messageNotificationsAkt[messageCounter] = 0;

      $(form + idParts.actions).style.display = 'block';

      box = cr('div');
      box.className = 'message';
      box.id = form + idParts.actions + '_' + messageCounter + idParts.messageBox;

      img = cr('img');
      img.src = grafiken.abort;
      img.title = 'remove this notification message';
      img.alt = messageCounter;

      img.onmouseover = function()
      {
         this.src = grafiken.abortOver;
      }

      img.onmouseout = function()
      {
         this.src = grafiken.abort;
      }

      img.onclick = function()
      {
         if(messageCounterAkt > 1)
         {
            messageCounterAkt--;
            remove($(form + idParts.actions + '_' + this.alt + idParts.messageBox));
         }
         else
         {
            alert('this element is not removable, because a publish condition requires one or morge notification messages');
         }
      }

      box.appendChild(img);

      input = cr('textarea');
      input.id = form + idParts.actions + '_' + messageCounter + idParts.messageText;
      input.name = messageCounter;

      if(typeof value != 'undefined')
      {
         input.value = value;
      }

      input.onfocus = function()
      {
         if($(idParts.availableVars) != null)
         {
            remove($(idParts.availableVars + '_' + this.name));
         }

         showAvailableVars(this, idParts.availableVars + '_' + this.name);
      }

      input.onblur = function()
      {
         var name = this.name;

         setTimeout(function()
         {
            remove($(idParts.availableVars + '_' + name));
         }, 150);
      }

      box.appendChild(input);

      p = cr('div');

      img = cr('img');
      img.src = grafiken.plus;
      img.title = 'add an account';
      img.alt = messageCounter;

      img.onmouseover = function()
      {
         this.src = grafiken.plusOver;
      }

      img.onmouseout = function()
      {
         this.src = grafiken.plus;
      }

      img.onclick = function()
      {
         addAccountSelect(this.alt, this.parentNode);
      }

      p.appendChild(img);

      if(typeof accountids == 'undefined')
      {
         addAccountSelect(messageCounter, p);
      }
      else
      {
         for(i in accountids)
         {
            addAccountSelect(messageCounter, p, accountids[i], accounttypes[i]);
         }
      }

      box.appendChild(p);

      p = cr('br');
      p.className = 'clear';
      box.appendChild(p);

      $(form + idParts.actions).appendChild(box);

      messageCounter++;
      messageCounterAkt++;
   }

   addAccountSelect = function(mC, ele, accountid, accounttype)
   {
      var box, acc, accli, span, input, i;

      box = cr('div');
      box.className = 'condition';
      box.style.width = '184px';
      box.id = form + idParts.actions + '_' + mC + '_' + messageNotifications[mC];

      input = cr('input');
      input.type = 'hidden';
      input.id = form + idParts.actions + '_' + mC + '_' + messageNotifications[mC] + idParts.accounts;

      acc = cr('span');
      acc.title = mC + '_' + messageNotifications[mC];
      acc.id = form + idParts.actions + '_' + mC + '_' + messageNotifications[mC] + idParts.notText;
      acc.appendChild(document.createTextNode('[click to select an account]'));

      acc.onclick = function()
      {
         var ta,
         sel = $(form + idParts.actions + '_' + this.title + idParts.select);
         sel.style.display = 'block';

         sel.onmouseout = function()
         {
            var f = this;
            ta = setTimeout(function()
            {
               f.style.display = 'none';
            }, 50);
         }

         sel.onmouseover = function()
         {
            clearTimeout(ta);
         }
      }

      if(typeof accountid != 'undefined')
      {
         setAccount(mC, messageNotifications[mC], accountid, accounttype, input, acc);
      }

      box.appendChild(input);
      box.appendChild(acc);

      acc = cr('ul');
      acc.className = 'select';
      acc.id = form + idParts.actions + '_' + mC + '_' + messageNotifications[mC] + idParts.select;

      for(i in accounts)
      {
         accli = cr('li');
         accli.title = i + '_' + messageNotifications[mC];
         accli.appendChild(document.createTextNode(accounts[i].name));

         accli.onclick = function()
         {
            var t = this.title.split('_');

            setAccount(mC, t[1], accounts[t[0]].id, accounts[t[0]].typ);
            $(form + idParts.actions + '_' + mC + '_' + t[1] + idParts.select).style.display = 'none';
         }

         acc.appendChild(accli);
      }

      box.appendChild(acc);

      img = cr('img');
      img.src = grafiken.abort;
      img.title = 'remove this account';
      img.alt = mC + '_' + messageNotifications[mC];

      img.onmouseover = function()
      {
         this.src = grafiken.abortOver;
      }

      img.onmouseout = function()
      {
         this.src = grafiken.abort;
      }

      img.onclick = function()
      {
         t = this.alt.split('_');
         removeAccount(t[0], t[1]);
      }

      box.appendChild(img);

      ele.insertBefore(box, ele.lastChild);

      messageNotifications[mC]++;
      messageNotificationsAkt[mC]++;
   }

   microblogCheck = function()
   {
      var i, j, tmp;

      for(i = 0; i < messageCounter; i++)
      {
         if($(form + idParts.actions + '_' + i + idParts.messageBox) != null)
         {
            for(j = 0; j < messageNotifications[i]; j++)
            {
               if($(form + idParts.actions + '_' + i + '_' + j) != null)
               {
                  tmp = $(form + idParts.actions + '_' + i + '_' + j + idParts.accounts).value.split('_');

                  if(tmp[0] == '1')
                  {
                     $(form + idParts.microblogCondition).disabled = false;
                     return true;
                  }
               }
            }
         }
      }

      $(form + idParts.microblogCondition).disabled = true;
   }

   setAccount = function(mC, mCnum, accountid, accounttype, input, ele)
   {
      var i,
      span = typeof input == 'undefined' ? $(form + idParts.actions + '_' + mC + '_' + mCnum + idParts.notText) : ele;

      for(i in accounts)
      {
         if(accounts[i].typ == accounttype && accounts[i].id == accountid)
         {
            break;
         }
      }

      removeChilds(span);
      span.appendChild(document.createTextNode(accounts[i].name));

      if(typeof input == 'undefined')
      {
         $(form + idParts.actions + '_' + mC + '_' + mCnum + idParts.accounts).value = accounttype + '_' + accountid;
         microblogCheck();
      }
      else
      {
         input.value = accounttype + '_' + accountid;

         if(accounttype == 1)
         {
            $(form + idParts.microblogCondition).disabled = false;
         }
      }
   }

   removeAccount = function(mC, mCnum)
   {
      if(messageNotificationsAkt[mC] > 1)
      {
         remove($(form + idParts.actions + '_' + mC + '_' + mCnum));
         messageNotificationsAkt[mC]--;
         microblogCheck();
      }
      else
      {
         alert('this element is not removable, because a notification message requires one or more notification accounts');
      }
   }

   showAvailableVars = function(textarea, id)
   {
      var box, tmp, span, i, t, j,
      pos = position(textarea);

      box = cr('div');
      box.className = 'avars';
      box.id = id;
      box.style.top = pos[1] + 1+ textarea.offsetHeight + 'px';
      box.style.left = pos[0] + 1 + 'px';

      for(i in available)
      {
         if(available[i] != 0)
         {
            t = i.split('_');

            for(j in sensorvars[t[0]].vars)
            {
               span = cr('span');
               span.appendChild(document.createTextNode(sensorvars[t[0]].name + '.' + sensorvars[t[0]].vars[j].name));

               span.onclick = function()
               {
                  einfuegen('[' + this.innerHTML + ']', '', textarea);
               }

               box.appendChild(span);
               box.appendChild(document.createTextNode('- ' + sensorvars[t[0]].vars[j].kommentar));
               box.appendChild(cr('br'));
            }
         }
      }

      document.body.appendChild(box);
   }

   save = function()
   {
      var req, i, j, tmp,
      tmp1 = 0,
      tmp2 = 0;

      req = new request('/conditions');

      if(typeof editId == 'undefined')
      {
         req.setParam('aktion', 'anlegen');
      }
      else
      {
         req.setParam('aktion', 'update');
         req.setParam('conditionid', editId);
      }

      req.setParam('name', $(form + idParts.conditionName).value);
      req.setParam('description', $(form + idParts.conditionDescription).value);
      req.setParam('critical', $(form + idParts.statusType).checked ? 1 : 0);
      req.setParam('dolock', $(form + idParts.lockType).checked ? 1 : 0);
      req.setParam('microblogCondition', $(form + idParts.microblogCondition).checked ? 1 : 0);

      for(i = 0; i < ruleCounter; i++)
      {
         if($(form + idParts.rules + '_' + i + idParts.vari) != null)
         {
            tmp = $(form + idParts.rules + '_' + i + idParts.vari).value.split('_');

            req.setParam('var[0][' + tmp1 + '][0]', tmp[1]);

            if($(form + idParts.rules + '_' + i + idParts.operator) != null)
            {
               req.setParam('var[0][' + tmp1 + '][1]', $(form + idParts.rules + '_' + i + idParts.operator).value);
               req.setParam('var[0][' + tmp1 + '][2]', $(form + idParts.rules + '_' + i + idParts.grenzwert).value);
            }
            else
            {
               alert('each condition variable must be set');
               return;
            }

            tmp1++;
         }

         if($(form + idParts.rules + '_' + i + idParts.connectoperator) != null)
         {
            req.setParam('connectors[' + tmp2 + ']', $(form + idParts.rules + '_' + i + idParts.connectoperator).value);
            tmp2++;
         }
      }

      tmp1 = 0;

      for(i = 0; i < messageCounter; i++)
      {
         if($(form + idParts.actions + '_' + i + idParts.messageBox) != null)
         {
            tmp2 = 0;
            req.setParam('messages[' + tmp1 + '][0]', $(form + idParts.actions + '_' + i + idParts.messageText).value);

            for(j = 0; j < messageNotifications[i]; j++)
            {
               if($(form + idParts.actions + '_' + i + '_' + j) != null)
               {
                  tmp = $(form + idParts.actions + '_' + i + '_' + j + idParts.accounts).value.split('_');

                  if(typeof tmp[1] != 'undefined')
                  {
                     req.setParam('messages[' + tmp1 + '][1][' + tmp2 + '][0]', tmp[0]);
                     req.setParam('messages[' + tmp1 + '][1][' + tmp2 + '][1]', tmp[1]);
                  }
                  else
                  {
                     alert('there is no account for some notification message given');
                     return;
                  }

                  tmp2++;
               }
            }

            tmp1++;
         }
      }

      req.setFunction(function()
      {
         location.reload();
      });

      req.send();
   }

   this.init = function()
   {
      var fs, rules, tmp, img, addBx;

      fs = cr('fieldset');
      fs.className = 'hl';

      tmp = cr('legend');
      tmp.appendChild(document.createTextNode(typeof editId == 'undefined' ? 'define new condition rules' : 'edit an existing condition rule'));
      fs.appendChild(tmp);

      tmp = cr('div');
      tmp.id = form + idParts.rules;
      tmp.className = 'rules';

      img = cr('img');
      img.src = grafiken.plus;

      addBx = cr('a');
      addBx.href = '#';
      addBx.appendChild(img);
      addBx.appendChild(document.createTextNode(' add another condition variable'));

      addBx.onclick = function()
      {
         addConnectExpression();
         addSelectBox();
         return false;
      }

      tmp.appendChild(addBx);
      fs.appendChild(tmp);

      tmp = cr('div');
      tmp.id = form + idParts.actions;
      tmp.className = 'rules';
      tmp.style.display = 'none';
      fs.appendChild(tmp);

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('condition name'));
      fs.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'text';
      tmp.id = form + idParts.conditionName;
      fs.appendChild(tmp);

      fs.appendChild(cr('br'));

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('condition description'));
      fs.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'text';
      tmp.id = form + idParts.conditionDescription;
      fs.appendChild(tmp);

      fs.appendChild(cr('br'));

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('status type'));
      fs.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'checkbox';
      tmp.id = form + idParts.statusType;
      fs.appendChild(tmp);

      fs.appendChild(document.createTextNode(' tag this rule as critical status'));
      fs.appendChild(cr('br'));

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('locking'));
      fs.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'checkbox';
      tmp.id = form + idParts.lockType;
      fs.appendChild(tmp);

      fs.appendChild(document.createTextNode(' lock this rule after execution'));
      fs.appendChild(cr('br'));

      tmp = cr('label');
      fs.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'checkbox';
      tmp.id = form + idParts.microblogCondition;
      tmp.disabled = 'disabled';
      fs.appendChild(tmp);

      fs.appendChild(document.createTextNode(' publish current condition via microblogs'));

      if(typeof editId != 'undefined')
      {
         fs.appendChild(cr('br'));

         tmp = cr('a');
         tmp.className = 'deleteaccount';
         tmp.href = '#';

         tmp.onclick = function()
         {
            deleteCondition();
            return false;
         }

         tmp.appendChild(document.createTextNode('delete this condition'));
         fs.appendChild(tmp);
      }

      tmp = cr('input');
      tmp.type = 'submit';
      tmp.className = 'submit';
      tmp.value = typeof editId == 'undefined' ? 'save conditions' : 'save changes';

      tmp.onclick = function()
      {
         save();
         return false;
      }

      fs.appendChild(tmp);

      $(form).appendChild(fs);

      ruleCounter = 0;
      ruleCounterAkt = 0;

      if(typeof editId == 'undefined')
      {
         addSelectBox();
      }
   }

   this.buildMessageMenu = function()
   {
      buildMessageMenu();
   }

   buildMessageMenu = function()
   {
      var tmp, img, addBx;

      tmp = $(form + idParts.actions);

      img = cr('img');
      img.src = grafiken.plus;

      addBx = cr('a');
      addBx.href = '#';
      addBx.appendChild(img);
      addBx.appendChild(document.createTextNode(' add another notification message'));

      addBx.onclick = function()
      {
         addMessageBox();
         return false;
      }

      tmp.appendChild(addBx);

      messageCounter = 0;
      messageCounterAkt = 0;

      if(typeof editId == 'undefined')
      {
         addMessageBox();
      }
   }

   deleteCondition = function()
   {
      if(confirm('are you sure to delete this condition'))
      {
         var req = new request('/conditions');
         req.setParam('aktion', 'delete');
         req.setParam('conditionid', editId);
         req.setFunction(function()
         {
            location.reload();
         });
         req.send();
      }
   }
}

function editCondition(conditionid)
{
   var req = new request('/conditions');
   req.setParam('aktion', 'edit');
   req.setParam('conditionid', conditionid);
   req.setFunction(function()
   {
      var fs,
      res = req.res();

      l = new layer('sclayerbg', 'sclayer');
      l.afterComplete(function()
      {
         var edit, i;

         edit = new Conditions('condition_edit');
         edit.setEditId(conditionid);
         edit.setAccounts(gAccounts);
         edit.setSensorVars(gSensorVars);
         edit.init();

         edit.setData(res.name, res.description, res.critical, res.dolock);

         for(i in res.variableids)
         {
            if(i > 0)
            {
               edit.addConnectExpression(res.connectoren[i - 1]);
            }

            edit.addSelectBox(res.sensorids[i], res.variableids[i], res.operatoren[i], res.werte[i]);
         }

         edit.buildMessageMenu();

         for(i in res.texts)
         {
            edit.addMessageBox(res.texts[i].text, res.texts[i].accountids, res.texts[i].accounttypes);
         }
      });
      l.init();

      fs = cr('form');
      fs.id = 'condition_edit';

      l.setDomTree(fs);
      l.setWH(873, 380);
   });
   req.send();
}
