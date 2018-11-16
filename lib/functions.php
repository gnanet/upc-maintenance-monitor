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

$aboutsvc = "This is a service which monitors the UPC Maintenance website, and notifies you when a maintenance is scheduled for your location. This extra notification helps you remember to prepare for the maintenance on time.\r\n\r\n";

function touch_json($fileparam) {
    if (php_sapi_name() != "cli") {
        error_log("touch_json called with ".$fileparam);
    }
    if ( ! @file_exists($fileparam) ) {
        file_put_contents($fileparam,'');
        error_log("touch_json created ".$fileparam);
        echo "\n<!-- created ".$fileparam." -->\n";
    }
}

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}
function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    if(!empty($haystack)) {
        return $needle === "" || strpos($haystack, $needle, strlen($haystack) - strlen($needle)) !== FALSE;
    }
}

function get_current_folder(){
    $url = $_SERVER['REQUEST_URI'];
    $parts = explode('/',$url);
    $folder = '';
    for ($i = 0; $i < count($parts) - 1; $i++) {
        $folder .= $parts[$i] . "/";
    }
    return $folder;
}

function gen_uuid() {
  return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    // 32 bits for "time_low"
    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

    // 16 bits for "time_mid"
    mt_rand( 0, 0xffff ),

    // 16 bits for "time_hi_and_version",
    // four most significant bits holds version number 4
    mt_rand( 0, 0x0fff ) | 0x4000,

    // 16 bits, 8 bits for "clk_seq_hi_res",
    // 8 bits for "clk_seq_low",
    // two most significant bits holds zero and one for variant DCE1.1
    mt_rand( 0, 0x3fff ) | 0x8000,

    // 48 bits for "node"
    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
  );
}

function bcdechex($dec) {
    $hex = '';
    do {
        $last = bcmod($dec, 16);
        $hex = dechex($last).$hex;
        $dec = bcdiv(bcsub($dec, $last), 16);
    } while($dec>0);
        return $hex;
}


function validate_email($email) {
  if (!filter_var(mb_strtolower($email), FILTER_VALIDATE_EMAIL)) {
    return false;
  } else {
    return true;
  }
}

function send_error_mail($varos, $email, $errors) {
  echo "\t\tSending error mail to $email for $varos.\n";
  global $aboutsvc;
  global $current_domain;
  global $current_link;
  global $check_file;
  $varos = trim($varos);
  $errors = implode("\r\n", $errors);
  $json_file = file_get_contents($check_file);
  if ($check_file === FALSE) {
      echo "\t\tCan't open database.\n";
      return false;
  }
  $json_a = json_decode($json_file, true);
  if ($json_a === NULL || json_last_error() !== JSON_ERROR_NONE) {
      echo "\t\tCan't read database.\n";
      return false;
  }

  foreach ($json_a as $key => $value) {
    if ($value["varos"] == $varos && $value["email"] == $email) {
      $id = $key;
      $failures = $value['errors'];
      $unsublink = "https://" . $current_link . "/unsubscribe.php?id=" . $id;
      $to      = $email;
      $subject = "Certificate monitor " . htmlspecialchars($varos) . " failed.";
      $message = "Hello,\r\n\r\nYou have a subscription to monitor the maintenance schedules of " . htmlspecialchars($varos) . " with the the UPC Maintenance Monitor. ".$aboutsvc."We've noticed that the check for the following city has failed: \r\n\r\ncity: " . htmlspecialchars($varos) . "\r\nError(s): " . htmlspecialchars($errors) . "\r\n\r\nFailure(s): " . htmlspecialchars($failures) . "\r\n\r\nPlease check the UPC website directly. If the check fails 7 times we will remove it from our monitoring. If the check succeeds again within 7 failures, the failure count will reset.\r\n\r\nTo unsubscribe from notifications for this city please click or copy and paste the below link in your browser:\r\n\r\n" . $unsublink . "\r\n\r\n\r\n Have a nice day,\r\nThe UPC Maintenance Monitor Service.\r\nhttps://" . $current_link . "";
      $message = wordwrap($message, 70, "\r\n");
      $headers = 'From: noreply@' . $current_domain . "\r\n" .
          'Reply-To: noreply@' . $current_domain . "\r\n" .
          'Return-Path: noreply@' . $current_domain . "\r\n" .
          'X-Visitor-IP: ' . $visitor_ip . "\r\n" .
          'X-Coffee: Black' . "\r\n" .
          'List-Unsubscribe: <https://' . $current_link . "/unsubscribe.php?id=" . $id . ">" . "\r\n" .
          'X-Mailer: PHP/7.2.9';

      if (mail($to, $subject, $message, $headers) === true) {
          echo "\t\tEmail sent to $to.\n";
          return true;
      } else {
          echo "\t\tCan't send email.\n";
          return false;
      }
    }
  }
}


