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
$result = array();
if (php_sapi_name() == "cli") {

    require('lib/simple_html_dom.php');
    require('lib/settings.php');

    $url = 'https://www.upc.hu/segithetunk/hasznos-tudnivalok/karbantartasok/';
    $karbsave = 'data/upc-karb.html.snip';

  $removal_queue = array();
  $tmp_check_file = $check_file . ".tmp";
  if (!copy($check_file, $tmp_check_file)) {
    echo "Failed to copy $check_file to $tmp_check_file.\n";
    die();
  }

  $file = file_get_contents($tmp_check_file);
  if ($file === FALSE) {
    $result['errors'][] = "Can't open database.";
  }
  $json_a = json_decode($file, true);
  if ($json_a === null && json_last_error() !== JSON_ERROR_NONE) {
    $result['errors'][] = "Can't read database: " . htmlspecialchars(json_last_error());
  }

  if (count($json_a) == 0) {
    echo "Empty checklist.\n";
    die();
  }

  echo "===== start " . date('Y-m-d H:i:s') . "=====\n";

  clearstatcache();
  if ( @file_exists($karbsave) && ( filemtime($karbsave) > ( time() - (12*3600)) ) ) {
    $html = file_get_html($karbsave);
  } else {
    $livehtml = file_get_html($url);
    $karbhtmltable = "<table>\n";
    foreach($livehtml->find('div#lgi-foldout-karbantartas div div div table tr') as $el) {
        $karbhtmltable .= $el->outertext."\n";
    }
    $karbhtmltable .=  "</table>\n";
    file_put_contents($karbsave,$karbhtmltable);
    $html = file_get_html($karbsave);
  }


  foreach ($json_a as $key => $value) {
    $varos = $value['varos'];
    $email = $value['email'];
    echo "City:  " . $varos . ".\n";
    echo "Email: " . $email . ".\n";

//  search for city in _table
    $maintenance = array();

    foreach($html->find('tr') as $el) {
        if ( strpos($el->find('td',3)->innertext,$varos) !== false ) {
            $maintenance[] = array('start' => strtotime($el->find('td',0)->innertext),
                                    'end' => strtotime($el->find('td',1)->innertext),
                                    'location' => html_entity_decode($el->find('td',3)->innertext),
                                    'services' => html_entity_decode($el->find('td',2)->innertext),
                                    'type' => html_entity_decode($el->find('td',4)->innertext),
                                    'published' => $el->find('td',5)->innertext);
        }
    }

    if ( count($maintenance) == 0 ) {
        continue;
    }

    foreach ($maintenance as $maint_key => $maint_value) {
      echo html_entity_decode("\tMaintenance scheduled for " . $maint_value['location'] . ". start date: " . date("Y-m-d H:i:s T", $maint_value['start']) . ". affected services: " . $maint_value['type'] . " " .$maint_value['services']. "\n");
      maintenance_notification_emails($varos, $email, $maint_value);
    }

    $file = file_get_contents($check_file);
    if ($file === FALSE) {
      echo "\tCan't open database.\n";
      continue;
    }
    $json_a = json_decode($file, true);
    if ($json_a === null && json_last_error() !== JSON_ERROR_NONE) {
      echo "\tCan't read database\n";
      continue;
    }
    if ($json_a[$key]['errors'] != 0) {
      if (strpos($errortexts,'Domain has expired certificate in chain') !== false) {
        $json_a[$key]['errors'] = 0;
        $check_json = json_encode($json_a); 
        if(file_put_contents($check_file, $check_json, LOCK_EX)) {
          echo "\tError count reset to 0.\n";
        } else {
          echo "Can't write database.\n";
          die();
        }
      }
    }

    echo "\n";

  }
// ENDFOREACH

  echo "===== end " . date('Y-m-d H:i:s') . "=====\n";


  if ( count($removal_queue) != 0 ) {
    echo "Processing removal queue.\n";
    foreach ($removal_queue as $remove_key => $remove_value) {
      $unsub_url = "https://" . $current_link . "/unsubscribe.php?cron=auto&id=" . $remove_value;
      $file = file_get_contents($unsub_url);
      if ($file === FALSE) {
        $error = error_get_last();
        echo "HTTP request failed. Error was: " . $error['message'];
        echo "\tRemoval Error.\n";
        continue;
      } else {
        echo "\tRemoved $remove_value.\n";
      }
    }
  }

    // remove non-confirmed subs older than 7 days
  $tmp_pre_check_file = $pre_check_file . ".tmp";
  if (!copy($pre_check_file, $tmp_pre_check_file)) {
    echo "Failed to copy $pre_check_file to $tmp_pre_check_file.\n";
    die();
  }

  $tmp_pre_file = file_get_contents($tmp_pre_check_file);
  if ($tmp_pre_file === FALSE) {
    echo "Can't open database.\n";
    die();
  }
  $tmp_pre_json_a = json_decode($tmp_pre_file, true);
  if ($tmp_pre_json_a === null && json_last_error() !== JSON_ERROR_NONE) {
    echo "Can't read database.\n";
    die();
  }

  if (count($tmp_pre_json_a) == 0) {
    echo "Empty pre-checklist.\n";
    die();
  }

  foreach ($tmp_pre_json_a as $pre_key => $pre_value) {
    $today = strtotime(date("Y-m-d"));
    $pre_add_date = strtotime(date("Y-m-d",$pre_value['pre_add_date']));
    $pre_add_diff = $today - $pre_add_date;
    if ($pre_add_diff > "604800") {
      unset($tmp_pre_json_a[$pre_key]);
      $tmp_pre_json = json_encode($tmp_pre_json_a); 
      if(file_put_contents($pre_check_file, $tmp_pre_json, LOCK_EX)) {
        echo "Subscription for " . $pre_value['varos'] . " from " . $pre_value['email'] . " older than 7 days. Removing from subscription list.\n";
      } else {
        echo "Failed to remove subscription for " . $pre_value['varos'] . " from " . $pre_value['email'] . " older than 7 days from subscription list.\n";
      }
    }
  }

} else {
  header('HTTP/1.0 301 Moved Permanently');  
  header('Location: /');
}

?>
