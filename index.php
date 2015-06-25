<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>

   <!--This file is part of OpenOCTracker.-->

   <!--OpenOCTracker is free software: you can redistribute it and/or modify-->
   <!--it under the terms of the GNU General Public License as published by-->
   <!--the Free Software Foundation, either version 3 of the License, or-->
   <!--(at your option) any later version.-->

   <!--OpenOCTracker is distributed in the hope that it will be useful,-->
   <!--but WITHOUT ANY WARRANTY; without even the implied warranty of-->
   <!--MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the-->
   <!--GNU General Public License for more details.-->

   <!--You should have received a copy of the GNU General Public License-->
   <!--along with OpenOCTracker.  If not, see <http://www.gnu.org/licenses/>.-->


   <?php
      /**
      * @file
      * stuff
      */

      require 'stops.php';
      require 'findme.php';
      require 'creds.php';
   ?>

   <head>
      <title> OC Help Me </title>

      <link rel="stylesheet" type="text/css" href="oc.css" />
      <script type="text/javascript" src="findme.js"></script>
      <link href='favicon.ico' rel='apple-touch-icon-precomposed' />
      <link href='favicon.ico' rel='icon' type='image/png' />
      <meta name="viewport" content="initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
      <link rel="stylesheet" type="text/css" href="bootstrap.min.css"/>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

      <?php if ($analyticsid) { ?>
      <script>
         (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
               m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
         })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

         ga('create', '<?= $analyticsid ?>', 'auto');
         ga('send', 'pageview');
      </script>

      <?php }; ?>

   </script>
</head>