function maintenance_notification_emails($varos, $email, $maintenance) {
$time_to_maintenance = $maintenance['start'] - time();
    switch ($time_to_maintenance) {
      case (($time_to_maintenance > 7776000) && ($time_to_maintenance < 7776000+86400)):
        # 90 days...
        send_maintenance_in_email(90, $varos, $email, $maintenance);
        break;
      case (($time_to_maintenance > 5184000) && ($time_to_maintenance < 5184000+86400)):
        # 60 days...
        send_maintenance_in_email(60, $varos, $email, $maintenance);
        break;
      case (($time_to_maintenance > 2592000) && ($time_to_maintenance < 2592000+86400)):
        # 30 days...
        send_maintenance_in_email(30, $varos, $email, $maintenance);
        break;
      case (($time_to_maintenance > 1209600) && ($time_to_maintenance < 1209600+86400)):
        # 14 days...
        send_maintenance_in_email(14, $varos, $email, $maintenance);
        break;
      case (($time_to_maintenance > 604800) && ($time_to_maintenance < 604800+86400)):
        # 7 days...
        send_maintenance_in_email(7, $varos, $email, $maintenance);
        break;
      case (($time_to_maintenance > 432000) && ($time_to_maintenance < 432000+86400)):
        # 5 days...
        send_maintenance_in_email(5, $varos, $email, $maintenance);
        break;
      case (($time_to_maintenance > 259200) && ($time_to_maintenance < 259200+86400)):
        # 3 days...
        send_maintenance_in_email(3, $varos, $email, $maintenance);
        break;
      case (($time_to_maintenance > 172800) && ($time_to_maintenance < 172800+86400)):
        # 2 days...
        send_maintenance_in_email(2, $varos, $email, $maintenance);
        break;
      case (($time_to_maintenance > 86400) && ($time_to_maintenance < 86400+86400)):
        # 1 days...
        send_maintenance_in_email(1, $varos, $email, $maintenance);
        break;
      case ($time_to_maintenance < 86400):
        # 0 days...
        send_maintenance_in_email(0, $varos, $email, $maintenance);
        break;
    }

}

function send_maintenance_in_email($days, $varos, $email, $maintenance) {
  global $aboutsvc;
  global $current_domain;
  global $current_link;
  global $check_file;
  $varos = trim($varos);
  echo "\t\tcity " . $varos . " is scheduled for maintenance in " . $days . " days.\n";

  $file = file_get_contents($check_file);
  if ($file === FALSE) {
      echo "\t\tCan't open database.\n";
      return false;
  }
  $json_a = json_decode($file, true);
  if ($json_a === null && json_last_error() !== JSON_ERROR_NONE) {
      echo "\t\tCan't read database.\n";
      return false;
  }

  foreach ($json_a as $key => $value) {

    if ($value["varos"] == $varos && $value["email"] == $email) {

      $id = $key;

      $unsublink = "https://" . $current_link . "/unsubscribe.php?id=" . $id;

      $to      = $email;
      $subject = "UPC Maintenance for " . htmlspecialchars($varos) . " is scheduled in " . htmlspecialchars($days) . " days";
      $message = html_entity_decode("Hello,\r\n\r\nMaintenance scheduled for " . $maintenance['location'] . ".\r\nStart date: " . date("Y-m-d H:i:s T", $maintenance['start']) . ".\r\nAffected services: " . $maintenance['type'] . " " .$maintenance['services']. "\r\n");
      $message .= "\r\nYou have a subscription to monitor the maintenance schedule of " . htmlspecialchars($varos) . " with the the UPC Maintenance Monitor. ".$aboutsvc."\r\n\r\nTo unsubscribe from notifications for this city please click or copy and paste the below link in your browser:\r\n\r\n" . $unsublink . "\r\n\r\n\r\n Have a nice day,\r\nThe UPC Maintenance Monitor Service.\r\nhttps://" . $current_link . "";
      $message = wordwrap($message, 70, "\r\n");
      $headers = 'From: noreply@' . $current_domain . "\r\n" .
          'Reply-To: noreply@' . $current_domain . "\r\n" .
          'Return-Path: noreply@' . $current_domain . "\r\n" .
          'X-Visitor-IP: ' . $visitor_ip . "\r\n" .
          'X-Coffee: Black' . "\r\n" .
          'List-Unsubscribe: <https://' . $current_link . "/unsubscribe.php?id=" . $id . ">" . "\r\n" .
          'X-Mailer: PHP/7.2.9';

      if (mail($to, $subject, $message, $headers) === true) {
          echo "\t\tEmail sent to $to.\n";
          return true;
      } else {
          echo "\t\tCan't send email.\n";
          return false;
      }
    } 
  }
}


