/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/
function Dashboard(boxId)
{
   var streams, dashboardData, addWindow, currentId, removeWindows, addWindow, loadDashboard, createFooter, startRequest, timeout, requests, sinceIds, aktResults, parseText,
   currentId = 0,
   windowNum = new Object(),
   windowNumAkt = new Object(),
   editmode = false,
   idParts = {'header' : '_header',
              'window' : '_window',
              'footer' : '_footer',
              'keyword' : '_keyword',
              'windowType' : '_windowType',
              'dashboardType' : '_dashboardType',
              'dashboardName' : '_dashboardName',
              'defaultBoard' : '_defaultBoard',
              'search' : '_search',
              'interval' : '_interval',
              'results' : '_results',
              'js' : '_js'},
   grafiken = {'plus' : ENV.codeBase + '/img/plus.png',
               'plusOver' : ENV.codeBase + '/img/plus2.png',
               'abort' : ENV.codeBase + '/img/abort.png',
               'abortOver' : ENV.codeBase + '/img/abort2.png',
               'load' : ENV.codeBase + '/img/wait.gif'};

   this.setStreams = function(stream)
   {
      streams = stream;
   }

   this.setDashboardData = function(dashboards)
   {
      dashboardData = dashboards;
   }

   this.setDefault = function(id)
   {
      currentId = id;
   }

   this.editmode = function()
   {
      editmode = true;
   }

   this.appendKeyword = function(id, windowNum, keyword)
   {
      var searchField = $(id + '_' + windowNum + idParts.search);
      searchField.value += ' ' + keyword;
      searchField.onkeyup({'keyCode' : 13});
   }

   this.init = function()
   {
      var header, window, footer, fs, tmp, i,
      dataExists = false,
      moreThanOne = false,
      main = $(boxId);

      header = cr('div');
      header.id = boxId + idParts.header;

      fs = cr('fieldset');
      fs.className = 'hl' + (editmode ? ' float' : '');

      tmp = cr('legend');
      tmp.appendChild(document.createTextNode('current loaded dashboard'));
      fs.appendChild(tmp);

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('dashboard name:'));
      fs.appendChild(tmp);

      for(i in dashboardData)
      {
         if(dataExists)
         {
            moreThanOne = true;
            break;
         }
         else
         {
            dataExists = true;
         }
      }

      if(dataExists)
      {
         if(editmode || moreThanOne)
         {
            tmp = cr('select');
            tmp.id = boxId + idParts.dashboardType;

            if(editmode)
            {
               tmp.options[0] = new Option('[add another dashboard]', 0, true);
            }

            for(i in dashboardData)
            {
               tmp.options[tmp.options.length] = new Option(dashboardData[i].name, i, i == currentId ? true : false);
               dataExists = true;
            }

            tmp.onchange = function()
            {
               loadDashboard(this.value);
            }
         }
         else
         {
            tmp = document.createTextNode(dashboardData[i].name);
         }
      }
      else
      {
         tmp = document.createTextNode(editmode ? '[new dashboard]' : 'you need to add a dashboard');
      }

      fs.appendChild(tmp);
      header.appendChild(fs);

      if(editmode)
      {
         fs = cr('fieldset');
         fs.className = 'hl';

         tmp = cr('legend');
         tmp.appendChild(document.createTextNode('add a window to current dashboard'));
         fs.appendChild(tmp);

         tmp = cr('label');
         tmp.appendChild(document.createTextNode('microblog type:'));
         fs.appendChild(tmp);

         tmp = cr('select');
         tmp.id = boxId + idParts.windowType;
         tmp.options[0] = new Option('[select a microblog type]', 0, true);

         for(i in streams)
         {
            tmp.options[tmp.options.length] = new Option(streams[i].name, i);
         }

         tmp.onchange = function()
         {
            if(windowNumAkt[currentId] < 6)
            {
               addWindow(currentId, this.value);
               this.selectedIndex = 0;
            }
            else
            {
               alert('there cannot be added more than 6 windows per dashboard');
            }
         }

         fs.appendChild(tmp);
         header.appendChild(fs);
      }

      main.appendChild(header);

      window = cr('div');
      window.id = boxId + idParts.window;

      main.appendChild(window);

      if(editmode)
      {
         footer = cr('fieldset');
         footer.className = 'hl';
         footer.id = boxId + idParts.footer;

         main.appendChild(footer);
      }

      loadDashboard(currentId, true);
   }

   createFooter = function(id)
   {
      var tmp,
      footer = $(boxId + idParts.footer);

      tmp = cr('legend');
      tmp.appendChild(document.createTextNode('save changes'));
      footer.appendChild(tmp);

      tmp = cr('label');
      tmp.appendChild(document.createTextNode('dashboard name:'));
      footer.appendChild(tmp);

      tmp = cr('input');
      tmp.type = 'text';
      tmp.id = boxId + idParts.dashboardName;

      if(id != 0)
      {
         tmp.value = dashboardData[id].name;
      }

      footer.appendChild(tmp);
      footer.appendChild(cr('br'));

      if(id == 0 || dashboardData[id].default == 0)
      {
         tmp = cr('label');
         tmp.appendChild(document.createTextNode('is default dashboard:'));
         footer.appendChild(tmp);

         tmp = cr('input');
         tmp.type = 'checkbox';
         tmp.id = boxId + idParts.defaultBoard;

         footer.appendChild(tmp);
         footer.appendChild(cr('br'));

         tmp = cr('a');
         tmp.href = '#';
         tmp.className = 'deleteaccount';
         tmp.appendChild(document.createTextNode('delete loaded dashboard'));

         tmp.onclick = function()
         {
            if(confirm('are you sure to delete this dashboard?'))
            {
               req = new request('/dashboard');
               req.setParam('aktion', 'loeschen');
               req.setParam('dashboardid', currentId);
               req.setFunction(function()
               {
                  location.reload();
               });
               req.send();
            }

            return false;
         }

         footer.appendChild(tmp);
      }

      tmp = cr('input');
      tmp.type = 'submit';
      tmp.className = 'submit';
      tmp.value = 'save changes';

      tmp.onclick = function()
      {
         save();
         return false;
      }

      footer.appendChild(tmp);
   }

   loadDashboard = function(id, first)
   {
      var i;

      if(!editmode)
      {
         if(typeof first == 'undefined')
         {
            for(i in timeout)
            {
               if(typeof timeout[i] == 'number')
               {
                  clearTimeout(timeout[i]);
               }
            }

            for(i in requests)
            {
               requests[i].abort();
            }
         }

         timeout = new Object();
         requests = new Object();
         sinceIds = new Object();
         aktResults = new Object();
      }

      removeWindows(currentId);

      if(editmode)
      {
         removeChilds($(boxId + idParts.footer));
         createFooter(id);
      }

      if(typeof windowNum[id] == 'undefined')
      {
         windowNum[id] = 0;
         windowNumAkt[id] = 0;
      }

      if(id != 0)
      {
         for(i in dashboardData[id].windows)
         {
            addWindow(id, dashboardData[id].windows[i].streamurlid, dashboardData[id].windows[i].keyword, dashboardData[id].windows[i].reload, dashboardData[id].windows[i].results);
         }
      }

      currentId = id;
   }

   removeWindows = function(id)
   {
      removeChilds($(boxId + idParts.window));

      windowNum[id] = 0;
      windowNumAkt[id] = 0;
   }

   addWindow = function(id, streamurlid, search, reload, results)
   {
      var box, tmp, input;

      box = cr('fieldset');
      box.className = 'hl';
      box.id = boxId + '_' + id + idParts.window + '_' + windowNum[id];

      tmp = cr('legend');
      tmp.appendChild(document.createTextNode(editmode ? 'microblog window' : (streams[streamurlid].name + ' (' + results + ' results @ ' + reload + ' seconds)')));
      box.appendChild(tmp);

      if(editmode)
      {
         tmp = cr('label');
         tmp.appendChild(document.createTextNode('microblog:'));
         box.appendChild(tmp);

         tmp = cr('select');
         tmp.id = boxId + '_' + id + idParts.window + '_' + windowNum[id] + idParts.windowType;

         for(i in streams)
         {
            tmp.options[tmp.options.length] = new Option(streams[i].name, i, i == streamurlid ? true : false);
         }

         box.appendChild(tmp);

         tmp = cr('label');
         tmp.appendChild(document.createTextNode('search:'));
         box.appendChild(tmp);

         tmp = cr('input');
         tmp.type = 'text';
         tmp.id = boxId + '_' + id + idParts.window + '_' + windowNum[id] + idParts.search;

         if(typeof search != 'undefined')
         {
            tmp.value = search;
         }

         box.appendChild(tmp);

         tmp = cr('label');
         tmp.appendChild(document.createTextNode('reload time:'));
         box.appendChild(tmp);

         tmp = cr('input');
         tmp.type = 'text';
         tmp.style.width = '85px';
         tmp.id = boxId + '_' + id + idParts.window + '_' + windowNum[id] + idParts.interval;

         if(typeof reload != 'undefined')
         {
            tmp.value = reload;
         }

         box.appendChild(tmp);
         box.appendChild(document.createTextNode(' seconds'));

         tmp = cr('label');
         tmp.appendChild(document.createTextNode('result limit:'));
         box.appendChild(tmp);

         tmp = cr('input');
         tmp.type = 'text';
         tmp.style.width = '85px';
         tmp.id = boxId + '_' + id + idParts.window + '_' + windowNum[id] + idParts.results;

         if(typeof results != 'undefined')
         {
            tmp.value = results;
         }

         box.appendChild(tmp);
         box.appendChild(document.createTextNode(' results'));

         tmp = cr('img');
         tmp.src = grafiken.abort;
         tmp.title = 'remove this window';

         tmp.onmouseover = function()
         {
            this.src = grafiken.abortOver;
         }

         tmp.onmouseout = function()
         {
            this.src = grafiken.abort;
         }

         tmp.onclick = function()
         {
            if(windowNumAkt[id] > 1)
            {
               remove(this.parentNode);
               windowNumAkt[id]--;
            }
            else
            {
               alert('error: this window could not be removed, because a dashboard must consist of at least 1 window');
            }
         }

         box.appendChild(tmp)
      }
      else
      {
         tmp = cr('input');
         tmp.type = 'text';
         tmp.name = id + '_' + windowNum[id] + '_' + reload + '_' + streamurlid + '_' + results;
         tmp.id = id + '_' + windowNum[id] + idParts.search;
         tmp.value = search;
         tmp.style.width = '230px';
         tmp.style.textAlign = 'center';

         tmp.onkeyup = function(e)
         {
            if(!e)
            {
               e = window.event
            }

            if(e.keyCode == 13)
            {
               var r = this.name.split('_');

               if(typeof timeout[r[1]] == 'number')
               {
                  clearTimeout(r[1]);
               }

               requests[r[1]].abort();

               sinceIds[r[1]] = 0;
               startRequest(r[0], r[1], r[2], r[3], r[4], this.value, true);
            }
         }

         box.appendChild(tmp);

         tmp = cr('div');
         tmp.id = boxId + '_' + id + idParts.window + '_' + windowNum[id] + idParts.results;
         tmp.className = 'results';

         box.appendChild(tmp);
      }

      $(boxId + idParts.window).appendChild(box);

      if(!editmode)
      {
         sinceIds[windowNum[id]] = 0;
         startRequest(id, windowNum[id], reload, streamurlid, results, search, true);
      }

      windowNum[id]++;
      windowNumAkt[id]++;
   }

   parseText = function(text, baseurl, id, windowNum)
   {
      var
      tag = /(\#[a-zA-Z0-9_üäöÜÄÖ]+)/g,
      user = /\@([a-zA-Z0-9_]+)/g,
      url = /(http:\/\/\S*)/g;

      if(url.test(text))
      {
         text = text.replace(url, '<a href="$1" class="link" target="_blank">$1</a>');
      }

      if(tag.test(text))
      {
         text = text.replace(tag, '<a href="#" onclick="gDashboard.appendKeyword(' + id + ', ' + windowNum + ', \'$1\'); return false" class="keyword">$1</a>');
      }

      if(user.test(text))
      {
         text = text.replace(user, '<a href="' + baseurl + '$1" class="touser" target="_blank">@$1</a>');
      }

      return text;
   }

   startRequest = function(id, windowNum, seconds, streamurlid, results, query, first)
   {
      var img, tmp,
      resultsField = $(boxId + '_' + id + idParts.window + '_' + windowNum + idParts.results);

      requests[windowNum] = new request('/microblog');
      requests[windowNum].setNoLoadWindow();
      requests[windowNum].setParam('query', query);
      requests[windowNum].setParam('since', sinceIds[windowNum]);
      requests[windowNum].setParam('rpp', results);
      requests[windowNum].setParam('url', streams[streamurlid].url);

      if(typeof first != 'undefined')
      {
         removeChilds(resultsField);
         aktResults[windowNum] = 0;

         resultsField.style.textAlign = 'center';

         tmp = cr('img');
         tmp.src = grafiken.load;
         tmp.alt = 'loading';
         tmp.title = 'loading content...';

         resultsField.appendChild(tmp);
         resultsField.appendChild(cr('br'));
         resultsField.appendChild(document.createTextNode('loading results...'));
      }

      requests[windowNum].setFunction(function()
      {
         var p, i, a, img, oldFirst,
         res = requests[windowNum].res();

         sinceIds[windowNum] = res.max_id + 1;

         if(typeof first != 'undefined')
         {
            removeChilds(resultsField);

            if(res.results.length == 0)
            {
               p = cr('p');
               p.style.fontWeight = 'bold';
               p.appendChild(document.createTextNode('no results found!'));
               resultsField.appendChild(p);
            }
            else
            {
               resultsField.style.textAlign = 'left';
            }
         }
         else if(aktResults[windowNum] > 0)
         {
            oldFirst = resultsField.firstChild;
         }

         if(aktResults[windowNum] + res.results.length > results)
         {
            if(aktResults[windowNum] == res.results.length)
            {
               removeChilds(resultsField);
               aktResults[windowNum] = 0;
            }
            else
            {
               while(aktResults[windowNum] + res.results.length > results)
               {
                  remove(resultsField.lastChild);
                  aktResults[windowNum]--;
               }
            }
         }

         for(i = 0; i < res.results.length; i++)
         {
            p = cr('p');

            img = cr('img');
            img.src = res.results[i].profile_image_url;
            img.style.width = '15px';
            p.appendChild(img);

            a = cr('a');
            a.href = streams[streamurlid].baseurl + res.results[i].from_user;
            a.target = '_blank';
            a.className = 'user';
            a.appendChild(document.createTextNode(res.results[i].from_user));

            p.appendChild(a);
            p.appendChild(document.createTextNode(' ' + res.results[i].created_at.substr(5, 20) + ':'));
            p.appendChild(cr('br'));
            p.innerHTML += parseText(res.results[i].text, streams[streamurlid].baseurl, id, windowNum);

            if(aktResults[windowNum] == 0 || typeof first != 'undefined')
            {
               resultsField.appendChild(p);

               if(aktResults[windowNum] == 0)
               {
                  oldFirst = resultsField.firstChild;
               }
            }
            else
            {
               resultsField.insertBefore(p, oldFirst);
            }

            aktResults[windowNum]++;
         }

         timeout[windowNum] = setTimeout(function()
         {
            startRequest(id, windowNum, seconds, streamurlid, results, query);
         }, seconds * 1000);
      });

      requests[windowNum].send();
   }

   save = function()
   {
      var i,
      c = 0,
      req = new request('/dashboard');

      if(currentId != 0)
      {
         req.setParam('aktion', 'update');
         req.setParam('dashboardid', currentId);
      }
      else
      {
         req.setParam('aktion', 'anlegen');
      }

      req.setParam('name', $(boxId + idParts.dashboardName).value);

      req.setParam('default', currentId == 0 || dashboardData[currentId].default == 0 ? ($(boxId + idParts.defaultBoard).checked ? 1 : 0) : 1);

      for(i = 0; i < windowNum[currentId]; i++)
      {
         if($(boxId + '_' + currentId + idParts.window + '_' + i) != null)
         {
            req.setParam('window[' + c + '][0]', $(boxId + '_' + currentId + idParts.window + '_' + i + idParts.windowType).value);
            req.setParam('window[' + c + '][1]', $(boxId + '_' + currentId + idParts.window + '_' + i + idParts.search).value);
            req.setParam('window[' + c + '][2]', $(boxId + '_' + currentId + idParts.window + '_' + i + idParts.interval).value);
            req.setParam('window[' + c + '][3]', $(boxId + '_' + currentId + idParts.window + '_' + i + idParts.results).value);
            c++;
         }
      }

      req.setFunction(function()
      {
         location.reload();
      });

      req.send();
   }
}
