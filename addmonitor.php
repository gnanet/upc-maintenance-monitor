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

echo "<div class='container'><div class='content'><section id='result'>";

if ( isset($_POST['email']) && !empty($_POST['email']) && isset($_POST['varos']) && !empty($_POST['varos']) ) {

  $errors = array();
  if (validate_email($_POST['email'])) {
    $email = htmlspecialchars($_POST['email']);
  } else {
    $errors[] = "Invalid email address.";
  }

    global $valid_city_file;
    $cityfile = file_get_contents($valid_city_file);
    if ($cityfile === FALSE) {
        $result['errors'][] = "Can't open database.";
        return $result;
    }
    $json_cities = json_decode($cityfile, true);
    if ($json_cities === null && json_last_error() !== JSON_ERROR_NONE) {
        $result['errors'][] = "Can't read database: " . htmlspecialchars(json_last_error());
        return $result;
    }

    foreach ($json_cities as $assoc_city) {
        $city_array[] = $assoc_city['value'];
    }

    if ( ! in_array(trim($_POST['varos']), $city_array) ) {
        $result['errors'][] = "City could not be validated";
        return $result;
    } else {
        $varos = trim($_POST['varos']);
    }

  if (is_array($errors) && count($errors) != 0) {
    $errors = array_unique($errors);
    foreach ($errors as $key => $value) {
      echo "<div class='alert alert-danger' role='alert'>";
      echo htmlspecialchars($value);
      echo "</div>";
    }
    echo "Please return and try again.<br>";
  } elseif ( is_array($errors) && count($errors) == 0 && (! empty($varos)) ) {
    echo "<div class='alert alert-info' role='alert'>";
    echo "Email: " . htmlspecialchars($email) . ".<br>";
    echo "</div>";
      $userip = $_SERVER["HTTP_X_FORWARDED_FOR"] ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
      $add_varos = add_varos_to_pre_check($varos, $email, $userip);
      if (is_array($add_varos["errors"]) && count($add_varos["errors"]) != 0) {
        $errors = array_unique($add_varos["errors"]);
        foreach ($add_varos["errors"] as $key => $err_value) {
          echo "<div class='alert alert-danger' role='alert'>";
          echo htmlspecialchars($err_value);
          echo "</div></div>";
        }
      } else {
        echo "<div class='alert alert-success' role='alert'>";
        echo "Confirmation email sent. Please confirm your subscription email to complete the process.<br>";
        echo "</div></div>";
      }
  } else {
    echo "<div class='alert alert-danger' role='alert'>";
    echo "Too many issues.<br>";
    echo "Please return and try again.<br>";
    echo "</div></div>";
  }
} else {

  echo "<div class='alert alert-danger' role='alert'>";;
  echo "Error. City and email address are required.<br>";
  echo "Please return and try again.<br>";
  echo "</div></div>";
}




?>
