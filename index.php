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

?>
<meta name="viewport" content="width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<body>

<form action="oc.php" method="get">
<table border='1'>
<tr>
<td>Stop:</td>
<td><input type="number" name="stop" maxlength=4 autocomplete="off" /></td>
</tr>
<tr>
<td>Route:</td>
<td><input type="number" name="route" maxlength=3 autocomplete="off" /></td>
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

<form action="oc.php" method="get">
<table border='1'>
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
