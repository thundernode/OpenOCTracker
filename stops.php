<?php
function stopFind($street) {
  $stops = fopen('./stops.txt', r);
  ?>
  <thead>
	  <tr>
		  <th id='TwoColLeft'> Stop </th>
		  <th id='TwoColRight'> Intersection/Map </th>
	  </tr>
  </thead>
  <tbody>
  <?php
  while ($row = fgets($stops)) {
    if (preg_match("/$street/i", $row)) {
      $results = explode(',', $row);
      echo '<tr>';
      echo '<td><a href="/?stop='.$results[0].'&route=">'.$results[0].'</a></td>';
      echo '<td><a href="https://maps.google.ca/maps?q=loc:'.$results[2].','.$results[3].'">'.$results[1].'</a></td>';
      echo '</tr>';
    }
  }
}
?>
</tbody>
