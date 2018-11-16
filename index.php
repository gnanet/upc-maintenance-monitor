<?php

// https://github.com/samacs/simple_html_dom
// https://raw.githubusercontent.com/samacs/simple_html_dom/master/simple_html_dom.php

// https://github.com/aFarkas/remote-list
// https://raw.githubusercontent.com/aFarkas/remote-list/master/dist/remote-list.min.js

// https://www.posta.hu/static/internet/download/Iranyitoszam-Internet_uj.xlsx
// browse to lib/telepulesnevek.php to generate "data/telepulesnevek.json"

// Notification solution based on https://github.com/RaymiiOrg/certificate-expiry-monitor
// Include the HTML DOM parserlibrary
include('lib/simple_html_dom.php');

$url = 'https://www.upc.hu/segithetunk/hasznos-tudnivalok/karbantartasok/';
$karbsave = 'data/upc-karb.html.snip';

if ( ! empty($_POST) ) {
    if ( ( isset($_REQUEST['loc']) ) && ( strlen(htmlentities($_REQUEST['loc'])) > 0 ) ) {
        $searchCity = htmlentities($_REQUEST['loc']);
    } else {
        $searchCity="Szombathely";
    }
    clearstatcache();
    if ( @file_exists($karbsave) &&  ( filemtime($karbsave) > ( time() - (12*3600)) ) ) {
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

    $karbresult =  "<table class='table table-striped' id='searchresults'>\n";
    foreach($html->find('tr') as $el) {
        if ( (strpos($el->find('td',3)->innertext,$searchCity) !== false ) || (strpos($el->innertext,'<th>') !== false ) ) {
            $datetime1 = date_create('@'.time());
            $datetime2 = date_create($el->find('td',0)->innertext);
            $interval = date_diff($datetime1, $datetime2);
            $karbresult .=  str_replace($el->find('td',0)->innertext,$interval->format('%r%a nap %H óra múlva')."<br>".$el->find('td',0)->innertext,str_replace($searchCity,'<b>'.$searchCity.'</b>',$el->outertext));

        }
    }
    $karbresult .= "</table>\n";
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <title>UPC karbantartások</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noarchive"/>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html">
    <meta http-equiv="Cache-Control" content="No-Cache">
    <meta property="og:type" content="website" />
    <meta property="og:title" content="UPC karbantartások">
    <meta property="og:description" content="Kereshető UPC karbantartások">

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>
<body style="padding-top: 70px;">
<nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
<!-- Hamburger bars -->
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
<!-- Hamburger bars end-->
          </button>
          <a class="navbar-brand" href="index.php">UPC Maintenance Monitor</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li><a href="createmonitor.php">Értesítés igénylése</a></li>
            <li><a href="https://www.upc.hu/segithetunk/hasznos-tudnivalok/karbantartasok/" target="_blank">UPC hivatalos karbantartások</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
</nav>
<div class="container">
    <div class="page-header">
        <h1>Karbantartás kereső</h1>
    </div>
    <div class="well">
        <p><b>UPC bejelentett karbantartás kereső használata:</b> az automata kiegészítés segítségével add meg melyik településre keressünk.</p>
        <p>A kereső a hivatalos UPC karbantartások oldal  [<a href="https://www.upc.hu/segithetunk/hasznos-tudnivalok/karbantartasok/" target="_blank">https://www.upc.hu/segithetunk/hasznos-tudnivalok/karbantartasok/</a>] tartalmából a karbantartások táblázatot elemzi, a gyors működés érdekében ideiglenesen eltárolja. A településlistát a posta.hu oldalon elérhető irányítószám táblázatból generáljuk.</p>
        <p>Lehetőséged van az emailcímed és a figyelendő településnév megadásával automatikus értesítő-emailekre feliratkozni, ezt a menüből az <a href="createmonitor.php">Értesítés igénylése</a> pontban kezdeményezheted.</p>
    </div>
  <form id="searchform" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <div class="input-group" style="margin-bottom: 10px;">
        <span id="loc-label" class="input-group-addon">Keresendő város:</span>
        <input type="text" size="50" maxlength="254" name="loc" id="loc" class="form-control" aria-describedby="loc-label" data-remote-list="data/telepulesnevek.json" data-list-highlight="true" data-list-value-completion="true" autocomplete="no" <?php if ( isset($searchCity) ) { echo ' value="'.$searchCity.'" '; }?> />
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Keress</button>
        <button type="reset" value="reset" onclick="clearform()" class="btn btn-danger">Visszaállít</button>
    </div>
  </form>
</div>
<?php if ( isset($karbresult) ) { echo "<div class=\"container\">\n".$karbresult."\n</div>\n"; } ?>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/remote-list.min.js"></script>
<script>
    function clearform() {
        document.getElementById("loc").setAttribute('value', '');
        document.getElementById("searchresults").outerHTML = '';
        document.getElementById("loc").focus();
    };

    $(function () {
        $('input#loc').remoteList({
            minLength: 0,
            maxLength: 0,
            select: function(){
                if(window.console){
                    console.log($(this).remoteList('selectedOption'), $(this).remoteList('selectedData'))
                }
            }
        });
    });
</script>
</body>
</html>