function add_varos_to_pre_check($varos,$email,$visitor_ip) {
    global $aboutsvc;
    global $current_domain;
    global $current_link;
    global $pre_check_file;
    global $check_file;
    global $valid_city_file;
    $result = array();
    $varos = trim($varos);
    $email = trim($email);

    touch_json($pre_check_file);
    touch_json($check_file);

    $cityfile = file_get_contents($valid_city_file);
    if ($cityfile === FALSE) {
        $result['errors'][] = "Can't open city database.";
        return $result;
    }
    $json_cities = json_decode($cityfile, true);
    if ($json_cities === null && json_last_error() !== JSON_ERROR_NONE) {
        $result['errors'][] = "Can't read city database: " . htmlspecialchars(json_last_error());
        return $result;
    }

    foreach ($json_cities as $assoc_city) {
        $city_array[] = $assoc_city['value'];
    }

    if ( ! in_array($varos, $city_array)) {
        $result['errors'][] = "City could not be validated";
        return $result;
    }

    $file = file_get_contents($pre_check_file);
    if ($file === FALSE) {
        $result['errors'][] = "Can't open database.";
        return $result;
    }
    $json_a = json_decode($file, true);
    if ($json_a === null && json_last_error() !== JSON_ERROR_NONE) {
        $result['errors'][] = "Can't read database: " . htmlspecialchars(json_last_error());
        return $result;
    }

    foreach ($json_a as $key => $value) {
        if ($value["varos"] == $varos && $value["email"] == $email) {
            $result['errors'][] = "Város / email combo for  " . htmlspecialchars($varos) . " already exists. Please confirm your subscription email.";
            return $result;
        }
    }

    $check_json_file = file_get_contents($check_file);
    if ($check_json_file === FALSE) {
        $result['errors'][] = "Can't open database.";
        return $result;
    }
    $check_json_a = json_decode($check_json_file, true);
    if ($check_json_a === null && json_last_error() !== JSON_ERROR_NONE) {
        $result['errors'][] = "Can't read database: " . htmlspecialchars(json_last_error());
        return $result;
    }

    foreach ($check_json_a as $key => $value) {
        if ($value["varos"] == $varos && $value["email"] == $email) {
            $result['errors'][] = "Város / email combo for  " . htmlspecialchars($varos) . " already exists.";
            return $result;
        }
    }

    $uuid = gen_uuid();

    $json_a[$uuid] = array("varos" => $varos,
        "email" => $email,
        "visitor_pre_register_ip" => $visitor_ip,
        "pre_add_date" => time());

    $json = json_encode($json_a); 
    if(file_put_contents($pre_check_file, $json, LOCK_EX)) {
        $result['success'][] = true;
    } else {
        $result['errors'][] = "Can't write database.";
        return $result;
    }

    $sublink = "https://" . $current_link . "/confirm.php?id=" . $uuid;

    $to      = $email;
    $subject = "Confirm your UPC Maintenance Monitor subscription for " . htmlspecialchars($varos) . ".";
    $message = "Hello,\r\n\r\nSomeone, hopefully you, has added his city to the  UPC Maintenance Monitor. ".$aboutsvc."If you have subscribed to this check, please click the link below to confirm this subscription. If you haven't subscribed to the UPC Maintenance Monitor service, please consider this message as not sent.\r\n\r\n\r\nCity: " . trim(htmlspecialchars($varos)) . "\r\nEmail: " . trim(htmlspecialchars($email)) . "\r\nIP subscribed from: " . htmlspecialchars($visitor_ip) . "\r\nDate subscribed: " . date("Y-m-d H:i:s T") . "\r\n\r\nPlease click or copy and paste the below link in your browser to subscribe: \r\n\r\n" . $sublink . "\r\n\r\n\r\nHave a nice day,\r\nThe UPC Maintenance Monitor Service.";
    $message = wordwrap($message, 70, "\r\n");
    $headers = 'From: noreply@' . $current_domain . "\r\n" .
        'Reply-To: noreply@' . $current_domain . "\r\n" .
        'Return-Path: noreply@' . $current_domain . "\r\n" .
        'X-Visitor-IP: ' . $visitor_ip . "\r\n" .
        'X-Coffee: Black' . "\r\n" .
        'List-Unsubscribe: <https://' . $current_link . "/unsubscribe.php?id=" . $uuid . ">" . "\r\n" .
        'X-Mailer: PHP/7.2.9';


    if (mail($to, $subject, $message, $headers) === true) {
        $result['success'][] = true;
    } else {
        $result['errors'][] = "Can't send email.";
        return $result;
    }

    return $result;
}


