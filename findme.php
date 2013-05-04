<?php

function calculateDistanceFromLatLong($point1,$point2) {
  //  Use Haversine formula to calculate the great circle distance
  //    between two points identified by longitude and latitude
  $earthMeanRadius = 6371.009; // km

  $deltaLatitude = deg2rad($point2['latitude'] - $point1['latitude']);
  $deltaLongitude = deg2rad($point2['longitude'] - $point1['longitude']);
  $a = sin($deltaLatitude / 2) * sin($deltaLatitude / 2) +
     cos(deg2rad($point1['latitude'])) * cos(deg2rad($point2['latitude'])) *
     sin($deltaLongitude / 2) * sin($deltaLongitude / 2);
  $c = 2 * atan2(sqrt($a), sqrt(1-$a));
  $distance = $earthMeanRadius * $c;
  return $distance;
}

function findMe($lat,$lng,$acc){
  $stoplist = fopen('./stops.txt', 'r');
  ?>
  <tr>
  <td id='TwoColLeft'>&nbsp;</td>
  <td id='TwoColRight'><a href='https://maps.google.ca/maps?q=loc:<?=$lat?>,<?=$lng?>'>Device Reported Location (Within=<?= round($acc)?>m)</a><td>
  </td>
  <tr>
  <td id='TwoColLeft'> Stop </td>
  <td id='TwoColRight'> Intersection/Map </td>
  </tr>
  <?php

  while (($data = fgetcsv($stoplist, 1000, ',', '"')) !== FALSE) {

    $startPoint = array( 'latitude'  => $lat,
                         'longitude' => $lng
                       );
    $endPoint = array( 'latitude'  => $data[2],
                       'longitude' => $data[3]
                      );
  $distance = calculateDistanceFromLatLong($startPoint,$endPoint);
    if ($distance <= 1) {
      $stops[] = array('stop' => $data[0], 'intersect' => $data[1], 'lat' => $data[2], 'lng' => $data[3], 'dist' => round($distance*1000));
    }
  }
  usort($stops, function($a, $b) {
        return $a['dist'] - $b['dist'];
  });

  foreach($stops as $stop) {
  ?>
  <tr>
  <td><a href='/?stop=<?= $stop['stop'] ?>&route='><?= $stop['stop'] ?></a></td>
  <td><a href='https://maps.google.ca/maps?q=loc:<?= $stop['lat']?>,<?= $stop['lng']?>'><?= $stop['intersect'] ?></a> (<?= $stop['dist']?>M)</td>
  </tr>
  <?php
  }
  fclose($stoplist);
}
?>
