<?php

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.

// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

error_reporting(E_ALL & ~E_NOTICE);
require_once('lib/settings.php');
require('parts/header.php');

echo '<div class="container">'."\n";
if ( isset($_GET['id']) && !empty($_GET['id'])  ) {
  $id = htmlspecialchars($_GET['id']);
  $uuid_pattern = "/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/";
  if (preg_match($uuid_pattern, $id)) {
    $userip = $_SERVER["HTTP_X_FORWARDED_FOR"] ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
    $add_varos = add_varos_check($id, $userip);
    if (is_array($add_varos["errors"]) && count($add_varos["errors"]) != 0) {
      $errors = array_unique($add_varos["errors"]);
      foreach ($add_varos["errors"] as $key => $err_value) {
        echo "<div class='alert alert-danger' role='alert'>";
        echo htmlspecialchars($err_value);
        echo "</div>";
      }
    } else {
      echo "<div class='alert alert-success' role='alert'>";
      echo "Check added. You will now receive notifications on maintenance events.<br>";
      echo "</div>";
    }
  } else {
      echo "<div class='alert alert-danger' role='alert'>";;
      echo "Error. ID is invalid.<br>";
      echo "Please return and try again.<br>";
      echo "</div>";
  }
} else {
  echo "<div class='alert alert-danger' role='alert'>";;
  echo "Error. ID is required.<br>";
  echo "Please return and try again.<br>";
  echo "</div>";
}

echo "<!-- container end -->\n";
echo "</div>\n";

require('parts/footer.php');

?>
