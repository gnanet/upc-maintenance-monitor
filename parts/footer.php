<?php
// only to include !!!
if ( $_SERVER['PHP_SELF'] == 'footer.php' ) {
    exit;
}

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
global $autocomplete;
?>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
<?php
if ( $autocomplete === true ) { ?>
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
<?php } ?>
</body>
</html>