function add_varos_check($id,$visitor_ip) {
    global $aboutsvc;
    global $current_domain;
    global $current_link;
    global $pre_check_file;
    global $check_file;
    $result = array();

    $pre_check_json_file = file_get_contents($pre_check_file);
    if ($file === FALSE) {
        $result['errors'][] = "Can't open database.";
        return $result;
    }
    $pre_check_json_a = json_decode($pre_check_json_file, true);
    if ($pre_check_json_a === null && json_last_error() !== JSON_ERROR_NONE) {
        $result['errors'][] = "Can't read database: " . htmlspecialchars(json_last_error());
        return $result;
    }

    if (!is_array($pre_check_json_a[$id]) ) {
      $result['errors'][] = "Can't find record in database for: " . htmlspecialchars($id);
        return $result;
    }

    $file = file_get_contents($check_file);
    if ($file === FALSE) {
        $result['errors'][] = "Can't open database.";
        return $result;
    }
    $json_a = json_decode($file, true);
    if ($json_a === null && json_last_error() !== JSON_ERROR_NONE) {
        $result['errors'][] = "Can't read database: " . htmlspecialchars(json_last_error());
        return $result;
    }

    foreach ($json_a as $key => $value) {
      if ($key == $id) {
          $result['errors'][] = "Város / email combo for  " . htmlspecialchars($pre_check_json_a[$id]['varos']) . " already exists.";
          return $result;
      }
      if ($value["varos"] == $pre_check_json_a[$id]['varos'] && $value["email"] == $pre_check_json_a[$id]['email']) {
          $result['errors'][] = "Város / email combo for  " . htmlspecialchars($pre_check_json_a[$id]['varos']) . " already exists.";
          return $result;
      }
    }

    $json_a[$id] = array("varos" => $pre_check_json_a[$id]['varos'],
        "email" => $pre_check_json_a[$id]['email'],
        "errors" => 0,
        "visitor_pre_register_ip" => $pre_check_json_a[$id]['visitor_pre_register_ip'],
        "pre_add_date" => $pre_check_json_a[$id]['pre_add_date'],
        "visitor_confirm_ip" => $visitor_ip,
        "confirm_date" => time());

    $json = json_encode($json_a);
    if(file_put_contents($check_file, $json, LOCK_EX)) {
        $result['success'][] = true;
    } else {
        $result['errors'][] = "Can't write database.";
        return $result;
    }

    unset($pre_check_json_a[$id]);
    $pre_check_json = json_encode($pre_check_json_a);
    if(file_put_contents($pre_check_file, $pre_check_json, LOCK_EX)) {
        $result['success'][] = true;
    } else {
        $result['errors'][] = "Can't write database.";
        return $result;
    }

    $unsublink = "https://" . $current_link . "/unsubscribe.php?id=" . $id;

    $to      = $json_a[$id]['email'];
    $subject = "UPC Maintenance Monitor subscription confirmed for " . htmlspecialchars($json_a[$id]['varos']) . ".";
    $message = "Hello,

Someone, hopefully you, has confirmed the subscription of their website to the UPC Maintenance Monitor. ".$aboutsvc."

City   : " . trim(htmlspecialchars($json_a[$id]['varos'])) . "
Email  : " . trim(htmlspecialchars($json_a[$id]['email'])) . "
IP subscription confirmed from: " . htmlspecialchars($visitor_ip) . "
Date subscribed confirmed: " . date("Y-m-d H:i:s T") . "

We will monitor the maintenance list for your city. You will receive emails when a maintenance is scheduled.

To unsubscribe from notifications for the maintenance schedule please click or copy and paste the below link in your browser:

  " . $unsublink . "

Have a nice day,
The UPC Maintenance Monitor Service.
https://" . $current_link . "";
    $message = wordwrap($message, 70, "\r\n");
    $headers = 'From: noreply@' . $current_domain . "\r\n" .
        'Reply-To: noreply@' . $current_domain . "\r\n" .
        'Return-Path: noreply@' . $current_domain . "\r\n" .
        'X-Visitor-IP: ' . $visitor_ip . "\r\n" .
        'X-Coffee: Black' . "\r\n" .
        'List-Unsubscribe: <https://' . $current_link . "/unsubscribe.php?id=" . $id . ">" . "\r\n" .
        'X-Mailer: PHP/7.2.9';



    if (mail($to, $subject, $message, $headers) === true) {
        $result['success'][] = true;
    } else {
        $result['errors'][] = "Can't send email.";
        return $result;
    }

    return $result;
}
