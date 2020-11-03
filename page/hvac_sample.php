<?php
/*
UserSpice 5
An Open Source PHP User Management System
by the UserSpice Team at http://UserSpice.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
require_once 'users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
?>

<h1>HVAC Sample Page</h1>

<?php

/*
    Start of HVAC development
*/

if( $_POST["random_text"])
{
    if ( hvac_sample_plugin_write($_POST["random_text"]) )
    {
        echo"<h2>User Entered Random Content PASS</h2>";
        echo "Random Text: ". $_POST["random_text"]. "<br/>";
    }
}
?>

<?php
$quote = hvac_sample_plugin_read();
echo
'
        <h2>Random Content</h2>
        </br>
        <form method="post">
        Star Wars quote: </br><textarea name="random_text" cols="40" rows="5">'.$quote.'</textarea><br>
        <input type="submit">
        </form>
';

/*
    End of HVAC development
*/

?>

<div class="row">
	<div class="col-sm-12">
	</div>
</div>

<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php'; ?>
