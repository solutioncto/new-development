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

            $pathArr = explode('/', $_POST['prevfile']);
            $pname = end($pathArr);
            $file_pointer = $abs_us_root.$us_url_root.'uploads/' . $pname;
            if (!unlink($file_pointer)) {  
                echo ("Previous file $file_pointer cannot be deleted due to an error");  
            }  
            else {  
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
                        'updated_date' => date('Y-m-d H:i:s'),
                    ];
                    if (!$db->update('user_added_content', $_POST['id'], $fields)) {
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
    }
    else if($_POST['type'] == "2"){
        $fields = [
            'title' => $_POST['title'],
            'content' => $_POST['link'],
            'updated_date' => date('Y-m-d H:i:s'),
        ];
        if (!$db->update('user_added_content', $_POST['id'], $fields)) {
            throw new Exception($db->errorString());
        } else {
            echo "Saved!";
        }
    }
    else if($_POST['type'] == "3"){
        $fields = [
            'title' => $_POST['title'],
            'content' => $_POST['note'],
            'updated_date' => date('Y-m-d H:i:s'),
        ];
        if (!$db->update('user_added_content', $_POST['id'], $fields)) {
            throw new Exception($db->errorString());
        } else {
            echo "Saved!";
        }
    }
}

?>
