<?php
require_once("init.php");

//For security purposes, it is MANDATORY that this page be wrapped in the following
//if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)) {
    $db = DB::getInstance();
    include "plugin_info.php";

    //all actions should be performed here.
    //you will probably be doing more than removing the item from the db

    $db->query("DELETE FROM us_plugins WHERE plugin = ?",array($plugin_name));
    deRegisterHooks($plugin_name);

    $publicPages = [
        'admin_user_uploads.php',
        'ajax_users_files.php'
    ];
    foreach($publicPages as $a){
        unlink($abs_us_root.$us_url_root."users/".$a);
        $check      = $db->query("SELECT * FROM pages WHERE page = ?",['users/'.$a]);
        $pageInfo   = $check->results();
        $check      = $check->count();

        if($check > 0){
            $db->delete('pages',['page'=>'users/'.$a,'private'=>1]);
            $db->delete('permission_page_matches',['permission_id'=>1,'page_id'=>$pageInfo->id]);
            $db->delete('permission_page_matches',['permission_id'=>2,'page_id'=>$pageInfo->id]);
        }
    }

    $sql = "SELECT * FROM `menus` WHERE `menus`.`link` = 'users/admin_user_uploads.php'";
    $menuInfo = $db->query($sql);
    $menuInfo = $menuInfo->results();
    $menuId   = isset($menuInfo[0]->id) ? $menuInfo[0]->id : '';

    if($menuId != ''){
        $sql = "DELETE FROM `menus` WHERE `menus`.`link` = 'users/admin_user_uploads.php'";
        $db->query($sql);
    }

    $sql = "DELETE FROM `groups_menus` WHERE `menu_id` = $menuId";
    $db->query($sql);

    if(!$db->error()) {
        err($plugin_name.' uninstalled');
        logger($user->data()->id,"USPlugins", $plugin_name. " uninstalled");
    } else {
        err($plugin_name.' was not uninstalled');
        logger($user->data()->id,"USPlugins","Failed to uninstall Plugin, Error: ".$db->errorString());
    }
} //do not perform actions outside of this statement
