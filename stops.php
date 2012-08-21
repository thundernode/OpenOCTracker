<?php
require 'creds.php';
function stopFind($street) {
  $stops = fopen('./stops.txt', r);
  while ($row = fgets($stops)) {
    if (preg_match("/$street/i", $row)) {
      $results = explode(',', $row);
      print "<a href='$siteURL/oc.php?stop=$results[0]&route='>$results[0]</a> <a href='https://maps.google.ca/maps?q=loc:$results[2],$results[3]'>$results[1]</a> </br>";
    }
  }
}
?>
