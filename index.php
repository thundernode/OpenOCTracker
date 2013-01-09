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
require 'creds.php';
?>

<head>

<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
<link rel="icon" href="/favicon.ico" />
<link rel="apple-touch-icon" href="favicon.ico"/>
<link rel="apple-touch-icon-precomposed" href="favicon.ico"/>

<meta name="viewport" content="initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?= $analyticsid ?>']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</head>

<body>
<div style="width: 100%; margin: 0px auto; max-width: 650px;">

<?php
/**
 * Fetches XML from octranspo and converts it to JSON
 * $request should either be 'stopSum' if you want to get a summary of
 * the routes that go to a stop or 'stopGPS' if you want the schedule'
 */
function getOCJson($request, $stop, $route = NULL) {
  require 'creds.php';
  if ($request == 'stopSum') {
    $url = 'GetRouteSummaryForStop';
  }
  elseif ($request == 'stopGPS') {
    $url = 'GetNextTripsForStop';
  }
  $c = curl_init();
  curl_setopt($c, CURLOPT_URL, "https://api.octranspo1.com/v1.1/$url");
  curl_setopt($c, CURLOPT_POST, TRUE);
  curl_setopt($c, CURLOPT_POSTFIELDS, "appID=$aID&apiKey=$aKey&stopNo=$stop&routeNo=$route");
  curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($c, CURLOPT_FOLLOWLOCATION, TRUE);
  $response = curl_exec($c);
  $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "", $response);
  $xml = simplexml_load_string($response);
  $json = json_encode($xml);
  $json_o = json_decode($json);
  curl_close($c);
  return $xml;
}

/**
 * Function returns all route for the inputted stop
 */
function listRoutes($stopjson) {
  $routes = $stopjson->GetRouteSummaryForStopResult;
  foreach ($routes->Routes as $route) {
    if ($route->RouteNo) {
      $stoproutes[] = $route->RouteNo;
        return $stoproutes;
    }
  }
  foreach ($routes->Routes->Route as $route) {
    $stoproutes[] = $route->RouteNo;
  }
  return $stoproutes;
}

/**
 * Function checks if the inputted route passes at the inputted stop
 */
function checkStop($stopjson, $userroute) {
  $exists = FALSE;
  $routes = $stopjson->GetRouteSummaryForStopResult;

  foreach ($routes->Routes->Route as $route) {
    if ($userroute == $route->RouteNo) {
        return TRUE;
    }
  }
}

/**
 * Generates the table headers for the schedule output
 */
function genHead($stop, $route) {
  ?>
  <tr bordercolor='blue' bgcolor='#CCCCCC'>
  <td id='left' align='center'> <?= $route ?> </td>
  <td id='center' align='center'> <?= $stop->StopNo ?> </td>
  <td id='right' align='center'> <?= $stop->StopLabel ?> </td>
  </tr>
  <?php
}

/**
 * Generates the table titles for the schedule output
 */
function genTitles() {
  ?>
  <tr>
  <td class='h' align='center'> Destination </td>
  <td class='h' align='center'> in </td>
  <td class='h' align='center'> Last Updated </td>
  </tr>
  <?php
}

/**
 * Generates the table info for the schedule output
 */
function genInfo($trip) {
  ?>
  <tr>
  <td style='width:47.5%' align='center'> <?= $trip->TripDestination ?> </td>
  <td style='width:5%' align='center'> <?= $trip->AdjustedScheduleTime ?>  min. </td>
  <?php
  if ($trip->AdjustmentAge < 0) {
    ?>
    <td style='width:47.5%' align='center'> Schedule </td>
    <?php
  }
  else {
    $time = explode('.', $trip->AdjustmentAge);
    $fixtime = round($time[1] * 60 / 100);
    ?>
    <td style='width:47.5%' align='center'>
    <?= $time[0] ?> min. <?= $fixtime ?> sec. ago at <a href='https://maps.google.ca/maps?q=loc:<?= $trip->Latitude ?>,<?= $trip->Longitude ?>'>~<?= $trip->GPSSpeed ?> km/h</a>
    </td>
    <?php
  }
  ?>
  </tr>
  <?php
}

