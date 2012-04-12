/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
function $(id)
{
   return document.getElementById(id);
}

function cr(ele)
{
   return document.createElement(ele);
}

function remove(ele)
{
   ele.parentNode.removeChild(ele);
}

function removeChilds(ele)
{
	while(ele.childNodes.length > 0)
   {
      ele.removeChild(ele.lastChild);
   }
}

function request(skript)
{
   var post, fkt, para, transform,
   header = [],
   vars = [],
   req = null,
   response = null,
   noLoadWindow = false,
   successfull = false;

   header[0] = ['Content-Type', 'application/x-www-form-urlencoded'];
   header[1] = ['Method', 'post ' + ENV.ajaxBase + skript + ' HTTP/1.1'];
   header[2] = ['Charset', 'utf-8'];

   this.setParam = function(name, value)
   {
      vars[vars.length] = [name, encodeURIComponent(value)];
   }

   this.addHeader = function(name, value)
   {
      header[header.length] = [name, value];
   }

   this.setFunction = function(name, param)
   {
      fkt = name;
      para = param;
   }

   this.setNoLoadWindow = function()
   {
      noLoadWindow = true;
   }

   this.abort = function()
   {
      if(req.readyState != 4)
      {
         req.abort();
      }
   }

   this.send = function()
   {
      var i, daten = '',
      img = cr('img');

      if(!noLoadWindow)
      {
         img.id = 'ajaxload';
         img.src = ENV.codeBase + '/img/loader.gif';
         $('menu').appendChild(img);
      }

      if(window.XMLHttpRequest)
      {
         req = new XMLHttpRequest();
      }
      else if(window.ActiveXObject)
      {
         try
         {
            req = new ActiveXObject('Msxml2.XMLHTTP');
         }
         catch(ex)
         {
            try
            {
               req = new ActiveXObject('Microsoft.XMLHTTP');
            }
            catch(ex)
            {
            }
         }
      }

      if(vars.length > 0)
      {
         for(i = 0; i < vars.length; i++)
         {
            daten += vars[i][0] + '=' + vars[i][1] + ((i < vars.length - 1) ? '&' : '');
         }

         req.open('POST', ENV.ajaxBase + skript, true);
         req.setRequestHeader('Content-Length', daten.length);
      }
      else
      {
         header[1] = ['Method', 'get ' + ENV.ajaxBase + ' HTTP/1.1'];
         req.open('GET', ENV.ajaxBase + skript, true);
         req.withCredentials = "true";
      }

      for(i = 0; i < header.length; i++)
      {
         req.setRequestHeader(header[i][0], header[i][1]);
      }

      req.onreadystatechange = function()
      {
         if(req.readyState == 4 && req.status == 200)
         {
            response = eval('(' + req.responseText + ')');

            if(!noLoadWindow)
            {
               remove($('ajaxload'));
            }

            switch(response['s'])
            {
               case 1: successfull = true; break;
               case 2: alert('Error: ' + decodeURIComponent(response['c'])); break;
               case 3: location.reload(); break;
            }

            if(successfull && typeof fkt != 'undefined')
            {
               if(typeof para != 'undefined')
               {
                  fkt.apply(null,para);
               }
               else
               {
                  fkt();
               }
            }
         }
      }

      req.send(vars.length > 0 ? daten : null);
   }

   this.res = function()
   {
      return transform(response['c']);
   }

   transform = function(f)
   {
      if(typeof f == 'Object')
      {
         for(i in f)
         {
            f[i] = transform(f[i]);
         }
      }
      else if(typeof f == 'Array')
      {
         for(i = 0; i < f.length; i++)
         {
            f[i] = transform(f[i]);
         }
      }
      else if(typeof f == 'String')
      {
         f = decodeURIComponent(f);
      }

      return f;
   }
}

function eventPosition(ev)
{
   if(!ev)
   {
      ev = window.event;
      x = ev.clientX + document.documentElement.scrollLeft;
      y = ev.clientY + document.documentElement.scrollTop;
   }
   else
   {
      x = ev.pageX;
      y = ev.pageY;
   }

   return [x, y];
}

function position(element)
{
	var valueT = 0, valueL = 0;

	do
	{
		valueT += element.offsetTop  || 0;
		valueL += element.offsetLeft || 0;
		element = element.offsetParent;
	}
	while (element);

	return [valueL, valueT];
}

function trim(value)
{
   return value.replace (/^\s+/, '').replace (/\s+$/, '');
}

function setOpacity(ele, val)
{
   ele.style.MozOpacity = val;
   ele.style.opacity = val;
   ele.style.filter = 'alpha(opacity=' + val * 100 + ')';

   return ele;
}

function getScreenCenter()
{
   var l = document.body.offsetWidth / 2,
   t = 0;

   if(typeof window.pageYOffset != 'undefined')
   {
      t = window.pageYOffset + window.innerHeight / 2;
      l = window.innerWidth / 2;
   }
   else if(typeof document.documentElement.scrollTop != 'undefined')
   {
      t = document.documentElement.scrollTop + document.documentElement.clientHeight / 2;
      l = document.documentElement.clientWidth / 2;
   }

   return [l, t];
}

function delOver()
{
   var ele, i;

   ele = document.getElementsByTagName('img');

   for(i = 0; i < ele.length; i++)
   {
      if(ele[i].name == 'del')
      {
         ele[i].style.cursor = 'pointer';

         ele[i].onmouseover = function()
         {
            this.src = ENV.codeBase + '/img/abort2.png';
         }

         ele[i].onmouseout = function()
         {
            this.src = ENV.codeBase + '/img/abort.png';
         }
      }
   }
}

function editOver()
{
   var ele, i;

   ele = document.getElementsByTagName('img');

   for(i = 0; i < ele.length; i++)
   {
      if(ele[i].name == 'edit')
      {
         ele[i].style.cursor = 'pointer';

         ele[i].onmouseover = function()
         {
            this.src = ENV.codeBase + '/img/edit2.png';
         }

         ele[i].onmouseout = function()
         {
            this.src = ENV.codeBase + '/img/edit.png';
         }
      }
   }
}

function einfuegen(StartTag, EndeTag, input)
{
   var range, insText, start, end, insText, pos;

   input.focus();

   if(typeof document.selection != 'undefined')
   {
      range = document.selection.createRange();
      insText = range.text;
      range.text = StartTag + insText + EndeTag;
      range = document.selection.createRange();

      if(insText.length == 0)
      {
         range.move('character', -EndeTag.length);
      }
      else
      {
         range.moveStart('character', StartTag.length + insText.length + EndeTag.length);
      }

      range.select();
   }
   else
   {
      start = input.selectionStart;
      end = input.selectionEnd;
      insText = input.value.substring(start, end);

      input.value = input.value.substr(0, start) + StartTag + insText + EndeTag + input.value.substr(end);

      if(insText.length == 0)
      {
         pos = start + StartTag.length;
      }
      else
      {
         pos=start+StartTag.length+insText.length+EndeTag.length;
      }

      input.selectionStart = pos;
      input.selectionEnd = pos;
   }
}
