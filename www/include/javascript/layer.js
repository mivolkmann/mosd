/*

   Skript-Name: Layer
   Version: 1.0a
   Autor: Ronny Bansemer

   Copyright (c) 2010 by SimplyClear.de

   Es ist nicht gestattet, dieses Skript ohne meine Einwilligung
   für den privaten oder kommerziellen Zweck zu verwenden.

*/

function layer(bgid, id)
{
   var bglayer, layer, close, blending, blendBySize, blendBackground, setWH,
   sc, centering, afterComplete, initWait, setContent, open,
   domTree = [],
   openProcess = false,
   fadeInAfterResize = false,
   blendTyp = 0,
   bgopa = 0.25,
   isInit = false,
   steps = 10,
   size = [1, 1],
   loadSize = [78, 78],
   stepAdd = [0, 0];

   this.init = function()
   {
      if(!isInit)
      {
         bglayer = cr('div');
         bglayer.id = bgid;

         bglayer.onclick = function()
         {
            close();
         }

         bglayer = setOpacity(bglayer, 0);
         document.body.appendChild(bglayer);
         bglayer = $(bgid);

         layer = cr('div');
         layer.id = id;

         document.body.appendChild(layer);
         layer = $(id);

         blendBackground(steps - 1 - bgopa * 10);

         isInit = true;
      }

      initWait();
   }

   initWait = function()
   {
      var cont, img;

      removeChilds(layer);
      sc = getScreenCenter();

      cont = cr('div');
      cont.id = id + '_init';

      img = cr('img');
      img.src = ENV.codeBase + '/img/wait.gif';

      cont.appendChild(img);
      setDomTree(cont);
      setWH(loadSize[0], loadSize[1]);
      open();
   }

   this.setDomTree = function(content)
   {
      setDomTree(content);
   }

   setDomTree = function(content)
   {
      removeChilds(layer);

      if(fadeInAfterResize)
      {
         domTree.push(content);
      }
      else
      {
         layer.appendChild(content);
      }
   }

   this.appendDomTree = function(content)
   {
      layer.appendChild(content);
   }

   this.open = function()
   {
      open();
   }

   open = function()
   {
      if(!openProcess)
      {
         openProcess = true;

         if(size[0] > 0 && size[1] == 0)
         {
            blendTyp = 1;
         }
         else if(size[0] == 0 && size[1] > 0)
         {
            blendTyp = 2;
         }
         else if(size[0] > 0 && size[1] > 0)
         {
            blendTyp = 3;
         }

         blending(steps - 1);

         if(!isInit)
         {
            blendBackground(steps - 1 - 5);
         }
      }
      else
      {
         setTimeout(function()
         {
            open();
         }, 500);
      }
   }

   this.setWH = function(w, h)
   {
      setWH(w, h);
   }

   setWH = function(w, h)
   {
      stepAdd = [(w - size[0]) / steps, (h - size[1]) / steps];
   }

   close = function()
   {
      remove(bglayer);
      remove(layer);
      isInit = false;
      size = [1, 1];
   }

   blending = function(stepsLeft)
   {
      size[0] += stepAdd[0];
      size[1] += stepAdd[1];

      switch(blendTyp)
      {
         case 0:
            blendBySize(stepsLeft, 0, 0);
            break;

         case 1:
            blendBySize(stepsLeft, size[0], 0);
            break;

         case 2:
            blendBySize(stepsLeft, 0, size[1]);
            break;

         case 3:
            blendBySize(stepsLeft, size[0], size[1]);
      }
   }

   blendBySize = function(stepsLeft, w, h)
   {
      var tmpId, c, inter;

      if(!isInit)
      {
         document.body.appendChild(layer);
         layer = $(id);
         isInit = true;

         if(w == 0 && h == 0)
         {
            centering(layer);
         }
      }

      if(w > 0)
      {
         layer.style.width = w + 'px';
         layer.style.left = sc[0] - w * 0.5 + 'px';
      }

      if(h > 0)
      {
         layer.style.minHeight = h + 'px';
         layer.style.top = sc[1] - h * 0.5 + 'px';
      }

      if(stepsLeft > 0)
      {
         setTimeout(function()
         {
            blending(stepsLeft - 1);
         }, 25);
      }
      else if(fadeInAfterResize)
      {
         dT = setOpacity(domTree.shift(), 0);
         openProcess = false;

         if(dT.id != '')
         {
            tmpId = dT.id;
         }
         else
         {
            tmpId = id + '_inner';
            dT.id = tmpId;
         }

         layer.appendChild(dT);
         dT = $(tmpId);

         c = 2;

         inter = setInterval(function()
         {
            if(c == 10)
            {
               clearInterval(inter);

               if(typeof afterComplete == 'function')
               {
                  afterComplete();
                  afterComplete = null;
               }
            }

            setOpacity(dT, c / 10);
            c += 2;
         }, 50);
      }
      else if(typeof afterComplete == 'function')
      {
         afterComplete();
         afterComplete = null;
         openProcess = false;
      }
      else
      {
         openProcess = false;
      }
   }

   blendBackground = function(stepsLeft)
   {
      setOpacity(bglayer, (steps - bgopa * 10 - stepsLeft) / 10);

      if(stepsLeft > 0)
      {
         setTimeout(function()
         {
            blendBackground(stepsLeft - 1);
         }, 50);
      }
   }

   this.afterComplete = function(fkt)
   {
      afterComplete = fkt;
   }

   this.fadeInAfterResize = function()
   {
      fadeInAfterResize = true;
   }

   this.close = function()
   {
      close();
   }
}