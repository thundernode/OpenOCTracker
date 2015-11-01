<?php
function stopFind($street) {
  $stops = fopen('./stops.txt', r);
  ?>
  <tr>
  <td id='StopsThreeColLeft'> Stop </td>
  <td id='StopsThreeColCenter'> Intersection/Map </td>
  <td id='StopsThreeColRight'> Routes </td>
  </tr>
  <?php
  while ($row = fgets($stops)) {
    if (preg_match("/$street/i", $row)) {
      $results = explode(',', $row);
      ?>
      <tr>
      <td><a href='/?stop=<?= $results[0] ?>&route='><?= $results[0] ?></a></td>
      <td><a href='https://maps.google.ca/maps?q=loc:<?=$results[2]?>,<?=$results[3]?>'><?= $results[1] ?></a></td>
      <td>
        <?php
        $routes = explode(" ", $results[4]);
        if (count($routes) <= 4) {
          foreach($routes as $route) {
            ?>
              <?= $route?>
            <?php
          }
        }
        else {
        ?>
          <?= $routes[0]?> <?= $routes[1]?> <?= $routes[2]?> <?= $routes[3]?> <a href='/?stop=<?=$results[0]?>'>more</a>
        <?php
        }
        ?>
      </td>
      </tr>
      <?php
    }
  }
}
?>
