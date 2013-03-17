<?php
function stopFind($street) {
  $stops = fopen('./stops.txt', r);
  ?>
  <tr>
  <td id='TwoColLeft'> Stop </td>
  <td id='TwoColRight'> Intersection/Map </td>
  </tr>
  <?php
  while ($row = fgets($stops)) {
    if (preg_match("/$street/i", $row)) {
      $results = explode(',', $row);
<<<<<<< HEAD
      echo '<div class="row-fluid"><div class="hero-unit">';
      echo '<div class="span2">Stop :<a href="/?stop='.$results[0].'&route=">'.$results[0].'</a></div>';
      echo '<div class="span6">Map :<a href="https://maps.google.ca/maps?q=loc:'.$results[2].','.$results[3].'">'.$results[1].'</a></div>';
	  echo '</div></div>';
=======
      ?>
      <tr>
      <td><a href='/?stop=<?= $results[0] ?>&route='><?= $results[0] ?></a></td>
      <td><a href='https://maps.google.ca/maps?q=loc:<?=$results[2],$results[3]?>'><?= $results[1] ?></a></td>
      </tr>
      <?php
>>>>>>> dev
    }
  }
}
?>
