/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
function Editieren()
{
   var setImageStatus, inputClassName, sendValue, selectValues,
   grafiken = {'edit' : ENV.codeBase + '/img/edit.png',
               'editOver' : ENV.codeBase + '/img/edit2.png',
               'abort' : ENV.codeBase + '/img/abort.png',
               'abortOver' : ENV.codeBase + '/img/abort2.png'},
   previous = null;

   this.showEditfield = function(ele, url, selectEle)
   {
      if(typeof selectEle != 'undefined')
      {
         selectValues = selectEle;
      }
      else
      {
         selectValues = null;
      }

      if(ele.getElementsByTagName('img').length == 0)
      {
         var editImg = cr('img');
         editImg.name = url;
         editImg.className = 'editImage';

         ele.appendChild(setImageStatus(editImg, 0));
      }
   }

   setImageStatus = function(img, status)
   {
      switch(status)
      {
         case 0:
            img.src = grafiken.edit;

            img.onmouseover = function()
            {
               this.src = grafiken.editOver;
            }

            img.onmouseout = function()
            {
               this.src = grafiken.edit;
            }

            img.onclick = function()
            {
               abortEdit();
               setImageStatus(this, 1);
            }
            break;

          case 1:
            previous = {'ele' : img.parentNode,
                        'text' : img.parentNode.getElementsByTagName('span')[0].innerHTML,
                        'type' : selectValues == null ? 1 : 2};
            createEditField(img.parentNode, img.name);

            img.src = grafiken.abortOver;

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
               abortEdit();
               setImageStatus(this, 0);
            }
            break;
      }

      return img;
   }

   createEditField = function(ele, url)
   {
      var inp, currVal,
      bufferEle = ele.getElementsByTagName('span')[0];
      currVal = trim(bufferEle.innerHTML);

      if(selectValues == null)
      {
         inp = cr('input');
         inp.type = 'text';
         inp.value = currVal;

         inp.onkeyup = function(e)
         {
            if(!e)
            {
               e = window.event
            }

            if(e.keyCode == 13)
            {
               sendValue(this.value, url);
            }
         }
      }
      else
      {
         inp = cr('select');

         for(i in selectValues)
         {
            inp.options[inp.options.length] = new Option(selectValues[i], i, false, currVal == i ? true : false);
         }

         inp.onchange = function()
         {
            sendValue(this.value, url);
         }
      }

      ele.removeChild(bufferEle);
      ele.appendChild(inp);

      if(selectValues == null)
      {
         ele.getElementsByTagName('input')[0].focus();
      }
   }

   abortEdit = function()
   {
      if(previous != null)
      {
         var ele;

         previous.ele.removeChild(previous.ele.getElementsByTagName(previous.type == 1 ? 'input' : 'select')[0]);

         ele = cr('span');
         ele.appendChild(document.createTextNode(previous.text));

         previous.ele.appendChild(ele);
         setImageStatus(previous.ele.getElementsByTagName('img')[0], 0);

         previous = null;
      }
   }

   sendValue = function(value, url)
   {
      var req = new request(url);

      req.setParam('value', value, 1);

      req.setFunction(function()
      {
         previous.text = req.res();
         abortEdit();
      });

      req.send();
   }
}
