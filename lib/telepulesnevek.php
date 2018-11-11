<?php
// https://github.com/shuchkin/simplexlsx
// https://github.com/shuchkin/simplexlsx/raw/master/src/SimpleXLSX.php


$postalive = 'https://www.posta.hu/static/internet/download/Iranyitoszam-Internet_uj.xlsx';
$postaxlsx = '../data/Iranyitoszam-Internet_uj.xlsx';
$telepulesfile = '../data/telepulesnevek.json';


include('SimpleXLSX.php');
clearstatcache();

$xlsxlive = new SimpleXLSX(file_get_contents($postalive), true);
$livepreg = preg_match('/([0-9\-T:]*)Z..dcterms.modified/', $xlsxlive->getEntryData('docProps/core.xml'), $livemtime);

if ( @file_exists($postaxlsx) && ( $xlsxlocal = SimpleXLSX::parse($postaxlsx)) ) {
    $xlsxpreg = preg_match('/([0-9\-T:]*)Z..dcterms.modified/', (string)$xlsxlocal->getEntryData('docProps/core.xml'), $xlsxmtime);
} else {
    $xlsxmtime[1] = '1970-01-01T00:00:00';
}

if ( (!empty($livemtime[1])) && (!empty($xlsxmtime[1])) ) {
    // echo "<pre> Live Mtime: ".print_r($livemtime[1],true) ."</pre>";
    $livestamp=strtotime($livemtime[1]);
    // echo "<pre> Local Mtime: ".print_r($xlsxmtime[1],true) ."</pre>";
    $xlsxstamp=strtotime($xlsxmtime[1]);
}

if ( ( $livestamp > $xlsxstamp ) || ( ! @file_exists($telepulesfile)) ) {
    echo "<pre>Live xlsx downloading</pre>\n";
    file_put_contents(file_get_contents($postalive),$postaxlsx);
}


if ( @file_exists($postaxlsx) && ( $xlsx = SimpleXLSX::parse($postaxlsx)) ) {
    $telepulesarray = array();
    list( $num_cols, $num_rows ) = $xlsx->dimension( 0 );

	foreach ( $xlsx->rows( 0 ) as $r ) {
        if ( trim($r[1]) == 'Település' ) { continue; }
            if ( (!empty(trim($r[1]))) && empty(trim($r[2])) ) {
                $telepulesarray[] = array( "value" => trim($r[1]));
            } else if ( (!empty(trim($r[1]))) &&  (!empty(trim($r[2]))) ) {
                $telepulesarray[] = array( "value" => trim($r[2]));
            }
	}

$telepulesarray[] = array( "value" => "Budapest");
$telepulesarray[] = array( "value" => "Budapest 01.ker");
$telepulesarray[] = array( "value" => "Budapest 02.ker");
$telepulesarray[] = array( "value" => "Budapest 03.ker");
$telepulesarray[] = array( "value" => "Budapest 04.ker");
$telepulesarray[] = array( "value" => "Budapest 05.ker");
$telepulesarray[] = array( "value" => "Budapest 06.ker");
$telepulesarray[] = array( "value" => "Budapest 07.ker");
$telepulesarray[] = array( "value" => "Budapest 08.ker");
$telepulesarray[] = array( "value" => "Budapest 09.ker");
$telepulesarray[] = array( "value" => "Budapest 10.ker");
$telepulesarray[] = array( "value" => "Budapest 11.ker");
$telepulesarray[] = array( "value" => "Budapest 12.ker");
$telepulesarray[] = array( "value" => "Budapest 13.ker");
$telepulesarray[] = array( "value" => "Budapest 14.ker");
$telepulesarray[] = array( "value" => "Budapest 15.ker");
$telepulesarray[] = array( "value" => "Budapest 16.ker");
$telepulesarray[] = array( "value" => "Budapest 17.ker");
$telepulesarray[] = array( "value" => "Budapest 18.ker");
$telepulesarray[] = array( "value" => "Budapest 19.ker");
$telepulesarray[] = array( "value" => "Budapest 20.ker");
$telepulesarray[] = array( "value" => "Budapest 21.ker");
$telepulesarray[] = array( "value" => "Budapest 22.ker");
    $telepulesjson = json_encode($telepulesarray,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    file_put_contents($telepulesfile,$telepulesjson);
    echo "<pre>\nJSON generated: ".$telepulesfile;
    // echo $telepulesjson;
    echo "\n</pre>\n";





} else {
	echo SimpleXLSX::parseError();
}
