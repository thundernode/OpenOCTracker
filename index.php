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

<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<body>
<?php
/**
 * @file
 * stuff
 */

require 'stops.php';
require 'creds.php';
echo "<script type='text/javascript'>

          var _gaq = _gaq || [];
      _gaq.push(['_setAccount', '$analyticsid']);
      _gaq.push(['_trackPageview']);

              (function() {
                              var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                                                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                                                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
                                                                    })();

            </script>";
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
	echo "<tr bordercolor='blue' bgcolor='#CCCCCC'>";
	echo "<td align='center'>" . $route . "</td>";
	echo "<td align='center'>" . $stop->StopNo . "</td>";
	echo "<td align='center'>" . $stop->StopLabel . "</td>";
	echo '</tr>';
}

/**
 * Generates the table titles for the schedule output
 */
function genTitles() {
	echo '<tr>';
	echo "<td class='h' align='center'> Destination </td>";
	echo "<td class='h' align='center'> in </td>";
	echo "<td class='h' align='center'> Last Updated </td>";
	echo '</tr>';
}

/**
 * Generates the table info for the schedule output
 */
function genInfo($trip) {

  echo '<tr>';
  echo "<td align='center'>" . $trip->TripDestination . "</td>";
  echo "<td align='center'>" . $trip->AdjustedScheduleTime . " min. </td>";
  if ($trip->AdjustmentAge < 0) {
    echo "<td align='center'>";
    echo 'Schedule';
    echo "</td>";
  }
  else {
    $time = explode('.', $trip->AdjustmentAge);
    echo "<td align='center'>";
    $fixtime = round($time[1] * 60 / 100);
    echo "$time[0]  min. $fixtime sec. ago at <a href='https://maps.google.ca/maps?q=loc:$trip->Latitude,$trip->Longitude'>~$trip->GPSSpeed km/h</a>";
    echo "</td>";
  }
  echo '</tr>';

}

/**
 * Parses the JSON and outputs the requested shedule
 */
function displayInfo($bus, $route) {
  $stop = $bus->GetNextTripsForStopResult;
  genHead($stop, $route);
  foreach ($stop->Route->RouteDirection as $routedir) {
    echo "<tr><td class='h' colspan='3' align='center'>$routedir->Direction</td></tr>";
    genTitles();
    foreach ($routedir->Trips as $trip) {
      if ($trip->Trip->TripDestination) {
        foreach ($trip->Trip as $info) {
          genInfo($info);
        }
      }
      else {
        echo "<tr><td class='h' colspan='3' align='center'>No trips scheduled at this time</td></tr>";
      }
    }
  }
}

if (isset($_GET['street'])) {
	if (!empty($_GET['street'])) {
		stopFind($_GET['street']);
	}
}
elseif (!empty($_GET['stop'])) {
	if (!empty($_GET['route'])) {
		$routes = explode(' ', $_GET['route']);
		foreach ($routes as $route) {
			$stop = getOCJson('stopSum', $_GET['stop']);
			//$exists = checkStop($stop, $_GET['route']);
			$exists = checkStop($stop, $route);
			if ($exists) {
			  $bus = getOCJson('stopGPS', $_GET['stop'], $route);
			  echo "<table border='2' width='300'>";
			  displayInfo($bus, $route);
			  echo '</table>';
			  echo '</br>';
			}
			else {
			  echo "Sorry, the " .  $route . " doesn't appear to pass at stop number " . $_GET['stop'] . '.';
			}
		}
	}
	//elseif (empty($_GET['route'])){
	else {
		echo "<table border='2' width='300'>";
		$routes = array_unique(listRoutes(getOCJson('stopSum', $_GET['stop'])));
		foreach ($routes as $route) {
			$bus = getOCJson('stopGPS', $_GET['stop'], $route);
			displayInfo($bus, $route);
		}
		echo '</table>';
		echo '</br>';
	}
}
else {
  echo 'Welcome to OC Help Me! </br> </br>';
}
?>



<form method="get">
<table border='1' width='300'>
<tr>
<td>Stop:</td>
<td><input width='100%' type="tel" name="stop" autocomplete="off" value="<?php echo $_GET['stop']?>" /></td>
</tr>
<tr>
<td>Route:</td>
<td><input width='100%' type="tel" name="route" autocomplete="off" value="<?php echo $_GET['route']?>" /></td>
</tr>
<tr>
<td>&nbsp;</td>
<td>
<input type="submit" />
</td>
</tr>
</table>
</form>

OR
</br>
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

</body>
</html>
