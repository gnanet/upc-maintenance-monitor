<!-- "createmonitor.php" -->
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