<body>
   <div id='Page' class="row-fluid">

      <?php
         /**
         * Fetches XML from octranspo and converts it to JSON
         * $request should either be 'stopSum' if you want to get a summary of
         * the routes that go to a stop or 'stopGPS' if you want the schedule'
         */
         function getOCJson($request, $stop, $route = NULL) {
            if (isset($_GET['timeout']) && !empty($_GET['timeout'])){
               $timeout = $_GET['timeout'];
            }
            else {
               $timeout = 10;
            }
            require 'creds.php';
            if ($request == 'stopSum') {
               $url = 'GetRouteSummaryForStop';
            }
            elseif ($request == 'stopGPS') {
               $url = 'GetNextTripsForStop';
            }
            $c = curl_init();
            curl_setopt($c, CURLOPT_URL, "https://api.octranspo1.com/v1.2/$url");
            curl_setopt($c, CURLOPT_POST, TRUE);
            curl_setopt($c, CURLOPT_POSTFIELDS, "appID=$aID&apiKey=$aKey&stopNo=$stop&routeNo=$route&format=json");
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($c, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($c, CURLOPT_CONNECTTIMEOUT , $timeout);
            curl_setopt($c, CURLOPT_TIMEOUT, $timeout);
            $response = curl_exec($c);
            if (curl_errno($c) !== 0) {
               $error["$url" . "Result"]['Error'] = "curl error " . curl_errno($c);
               curl_close($c);
               return $error;
            }
            elseif ($response == "") {
               $error["$url" . "Result"]['Error'] = "Empty Response";
               curl_close($c);
               return $error;
            }
            else {
               curl_close($c);
               return json_decode($response, $assoc = TRUE);
            }
         }

         /**
         * Function returns all route for the inputted stop
         */
         function listRoutes($stopjson) {
            $routes = $stopjson['GetRouteSummaryForStopResult'];
            foreach ($routes['Routes'] as $route) {
               if ($route['RouteNo']) {
                  $stoproutes[] = $route['RouteNo'];
                  return $stoproutes;
               }
            }
            foreach ($routes['Routes']['Route'] as $route) {
               $stoproutes[] = $route['RouteNo'];
            }
            return $stoproutes;
         }

         /**
         * Function checks if the inputted route passes at the inputted stop
         */
         function checkStop($info, $stop, $route = NULL) {
            if (isset($_GET['timeout']) && !empty($_GET['timeout'])){
               $timeout = $_GET['timeout'];
            }
            else {
               $timeout = 10;
            }
            $error = $info['Error'];
            if (! isset($info['Error'])) {
               return "An unknown error has occured for $stop $route";
            }
            elseif ($error == "") {
               return false;
            }
            else {
               switch ($error) {
                  case "10":
                  return "Stop $stop doesn't appear to exist.";
                  case "11":
                  if ($route != "") {
                     return "Route $route doesn't appear to exist.";
                  }
                  else {
                     return "There appears to be a blank space in the Route field.";
                  }
                  case "12":
                  if ($route != "") {
                     return "Route $route doesn't appear to service stop $stop.";
                  }
                  else {
                     return "There appears to be a blank space in the Route field.";
                  }
                  case "curl error 6":
                     return 'Could not contact Octranspo Servers.  Please refresh the page. <script type="text/javascript"> location.reload(); </script>';
                  case "curl error 28":
                     return "Connection to octranspo timed out (waited $timeout seconds), <a href='/?stop=$stop&route=" . $_GET['route'] . "&timeout=" . ($timeout + 10) . "'>try longer?</a>";
                  default:
                  return "An unknown error has occured ($error)";
               }
            }
         }

         /**
         * Generates the table headers for the schedule output
         */
         function genHead($stop, $route) {
         ?>
         <tr bordercolor='blue' bgcolor='#CCCCCC'>
            <td id='ThreeColLeft'> <?= $route ?> </td>
            <td id='ThreeColCenter'> <?= $stop['StopNo'] ?> </td>
            <td id='ThreeColRight'> <?= $stop['StopLabel'] ?> </td>
         </tr>
         <?php
         }

         /**
         * Generates the table titles for the schedule output
         */
         function genTitles() {
         ?>
         <tr>
            <td class='h'> Destination </td>
            <td class='h'> in </td>
            <td class='h'> Last Updated </td>
         </tr>
         <?php
         }

         /**
         * Generates the table info for the schedule output
         */
         function genInfo($trip) {
            if (preg_match('/6/',$trip['BusType']) && preg_match('/4/',$trip['Bustype'])){
               if (preg_match('/B/',$trip['BusType'])){
                  $type = '(60 or 40 Footer / Bike Rack)';
                  $bike = TRUE;
               }
               else {
                  $type = '(60 or 40 Footer)';
                  $bike = FALSE;
               }
            }
            elseif (preg_match('/6/',$trip['BusType'])){
               if (preg_match('/B/',$trip['BusType'])){
                  $type = '(60 Footer / Bike Rack)';
                  $bike = TRUE;
               }
               else {
                  $type = '(60 Footer)';
                  $bike = FALSE;
               }
            }
            elseif (preg_match('/4/',$trip['BusType'])){
               if (preg_match('/B/',$trip['BusType'])){
                  $type = '(40 Footer / Bike Rack)';
                  $bike = TRUE;
               }
               else {
                  $type = '(40 Footer)';
                  $bike = FALSE;
               }
            }
            elseif (preg_match('/DD/',$trip['BusType'])){
               if (preg_match('/B/',$trip['BusType'])){
                  $type = '(Double-Decker / Bike Rack)';
                  $bike = TRUE;
               }
               else {
                  $type = '(Double-Decker)';
                  $bike = FALSE;
               }
            }
            else {
               $type = '(Size Unknown)';
               $bike = FALSE;
            }
         ?>
         <tr>
            <td> <?= $trip['TripDestination'] ?><br /><?= $type ?></td>
            <td> <?= $trip['AdjustedScheduleTime'] ?>  min. </td>
            <?php
               if ($trip['AdjustmentAge'] < 0) {
               ?>
               <td> Schedule </td>
               <?php
               }
               else {
                  $time = explode('.', $trip['AdjustmentAge']);
                  $fixtime = round($time[1] * 60 / 100);
               ?>
               <td>
                  <?= $time[0] ?> min. <?= $fixtime ?> sec. ago at <a href='https://maps.google.ca/maps?q=loc:<?= $trip['Latitude'] ?>,<?= $trip['Longitude'] ?>'>~<?= $trip['GPSSpeed'] ?> km/h</a>
               </td>
               <?php
               }
            ?>
         </tr>
         <?php
            return $bike;
         }

         /**
         * Parses the JSON and outputs the requested shedule
         */
         function displayInfo($bus, $route) {
            $stop = $bus['GetNextTripsForStopResult'];
            genHead($stop, $route);
            $stop['Route']['RouteDirection'] = isset($stop['Route']['RouteDirection'][0]) ? $stop['Route']['RouteDirection'] : array($stop['Route']['RouteDirection']);
            foreach ($stop['Route']['RouteDirection'] as $routedir) {
            ?>
            <tr><td class='h' colspan='3'> <?= $routedir['Direction'] ?> </td></tr>
            <?php
               genTitles();
               $routedir['Trips']['Trip'] = isset($routedir['Trips']['Trip'][0]) ? $routedir['Trips']['Trip'] : array($routedir['Trips']['Trip']);
               foreach ($routedir['Trips']['Trip'] as $trip) {
                  if ($trip['TripDestination']) {
                     genInfo($trip);
                  }
                  else {
                  ?>
                  <tr><td class='h' colspan='3'>No trips scheduled at this time</td></tr>
                  <?php
                  }
               }
            }
         }

         if (isset($alertTop) && !empty($alertTop)) {
         ?>
         <div id='alertTop' class="span8 offset2">
            <?= $alertTop ?>
         </div>
         <?php
         }

         if (isset($_GET['street'])) {
            if (!empty($_GET['street'])) {
            ?>
            <div id='StopInfoTable'>
               <table border='2' class='table-condensed'>
                  <?php stopFind($_GET['street']); ?>
               </table>
            </div>
            <?php
            }
         }

         elseif (isset($_GET['lat']) && isset($_GET['lng'])){
            if (!empty($_GET['lat']) && !empty($_GET['lng'])) {
            ?>
            <div id='StopInfoTable'>
               <table border='2'>
                  <?php findMe($_GET['lat'],$_GET['lng'],$_GET['acc']); ?>
               </table>
            </div>
            <?php
            }
         }

         elseif (!empty($_GET['stop'])) {
            if (!empty($_GET['route'])) {
               $routes = preg_split("/(\+|\ )/", $_GET['route']);
               foreach ($routes as $route) {
                  $bus = getOCJson('stopGPS', $_GET['stop'], $route);
                  $error = checkStop($bus['GetNextTripsForStopResult'], $_GET['stop'], $route);
                  if (! $error) {
                  ?>
                  <div id='StopInfoTable'>
                     <table border='2'>
                        <?php
                           displayInfo($bus, $route);
                        ?>
                     </table>
                  </div>
                  <?php
                  }
                  else {
                  ?>
                  <div id='StopInfoTable' class='span8 offset2'>
                     <?= $error ?>
                  </div>
                  <?php
                  }
               }
            }
            else {
               $routelist = getOCJson('stopSum', $_GET['stop']);
               $error = checkStop($routelist['GetRouteSummaryForStopResult'], $_GET['stop']);
               if (! $error) {
                  $routes = array_unique(listRoutes($routelist));
                  if (count($routes) <= 5) {
                     foreach ($routes as $route) {
                     ?>
                     <div id='StopInfoTable'>
                        <table border='2'>
                           <?php
                              $bus = getOCJson('stopGPS', $_GET['stop'], $route);
                              displayInfo($bus, $route);
                           ?>
                        </table>
                     </div>
                     <?php
                     }
                  }
                  else {
                     $stop = $_GET['stop'];
                  ?>
                  <div class='span6 offset3'>
                     Which route would you like to view?
                     </br>
                     <?php
                        foreach ($routes as $route) {
                        ?>
                        <button class='btn-small' onclick="location.href='/?stop=<?= $stop ?>&route=<?= $route ?>'">
                           <?= $route ?>
                        </button>

                        <?php
                        }
                     ?>
                  </div>
                  <?php
                  }
               ?>
               </br>
               <?php
               }
               else {
               ?>
               <div id='StopInfoTable' class='span8 offset2'>
                  <?= $error ?>
               </div>
               <?php
               }
            }
         }
         else{
         ?>
         <div class="span8 offset2 center-header">
            <h3> Welcome to OC Help Me! </h3>
         </div>
         <?php
         }
      ?>



      <div id='RouteStop' class="span4 offset2">
         <div class="row-fluid">
            <form method="get">
               <div class="span12">
                  <label for="stop">Stop:</label>
                  <input type="tel" name="stop" id="stop" autocomplete="off" value="<?php echo (isset($_GET['stop'])) ? $_GET['stop'] : ''; ?>" placeholder="3025" /><br />
                  <label for="route">Route(s):</label>
                  <input type="tel" id="route" name="route" autocomplete="off" value="<?php echo (isset($_GET['route'])) ? $_GET['route'] : ''; ?>" placeholder="94 95" /><br />
                  <input type="submit" value='Get Stop Info' class="btn btn-success"/>
                  <hr />
               </div>
            </form>
         </div>
      </div>


      <div id='StopSearch' class="span4">
         <form method="get">
            <label for="street">Street/Station:</label>
            <input id="street" type="text" name="street" placeholder="St Laurent" /><br />
            <input type="submit" value='Search For Stop' class="btn btn-info" />
         </form>
         <hr />
      </div>
      <div id='FindMe' class="span4">
         <button id='FindMeButton' onclick="findMe()" class="btn btn-primary">Find Me (only accurate on mobiles)</button>
      </div>
      <br />

      <?php
         if (isset($alertBottom) && !empty($alertBottom)) {
         ?>
         <div id='alertBottom' class="span8 offset2">
            <br />
            <?= $alertBottom ?>
         </div>
         <?php
         }
      ?>

   </div>
</body>
</html>
