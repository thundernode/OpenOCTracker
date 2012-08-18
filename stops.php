<?php

$stops = fopen('./stops.txt', r);
$street = $_GET['street'];
while ($row = fgets($stops)) {
  if (preg_match("/$street/i", $row)) {
    $test = explode(',', $row);
    print "<a href='http://oc.thundernode.net/oc.php?stop=$test[0]&route='>$test[0]</a> <a href='https://maps.google.ca/maps?q=$test[2],$test[3]'>$test[1]</a> </br>";
  }
}

?>

<form action="stops.php" method="get">
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
