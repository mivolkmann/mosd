/*

   Skript-Name: OpenBox
   Version: 1.0a
   Autor: Ronny Bansemer

   Copyright (c) 2010 by SimplyClear.de

   Es ist nicht gestattet, dieses Skript ohne meine Einwilligung
   für den privaten oder kommerziellen Zweck zu verwenden.

*/

function openBox()
{
   var i, openLayer, tt, lay,
   counter = 0,
   ids = {'layerbg' : 'sclayerbg',
          'layer' : 'sclayer',
          'tooltip' : 'scttbox',
          'openbox' : 'scopenbox',
          'bedien' : 'scbedien',
          'ne' : 'openboxnext',
          'pr' : 'openboxprevious'},
   grafiken = {'ne' : '/img/openbox/next.png',
               'pr' : '/img/openbox/previous.png',
               'neO' : '/img/openbox/next2.png',
               'prO' : '/img/openbox/previous2.png'},
   parameter = new Object(),
   imgs = document.getElementsByName('obox'),
   sizeValues = [12, 31];

   lay = new layer(ids.layerbg, ids.layer);
   tt = new tooltip(ids.tooltip, 0, 20);

   for(i = 0; i < imgs.length; i++)
   {
      imgs[i].style.cursor = 'pointer';

      imgs[i].onmouseover = function(e)
      {
         if(parameter[this.alt].tooltip != '')
         {
            var pos = eventPosition(e);
            tt.setXY(pos[0], pos[1]);
            tt.setText(parameter[this.alt].tooltip);
            tt.show();
         }
      }

      imgs[i].onmousemove = function(e)
      {
         if(parameter[this.alt].tooltip != '')
         {
            var pos = eventPosition(e);
            tt.setXY(pos[0], pos[1]);
         }
      }

      imgs[i].onmouseout = function()
      {
         if(parameter[this.alt].tooltip != '')
         {
            tt.hide();
         }
      }

      imgs[i].onclick = function()
      {
         if(parameter[this.alt].tooltip != '')
         {
            tt.hide();
         }

         openLayer(this.alt);
      }
   }

   openLayer = function(ele)
   {
      var pre, load;

      lay.fadeInAfterResize();
      lay.init();

      pre = new Image();
      pre.src = parameter[ele].src;

      load = setInterval(function()
      {
         if(pre.complete)
         {
            var img, outer, des;

            clearInterval(load);

            outer = cr('div');
            outer.id = ids.openbox;

            img = cr('img');
            img.src = pre.src;
            img.width = parameter[ele].w;
            img.height = parameter[ele].h;

            outer.appendChild(img);

            des = cr('p');
            des.appendChild(document.createTextNode(parameter[ele].text));

            outer.appendChild(des);
            lay.setDomTree(outer);

            if(counter > 1)
            {
               lay.afterComplete(function()
               {
                  var cont, ne, pre, akt, cl, mainbox, inter, c, startHide, waitForInit;

                  cont = cr('div');
                  cont.id = ids.bedien;
                  cont.style.width = parameter[ele].w + 'px';
                  cont = setOpacity(cont, 0);

                  cont.show = function()
                  {
                     var ele = this;

                     if(typeof inter == 'number')
                     {
                        clearInterval(inter);
                     }

                     if(typeof c == 'undefined')
                     {
                        c = 2;
                     }

                     inter = setInterval(function()
                     {
                        if(c == 10)
                        {
                           clearInterval(inter);
                        }

                        setOpacity(ele, c / 10);
                        c += 2;
                     }, 25);
                  }

                  cont.hide = function()
                  {
                     var ele = this;

                     if(typeof inter == 'number')
                     {
                        clearInterval(inter);
                     }

                     inter = setInterval(function()
                     {
                        if(c == 0)
                        {
                           clearInterval(inter);
                        }

                        setOpacity(ele, c / 10);
                        c -= 2;
                     }, 25);
                  }

                  pre = cr('img');
                  pre.src = grafiken.pr;
                  pre.className = 'pre';
                  pre.id = ids.pr;

                  pre.onmouseover = function()
                  {
                     this.src = grafiken.prO;
                  }

                  pre.onmouseout = function()
                  {
                     this.src = grafiken.pr;
                  }

                  pre.onclick = function()
                  {
                     var i, lasti;

                     document.onkeydown = null;
                     document.onkeyup = null;

                     for(i in parameter)
                     {
                        if(i == ele && typeof lasti != 'undefined')
                        {
                           break;
                        }

                        lasti = i;
                     }

                     openLayer(lasti);
                  }

                  ne = cr('img');
                  ne.src = grafiken.ne;
                  ne.className = 'ne';
                  ne.id = ids.ne;

                  ne.onmouseover = function()
                  {
                     this.src = grafiken.neO;
                  }

                  ne.onmouseout = function()
                  {
                     this.src = grafiken.ne;
                  }

                  ne.onclick = function()
                  {
                     var i, lasti, nexti;

                     document.onkeydown = null;
                     document.onkeyup = null;

                     for(i in parameter)
                     {
                        if(lasti == ele)
                        {
                           nexti = i;
                           break;
                        }

                        lasti = i;
                     }

                     if(typeof nexti == 'undefined')
                     {
                        for(i in parameter)
                        {
                           nexti = i;
                           break;
                        }
                     }

                     openLayer(nexti);
                  }

                  akt = cr('span');
                  akt.appendChild(document.createTextNode('Bild ' + (parameter[ele].num + 1) + ' von ' + counter));

                  cont.appendChild(pre);
                  cont.appendChild(ne);
                  cont.appendChild(akt);

                  waitForInit = setInterval(function()
                  {
                     if($(ids.openbox) != null)
                     {
                        clearInterval(waitForInit);

                        mainbox = $(ids.openbox);
                        mainbox.appendChild(cont);

                        mainbox.onmouseover = function()
                        {
                           if(typeof startHide == 'number')
                           {
                              clearTimeout(startHide);
                           }

                           $(ids.bedien).show();
                        }

                        mainbox.onmouseout = function()
                        {
                           $(ids.bedien).hide();
                        }

                        $(ids.bedien).show();

                        startHide = setTimeout(function()
                        {
                           if($(ids.bedien) != null)
                           {
                              $(ids.bedien).hide();
                           }
                        }, 2000);

                        document.onkeydown = function(e)
                        {
                           if(!e)
                           {
                              e = window.event;
                           }

                           if(e.keyCode == 37)
                           {
                              $(ids.bedien).show();
                              $(ids.pr).onmouseover();
                           }
                           else if(e.keyCode == 39)
                           {
                              $(ids.bedien).show();
                              $(ids.ne).onmouseover();
                           }
                        }

                        document.onkeyup = function(e)
                        {
                           if(!e)
                           {
                              e = window.event;
                           }

                           if(e.keyCode == 37)
                           {
                              setTimeout(function()
                              {
                                 if($(ids.pr) != null)
                                 {
                                    $(ids.pr).onclick();
                                 }
                              }, 150);
                           }
                           else if(e.keyCode == 39)
                           {
                              setTimeout(function()
                              {
                                 if($(ids.ne) != null)
                                 {
                                    $(ids.ne).onclick();
                                 }
                              }, 150);
                           }
                        }
                     }
                  }, 500);
               });
            }

            lay.setWH(parameter[ele].w + sizeValues[0], parameter[ele].h + sizeValues[1]);
            lay.open();
         }
      }, 500);
   }

   this.setParam = function(name, src, w, h, tText, longText)
   {
      parameter[name] = {'src' : src,
                         'w' : w,
                         'h' : h,
                         'tooltip' : tText,
                         'text' : longText,
                         'num' : counter};
      counter++;
   }
}