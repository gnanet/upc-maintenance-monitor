<?php
// only to include !!!
if ( $_SERVER['PHP_SELF'] == 'header.php' ) {
    exit;
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
