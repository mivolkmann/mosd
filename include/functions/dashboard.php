<?php
/*
This Source Code Form is subject to the terms of the Mozilla Public License, v. 2.0. If a copy of the MPL was not distributed with this file, You can obtain one at http://mozilla.org/MPL/2.0/.
*/

/**
* @author Ronny Bansemer
*/

   $this->addJavascript('dashboard');
   $this->addJavascriptBlank('var gDashboard = new Dashboard(\'streams\');');

   $streams = array();

   DB::query('SELECT
                  `streamurlid`,
                  `name`,
                  `baseurl`,
                  `searchurl`
              FROM
                  `streamurls`
              WHERE
                  `unternehmenid` = ?
              OR
                  `unternehmenid` = 0
              ORDER BY
                  `name` ASC', 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

   while(DB::nextResult())
   {
      $streams[DB::result('streamurlid')] = array('name' => DB::result('name'),
                                                  'url' => DB::result('searchurl'),
                                                  'baseurl' => DB::result('baseurl'));
   }

   $this->addJavascriptBlank('gDashboard.setStreams('.json_encode($streams).');');

   $dashboard = array();
   $default = 0;

   DB::query('SELECT
                  `dashboardid`,
                  `name`,
                  `default`
              FROM
                  `dashboard` `d1`
              WHERE
                  `d1`.`unternehmenid` = ?', 1);
   DB::setParam($_SESSION['unternehmenid'], 'int');
   DB::exec();

   if(DB::numRows() > 0)
   {
      $dashboardids = array();

      while(DB::nextResult())
      {
         $dashboard[DB::result('dashboardid')] = array('name' => DB::result('name'),
                                                       'default' => DB::result('default'),
                                                       'windows' => array());

         if(DB::result('default') == 1)
         {
            $default = DB::result('dashboardid');
         }

         $dashboardids[] = DB::result('dashboardid');
      }

      DB::query('SELECT
                     `dashboardid`,
                     `windowid`,
                     `streamurlid`,
                     `keyword`,
                     `reload`,
                     `results`
                 FROM
                     `dashboard_windows`
                 WHERE
                     `dashboardid` IN ('.implode(',', $dashboardids).')
                 ORDER BY
                     `windowid` ASC', 0);
      DB::exec();

      while(DB::nextResult())
      {
         $dashboard[DB::result('dashboardid')]['windows'][DB::result('windowid')] = array('streamurlid' => DB::result('streamurlid'),
                                                                                          'keyword' => DB::result('keyword'),
                                                                                          'reload' => DB::result('reload'),
                                                                                          'results' => DB::result('results'));
      }

      $this->addJavascriptBlank('gDashboard.setDashboardData('.json_encode($dashboard).');');
      $this->addJavascriptBlank('gDashboard.setDefault('.$default.');');
   }

   if($this->_getExists('editmode'))
   {
      $this->addJavascriptBlank('gDashboard.editmode();');
   }

   $this->addJavascriptBlank('gDashboard.init();');


?>
