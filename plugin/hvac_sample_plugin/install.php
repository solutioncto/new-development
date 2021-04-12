<?php
require_once("init.php");
// For security purposes, it is MANDATORY that this page be wrapped in the following
// if statement. This prevents remote execution of this code.
if (in_array($user->data()->id, $master_account)) {

    $db = DB::getInstance();
    include "plugin_info.php";

    // all actions should be performed here.
    $check = $db->query("SELECT * FROM us_plugins WHERE plugin = ?", array($plugin_name))->count();
    if ($check > 0) {
        err($plugin_name.' has already been installed!');
    } else {
        $fields = array(
            'plugin' => $plugin_name,
            'status' => 'installed',
        );
        $db->insert('us_plugins', $fields);
        if (!$db->error()) {
            err($plugin_name.' installed');
            logger($user->data()->id, "USPlugins", $plugin_name." installed");
        } else {
            err($plugin_name.' was not installed');
            logger($user->data()->id, "USPlugins", "Failed to to install plugin, Error: " . $db->errorString());
        }
    }

    // setting table
    // $db->query("ALTER TABLE notifications ADD COLUMN class varchar(100)");

    // plg_uf
    $db->query("DROP TABLE `plg_tbl_uf`;");
    $db->query("CREATE TABLE plg_tbl_uf (
		`id` int(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
		`user_id` int(11) NOT NULL,
		`file_type` varchar(500) NOT NULL,
		`file_name` varchar(500) NOT NULL,
		`file_path` varchar(500) DEFAULT NULL,
		`link` varchar(500) DEFAULT NULL,
		`note` text DEFAULT NULL,
		`created_at` timestamp NOT NULL,
		`updated_at` timestamp NOT NULL
	)");

    // do you want to inject your plugin in the middle of core UserSpice pages?
    $hooks = [];

    // The format is $hooks['userspicepage.php']['position'] = path to filename to include
    // Note you can include the same filename on multiple pages if that makes sense;
    // position options are post,body,form,bottom
    // See documentation for more information
    // $hooks['login.php']['body'] = 'hooks/loginbody.php';
    // $hooks['login.php']['form'] = 'hooks/loginform.php';
    // $hooks['login.php']['bottom'] = 'hooks/loginbottom.php';
    // $hooks['login.php']['post'] = 'hooks/loginpost.php';
    registerHooks($hooks, $plugin_name);

    $publicPages = [
        'admin_user_uploads.php',
        'ajax_users_files.php'
    ];
    foreach ($publicPages as $a) {
        unlink($abs_us_root.$us_url_root."users/".$a);
        copy($abs_us_root.$us_url_root."usersc/plugins/hvac_sample_plugin/files/".$a, $abs_us_root.$us_url_root."users/".$a);
        $check = $db->query("SELECT * FROM pages WHERE page = ?", ['users/' . $a])->count();
        if ($check < 1) {
            $db->insert('pages', ['page' => 'users/'.$a, 'private' => 1]);
            $newId = $db->lastId();
            $db->insert('permission_page_matches', ['permission_id' => 1, 'page_id' => $newId]);
            $db->insert('permission_page_matches', ['permission_id' => 2, 'page_id' => $newId]);
        }
    }
    $sql = "INSERT INTO `menus` (`id`, `menu_title`, `parent`, `dropdown`, `logged_in`, `display_order`, `label`, `link`, `icon_class`) VALUES (NULL, 'main', '-1', '0', '1', '99999', 'Files &amp; Links', 'users/admin_user_uploads.php', '')";
    $db->query($sql);
    $menuId = $db->lastId();

    $sql = "INSERT INTO `groups_menus` (`group_id`, `menu_id`) VALUES (2,$menuId)";
    $db->query($sql);

    $sql = "INSERT INTO `groups_menus` (`group_id`, `menu_id`) VALUES (1,$menuId)";
    $db->query($sql);

} // do not perform actions outside of this statement
