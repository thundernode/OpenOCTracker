
<?php
function stopFind($street) {
  $stops = fopen('./stops.txt', r);?>
	<tr>
		<th id="TwoColLeft"> Stop </th>
		<th id="TwoColRight"> Intersection/Map </th>
	</tr>
<?php
  while ($row = fgets($stops)) {
    if (preg_match("/$street/i", $row)) {
      $results = explode(',', $row);
		?>
      <tr>
      <td><a href="/?stop=<?php echo $results[0]; ?>&route="><?php echo $results[0]; ?></a></td>
      <td><a href="https://maps.google.ca/maps?q=loc:<?php echo $results[2]; ?>,<?php echo $results[3]; ?>"><?php echo $results[1]; ?></a></td>
      </tr>
<?php   }
  }
}
?>
