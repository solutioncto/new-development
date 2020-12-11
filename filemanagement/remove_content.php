<?php
require_once 'users/init.php';
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
?>

<?php
if(isset($_POST['id'])){
    
    $db = DB::getInstance();

    if($_POST['filename'] != ""){
        $pathArr = explode('/', $_POST['filename']);
		$name = end($pathArr);
        $file_pointer = $abs_us_root.$us_url_root.'uploads/' . $name;
        if (!unlink($file_pointer)) {  
            echo ("$file_pointer cannot be deleted due to an error");  
        }  
        else {  
            if (!$db->deleteById('user_added_content',$_POST['id'])) {
                throw new Exception($db->errorString());
            } else {
                echo "Removed!";
            } 
        } 
    }
    else{
        if (!$db->deleteById('user_added_content',$_POST['id'])) {
            throw new Exception($db->errorString());
        } else {
            echo "Removed!";
        }
    }

     
    
    
}

?>
