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

include_once(__DIR__.'/functions.php');

$version = 1.0;
$title = "UPC Maintenance Monitor";

$current_folder = get_current_folder();

# timeout in seconds
$timeout = 20;

date_default_timezone_set('Europe/Budapest');

ini_set('default_socket_timeout', 20);

$random_blurp = rand(1000,99999);

$current_domain = "www.do01.r-us.hu";
$current_link = "www.do01.r-us.hu/upc-karb";

// set this to a location outside of your webroot so that it cannot be accessed via the internets.
$datastore = '/var/www/upc-mm-db';
$pre_check_file = $datastore.'/upc_pre_checks.json';
$check_file = $datastore.'/upc_checks.json';
$deleted_check_file = $datastore.'/upc_deleted_checks.json';

touch_json($pre_check_file);
touch_json($check_file);
touch_json($deleted_check_file);

$valid_city_file = '/var/www/default/upc-karb/data/telepulesnevek.json';
