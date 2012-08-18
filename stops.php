<?php
require 'creds.php';
function stopFind($street) {
  $stops = fopen('./stops.txt', r);
  while ($row = fgets($stops)) {
    if (preg_match("/$street/i", $row)) {
      $test = explode(',', $row);
      print "<a href='$siteURL/oc.php?stop=$test[0]&route='>$test[0]</a> <a href='https://maps.google.ca/maps?q=loc:$test[2],$test[3]'>$test[1]</a> </br>";
    }
  }
}
?>
