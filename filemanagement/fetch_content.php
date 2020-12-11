<?php
require_once 'users/init.php';
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
?>

<?php
$db = DB::getInstance();
//$table = "user_added_content";
//$where = array('user_id','=',$user->data()->id);
$sql = "SELECT * FROM user_added_content WHERE user_id = ". $user->data()->id . " ORDER BY updated_date DESC";
//$result = $db->get($table, $where);
$result = $db->query($sql);
$user_content = $result->results(true);

$table = "content_types";
$result = $db->findAll($table);
$content_types = $result->results(true);

?>
