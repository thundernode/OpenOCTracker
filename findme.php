<?php

function findMe($lat,$lng){
  $stops = fopen('./stops.txt', r);
  ?>
  <tr>
  <td id='TwoColLeft'> Stop </td>
  <td id='TwoColRight'> Intersection/Map </td>
  </tr>
  <?php
  while ($row = fgets($stops)) {
    if (preg_match("/$lat/i", $row) && preg_match("/$lng/i", $row)) {
      $results = explode(',', $row);
      ?>
      <tr>
      <td><a href='/?stop=<?= $results[0] ?>&route='><?= $results[0] ?></a></td>
      <td><a href='https://maps.google.ca/maps?q=loc:<?= $results[2],$results[3]?>'><?= $results[1] ?></a></td>
      </tr>
    <?php
    }
  }
}
?>