/**
 * Parses the JSON and outputs the requested shedule
 */
function displayInfo($bus, $route) {
  $stop = $bus->GetNextTripsForStopResult;
  genHead($stop, $route);
  foreach ($stop->Route->RouteDirection as $routedir) {
    ?>
    <tr><td class='h' colspan='3' align='center'> <?= $routedir->Direction ?> </td></tr>
    <?php
    genTitles();
    foreach ($routedir->Trips as $trip) {
      if ($trip->Trip->TripDestination) {
        foreach ($trip->Trip as $info) {
          genInfo($info);
        }
      }
      else {
        ?>
	<tr><td class='h' colspan='3' align='center'>No trips scheduled at this time</td></tr>
	<?php
      }
    }
  }
}

if (isset($_GET['street'])) {
  if (!empty($_GET['street'])) {
    ?>
    <div style='width: 400px; margin: 0px auto;'>
    <?php stopFind($_GET['street']); ?>
    </div>
    <?php
  }
}
elseif (!empty($_GET['stop'])) {
  if (!empty($_GET['route'])) {
    $routes = explode(' ', $_GET['route']);
    foreach ($routes as $route) {
      $stop = getOCJson('stopSum', $_GET['stop']);
      $exists = checkStop($stop, $route);
      if ($exists) {
        $bus = getOCJson('stopGPS', $_GET['stop'], $route);
        ?>
        <div style='width: 100%; max-width: 650px;'>
        <table border='2' style='width: 100%;'>
        <?php
        displayInfo($bus, $route);
        ?>
        </table>
        </div>
        </br>
        <?php
      }
      else {
        ?>
        Sorry, the <?= $route ?> doesn't appear to pass at stop number <?= $_GET['stop'] ?>.
        <?php
      }
  }
}
else {
  $routes = array_unique(listRoutes(getOCJson('stopSum', $_GET['stop'])));
  if (count($routes) <= 5) {
    ?>
    <div style='width: 100%; max-width: 650px;'>
    <?php
    foreach ($routes as $route) {
      ?>
      <table border='2' style='width: 100%;'>
      <?php
      $bus = getOCJson('stopGPS', $_GET['stop'], $route);
      displayInfo($bus, $route);
      ?>
      </table>
      </br>
      <?php
    }
    ?>
    </div>
    <?php
  }
  else {
    $stop = $_GET['stop'];	
    ?>
    Which route would you like to view?
    </br>
    <?php
    foreach ($routes as $route) {
    ?>
    <a href='/?stop=<?= $stop ?>&route=<?= $route ?>'><?= $route ?></a>
    <?php
  }
  ?>
  </br>
  <?php
  }
  ?>
  </br>
  <?php
  }
}
else {
  ?>
  <div style='width: 250px; margin: 0px auto;'> <h3> Welcome to OC Help Me! </h3> </div>
  <?php
}
?>


<div style="float: left; width: 100%; max-width: 300px;">
<form method="get">
<table border='1' width='300'>
<tr>
<td>Stop:</td>
<td><input width='100%' type="tel" name="stop" autocomplete="off" value="<?= $_GET['stop'] ?>" /></td>
</tr>
<tr>
<td>Route:</td>
<td><input width='100%' type="tel" name="route" autocomplete="off" value="<?= $_GET['route'] ?>" /></td>
</tr>
<tr>
<td>&nbsp;</td>
<td>
<input type="submit" />
</td>
</tr>
</table>
</form>
</div>

<div style="float: left; width: 40px;">
<a style='width: 20px; margin: 0px auto; display: block;'>OR</a>
</div>

<div style="float: left; width: 100%; max-width: 300px;">
<form method="get">
<table border='1' width='300'>
<tr>
<td>Street:</td>
<td><input type="text" name="street"/></td>
</tr>
<tr>
<td>&nbsp;</td>
<td>
<input type="submit" />
</td>
</tr>
</table>
</form>
</div>

</div>
</body>
</html>
