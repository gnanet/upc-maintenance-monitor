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
        <h1>Karbantartás figyelő</h1>
    </div>
    <div class="well">
        <p>UPC karbantartási értesítő beállításához az automata kiegészítés segítségével add meg melyik településre vonatkozó karbantartásokról szeretnél értesítést, és add meg az emailcímed. Egy aktiváló linket küldünk először, így biztosítva az emailcímed helyességét, és hogy megerősítsd az értesítés igénylését.</p>
    </div>
  <form class="form-horizontal" action="addmonitor.php" method="POST">
    <fieldset>
    <div class="input-group" style="margin-bottom: 10px;">
        <span id="loc-label" class="input-group-addon">Figyelendő város:</span>
        <input type="text" required="true" size="50" maxlength="254" name="varos" id="loc" class="form-control" aria-describedby="loc-label" data-remote-list="data/telepulesnevek.json" data-list-highlight="true" data-list-value-completion="true" autocomplete="no" />
    </div>
    <div class="input-group" style="margin-bottom: 10px;">
        <span id="email-label" class="input-group-addon">Értesítési E-mailcím:</span>
        <input type="email" required="true" size="50" maxlength="254" name="email" id="email" class="form-control" aria-describedby="email-label" autocomplete="no" />
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary">Ment</button>
    </div>
    </fieldset>
  </form>
</div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/remote-list.min.js"></script>
<script>
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
