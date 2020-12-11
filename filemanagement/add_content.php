<?php
require_once 'users/init.php';
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
?>

<?php
if(isset($_POST['type'])){
    $db = DB::getInstance();
    if($_POST['type'] == "1"){
        if($_FILES["file"]["name"] != '')
        {
            $name = $_FILES["file"]["name"];
            $name = date('YmdHis')."_".$name;
            $location = '/uploads/' . $name;  
            //$location = $abs_us_root.$us_url_root.'uploads/' . $name;
            $success = move_uploaded_file($_FILES["file"]["tmp_name"], $abs_us_root.$us_url_root.'uploads/' . $name);
            if($success)
            {    
                $fields = [
                    'title' => $_POST['title'],
                    'content' => $location,
                    'type_id' => $_POST['type'],
                    'user_id' => $user->data()->id,
                    'updated_date' => date('Y-m-d H:i:s'),
                ];
                if (!$db->insert('user_added_content', $fields)) {
                    throw new Exception($db->errorString());
                } else {
                    echo "Saved!";
                }
            }
            else{
                throw new Exception("File Upload Failed!");
            }
        }
    }
    else if($_POST['type'] == "2"){
        $fields = [
            'title' => $_POST['title'],
            'content' => $_POST['link'],
            'type_id' => $_POST['type'],
            'user_id' => $user->data()->id,
            'updated_date' => date('Y-m-d H:i:s'),
        ];
        if (!$db->insert('user_added_content', $fields)) {
            throw new Exception($db->errorString());
        } else {
            echo "Saved!";
        }
    }
    else if($_POST['type'] == "3"){
        $fields = [
            'title' => $_POST['title'],
            'content' => $_POST['note'],
            'type_id' => $_POST['type'],
            'user_id' => $user->data()->id,
            'updated_date' => date('Y-m-d H:i:s'),
        ];
        if (!$db->insert('user_added_content', $fields)) {
            throw new Exception($db->errorString());
        } else {
            echo "Saved!";
        }
    }
}

?>
