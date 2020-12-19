<?php
// This is a user-facing page
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
require_once '../users/init.php';
// // require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
$db = DB::getInstance();
$settings = $db->query('SELECT * FROM settings')->first();
if (!securePage($_SERVER['PHP_SELF'])) {
    die();
}
$hooks =  getMyHooks();
includeHook($hooks, 'pre');
//dealing with if the user is logged in
if ($user->isLoggedIn() && !checkMenu(2, $user->data()->id)) {
    if (($settings->site_offline==1) && (!in_array($user->data()->id, $master_account)) && ($currentPage != 'login.php') && ($currentPage != 'maintenance.php')) {
        $user->logout();
        Redirect::to($us_url_root.'users/maintenance.php');
    }
}


$emailQ = $db->query("SELECT * FROM email");
$emailR = $emailQ->first();

//PHP Goes Here!
$errors=[];
$successes=[];
$userId = $user->data()->id;
$adminUserTrue = $db->query("SELECT * FROM `user_permission_matches` WHERE user_id = $userId AND permission_id = 2;")->count();
$adminUserTrue = $adminUserTrue > 0 ? true : false;
$grav = get_gravatar(strtolower(trim($user->data()->email)));
$validation = new Validate();
$userdetails=$user->data();
//Temporary Success Message
$holdover = Input::get('success');
if ($holdover == 'true') {
    bold("Account Updated");
}
// error_reporting(E_ALL);
$formType = isset($_POST['formType']) ? $_POST['formType'] : '';
$fileUploaded = false;
$totalRecords = [];
$arr = [];
error_reporting(E_ALL);
if ($formType == 'dataTable') {
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    // if ($columnName === 'phoneNumber') {
    //     $columnName = 'CONCAT(cb.bookingPhoneCode,cb.bookingPhoneNumber)';
    // }

    $searchQuery = " ";
    $filterQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND ( file_type like '%".$searchValue."%' OR link like  '%".$searchValue."%' OR note like '%".$searchValue."%' OR created_at like '%".$searchValue."%' ) ";
    }

    $fBookingNo  = isset($_REQUEST['fBookingNo']) ? $_REQUEST['fBookingNo'] : '';

    if ($fBookingNo) {
        $filterQuery .= " AND vBookingNo = '$fBookingNo'";
    }

    $userWhere = '';
    if (!$adminUserTrue) {
        if ( isset($userId) && !empty($userId) ) {
            $userWhere = " AND user_id = $userId  ";
        }
    }
    $data           = $db->query('SELECT count(id) AS all_count FROM plg_tbl_uf');
    $totalRecords   = $data->count();

    $sql            = "SELECT * FROM plg_tbl_uf WHERE 1=1 ". $userWhere ." " . $searchQuery . " " . $filterQuery . " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;
    $data           = $db->query($sql);
    $filterRecords  = $data->count();
    $results        = $data->results();

    $arr = [];
    $i = 0;
    $serial = 1;
    $actions = '';
    foreach ($results as $result) {
        $user_file_id = isset($result->id) ? $result->id : '';
        $linkAndDocument = '';
        if (isset($result->link) && !empty($result->link)) {
            $linkAndDocument = '<div class="text-center"><a class="btn btn-primary btn-sm" target="_blank" href="'.$result->link.'"><i class="fa fa-external-link" aria-hidden="true"></i></a></div>';
        } elseif (isset($result->file_path) && !empty($result->file_path)) {
            $linkAndDocument = '<div class="text-center"><a class="btn btn-primary btn-sm" target="_blank" href="'.$result->file_path.'"><i class="fa fa-file" aria-hidden="true"></i></a></div>';
        }
        $note = '';
        if (isset($result->note) && !empty($result->note)) {
            $note = '<div class="">'.$result->note.'</div>';
        }
        $actions = '<div class="fa-hover">
                      <button onclick="editNote('.$user_file_id.')" class="btn btn-primary btn-sm"><i class="fa fa-pencil-square-o fa-2"></i></button>
                      <button onclick="deleteNote('.$user_file_id.')" class="btn btn-primary btn-sm"><i class="fa fa-trash fa-2"></i></button>
                    </div>';

        $arr[$i]['serial']      = $serial++;
        $arr[$i]['file_type']   = isset($result->file_type) ? $result->file_type : '';
        $arr[$i]['file_name']   = isset($result->file_name) ? $result->file_name : '';
        $arr[$i]['link']        = $linkAndDocument;
        $arr[$i]['note']        = $note;
        $arr[$i]['created_at']  = isset($result->created_at) ? $result->created_at : '';
        $arr[$i]['actions']     = $actions;
        $i++;
    }

    $response = [
        "draw"                  => intval($draw),
        "iTotalRecords"         => $totalRecords,
        "iTotalDisplayRecords"  => $filterRecords,
        'data'                  => $arr
    ];
    echo json_encode($response);
    exit();
} else if ($formType == 'adminDataTable' && false) {
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value

    // if ($columnName === 'phoneNumber') {
    //     $columnName = 'CONCAT(cb.bookingPhoneCode,cb.bookingPhoneNumber)';
    // }

    $searchQuery = " ";
    $filterQuery = " ";
    if ($searchValue != '') {
        $searchQuery = " AND ( file_type like '%".$searchValue."%' OR link like  '%".$searchValue."%' OR note like '%".$searchValue."%' OR created_at like '%".$searchValue."%' ) ";
    }

    $fBookingNo  = isset($_REQUEST['fBookingNo']) ? $_REQUEST['fBookingNo'] : '';

    if ($fBookingNo) {
        $filterQuery .= " AND vBookingNo = '$fBookingNo'";
    }

    // $userWhere = '';
    // if(!empty($userId)){
    //     $userWhere = " AND user_id = $userId  ";
    // }
    $data           = $db->query('SELECT count(id) AS all_count FROM plg_tbl_uf');
    $totalRecords   = $data->count();

    $sql            = "SELECT * FROM plg_tbl_uf WHERE 1=1 " . $searchQuery . " " . $filterQuery . " order by " . $columnName . " " . $columnSortOrder . " limit " . $row . "," . $rowperpage;
    $data           = $db->query($sql);
    $filterRecords  = $data->count();
    $results        = $data->results();

    $arr = [];
    $i = 0;
    $serial = 1;
    $actions = '';
    foreach ($results as $result) {
        $user_file_id = isset($result->id) ? $result->id : '';
        $linkAndDocument = '';
        if (isset($result->link) && !empty($result->link)) {
            $linkAndDocument = '<div class="text-center"><a class="btn btn-primary btn-sm" target="_blank" href="'.$result->link.'"><i class="fa fa-external-link" aria-hidden="true"></i></a></div>';
        } elseif (isset($result->file_path) && !empty($result->file_path)) {
            $linkAndDocument = '<div class="text-center"><a class="btn btn-primary btn-sm" target="_blank" href="'.$result->file_path.'"><i class="fa fa-file" aria-hidden="true"></i></a></div>';
        }
        $note = '';
        if (isset($result->note) && !empty($result->note)) {
            $note = '<div class="">'.$result->note.'</div>';
        }
        $actions = '<div class="fa-hover">
                      <button onclick="editNote('.$user_file_id.')" class="btn btn-primary btn-sm"><i class="fa fa-pencil-square-o fa-2"></i></button>
                      <button onclick="deleteNote('.$user_file_id.')" class="btn btn-primary btn-sm"><i class="fa fa-trash fa-2"></i></button>
                    </div>';

        $arr[$i]['serial']      = $serial++;
        $arr[$i]['file_type']   = isset($result->file_type) ? $result->file_type : '';
        $arr[$i]['file_name']   = isset($result->file_name) ? $result->file_name : '';
        $arr[$i]['link']        = $linkAndDocument;
        $arr[$i]['note']        = $note;
        $arr[$i]['created_at']  = isset($result->created_at) ? $result->created_at : '';
        if(!$adminUserTrue){
            $arr[$i]['actions']     = $actions;
        }
        $i++;
    }

    $response = [
        "draw"                  => intval($draw),
        "iTotalRecords"         => $totalRecords,
        "iTotalDisplayRecords"  => $filterRecords,
        'data'                  => $arr
    ];
    echo json_encode($response);
    exit();
} elseif ($formType == 'addNote') {
    if (!empty($_POST)) {
        $token = $_POST['csrf'];
        if (!Token::check($token)) {
            include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
        } else {
            includeHook($hooks, 'post');
            $user_file_id   = isset($_REQUEST["user_file_id"]) ? $_REQUEST["user_file_id"] : 0;
            $userId         = isset($_REQUEST["user_id"]) && !empty($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : $userId;
            $name           = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
            $type           = isset($_REQUEST["type"]) ? $_REQUEST["type"] : '';
            $note           = isset($_REQUEST["note"]) ? $_REQUEST["note"] : '';
            $url            = isset($_REQUEST["url"]) ? $_REQUEST["url"] : '';
            $document       = isset($_REQUEST["document"]) ? $_REQUEST["document"] : '';
            $dest_path      = '';

            if (count($_FILES['document']) > 0 && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath    = $_FILES['document']['tmp_name'];
                $fileName       = $_FILES['document']['name'];
                $fileSize       = $_FILES['document']['size'];
                $fileType       = $_FILES['document']['type'];
                $fileNameCmps   = explode(".", $fileName);
                $fileExtension  = strtolower(end($fileNameCmps));
                $newFileName    = md5(time() . $fileName) . '.' . $fileExtension;
                // $allowedfileExtensions = array('pdf');
                // if (in_array($fileExtension, $allowedfileExtensions)) {
                // }
                // directory in which the uploaded file will be moved

                $uploadFileDir = '../uploads/' . $userId;
                if (!file_exists('../uploads/')) {
                    mkdir('../uploads/', 0777, true);
                }
                if (!file_exists('../uploads/' . $userId)) {
                    mkdir('../uploads/' . $userId, 0777, true);
                }
                $dest_path = $uploadFileDir .'/'. $newFileName;

                $fileUploaded = move_uploaded_file($fileTmpPath, $dest_path);
                // if(move_uploaded_file($fileTmpPath, $dest_path)) {
                //     $message ='File is successfully uploaded.';
                // } else {
                //     $message = 'There was some error moving the file to upload directory. Please make sure the upload directory is writable by web server.';
                // }
            }
            $currentDate = date('Y-m-d H:i:s');
            $userFilesArr = [
                'file_type'     => $type,
                'user_id'       => $userId,
                'file_name'     => $name,
                'file_path'     => $dest_path,
                'link'          => $url,
                'note'          => $note,
                'created_at'    => date('Y-m-d H:i:s'),
            ];
            // dnd($userFilesArr);
            // $query = "INSERT INTO `plg_tbl_uf` (`file_type`, `file_name`, `file_path`, `link`, `note`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,     'sadasd', 'asdasd', 'asdasd', 'asdasdasdasd');";
            // $userUploads = $db->query($query);
            if ($fileUploaded || ($type = 'Link' || $type = 'Text')) {
                if ($user_file_id > 0) {
                    // $sql = 'UPDATE SET `file_type`= '.$type.',`user_id` = '.$userId.', `file_name`= '.$name.',`file_path`= '.trim($dest_path, '.').', `link` = '.$url.',`note` = '.$note.',`updated_at` = "'.$currentDate.'";';
                    $sql = 'UPDATE `plg_tbl_uf` SET `file_type`= '.$type.', `file_name`= "'.$name.'", `file_path`= "'.$dest_path.'", `link` = "'.$url.'", `note` = "'.$note.'", `updated_at` = "'.$currentDate.'" WHERE `id` = '.$user_file_id.';';
                    $userUploads = $db->query($sql);
                } else {
                    $userUploads = $db->insert('plg_tbl_uf', $userFilesArr);
                }
                if ($userUploads) {
                    $message ='File & Link successfully added.';
                } else {
                    $message = 'Something went wrong';
                }
            }

            /*
            //Update display name
            //if (($settings->change_un == 0) || (($settings->change_un == 2) && ($user->data()->un_changed == 1)))
            $displayname = Input::get("username");
            if ($userdetails->username != $displayname && ($settings->change_un == 1 || (($settings->change_un == 2) && ($user->data()->un_changed == 0)))) {
                $fields=array(
                    'username'=>$displayname,
                    'un_changed' => 1,
                );
                $validation->check($_POST, array(
                    'username' => array(
                        'display' => lang("GEN_UNAME"),
                        'required' => true,
                        'unique_update' => 'users,'.$userId,
                                            'min' => $settings->min_un,
                                  'max' => $settings->max_un
                    )
                ));
                if ($validation->passed()) {
                    if (($settings->change_un == 2) && ($user->data()->un_changed == 1)) {
                        $msg = lang("REDIR_UN_ONCE");
                        Redirect::to($us_url_root.'users/user_uploads.php?err='.$msg);
                    }
                    $db->update('users', $userId, $fields);
                    $successes[]=lang("GEN_UNAME")." ".lang("GEN_UPDATED");
                    logger($user->data()->id, "User", "Changed username from $userdetails->username to $displayname.");
                } else {
                    //validation did not pass
                    foreach ($validation->errors() as $error) {
                        $errors[] = $error;
                    }
                }
            } else {
                $displayname=$userdetails->username;
            }
            //Update first name
            $fname = ucfirst(Input::get("fname"));
            if ($userdetails->fname != $fname) {
                $fields=array('fname'=>$fname);
                $validation->check($_POST, array(
                    'fname' => array(
                        'display' => lang("GEN_FNAME"),
                        'required' => true,
                        'min' => 1,
                        'max' => 60
                    )
                ));
                if ($validation->passed()) {
                    $db->update('users', $userId, $fields);
                    $successes[]=lang("GEN_FNAME")." ".lang("GEN_UPDATED");
                    logger($user->data()->id, "User", "Changed fname from $userdetails->fname to $fname.");
                } else {
                    //validation did not pass
                    foreach ($validation->errors() as $error) {
                        $errors[] = $error;
                    }
                }
            } else {
                $fname=$userdetails->fname;
            }
            //Update last name
            $lname = ucfirst(Input::get("lname"));
            if ($userdetails->lname != $lname) {
                $fields=array('lname'=>$lname);
                $validation->check($_POST, array(
                    'lname' => array(
                        'display' => lang("GEN_LNAME"),
                        'required' => true,
                        'min' => 1,
                        'max' => 60
                    )
                ));
                if ($validation->passed()) {
                    $db->update('users', $userId, $fields);
                    $successes[]=lang("GEN_FNAME")." ".lang("GEN_UPDATED");
                    logger($user->data()->id, "User", "Changed lname from $userdetails->lname to $lname.");
                } else {
                    //validation did not pass
                    foreach ($validation->errors() as $error) {
                        $errors[] = $error;
                    }
                }
            } else {
                $lname=$userdetails->lname;
            }
            if (!empty($_POST['password']) || $userdetails->email != $_POST['email'] || !empty($_POST['resetPin'])) {
                //Check password for email or pw update
                if (is_null($userdetails->password) || password_verify(Input::get('old'), $user->data()->password)) {
                    //Update email
                    $email = Input::get("email");
                    if ($userdetails->email != $email) {
                        $confemail = Input::get("confemail");
                        $fields=array('email'=>$email);
                        $validation->check($_POST, array(
                    'email' => array(
                        'display' => lang("GEN_EMAIL"),
                        'required' => true,
                        'valid_email' => true,
                        'unique_update' => 'users,'.$userId,
                        'min' => 5,
                        'max' => 100
                    )
                ));
                        if ($validation->passed()) {
                            if ($confemail == $email) {
                                if ($emailR->email_act==0) {
                                    $db->update('users', $userId, $fields);
                                    $successes[]=lang("GEN_EMAIL")." ".lang("GEN_UPDATED");
                                    logger($user->data()->id, "User", "Changed email from $userdetails->email to $email.");
                                }
                                if ($emailR->email_act==1) {
                                    $vericode=randomstring(15);
                                    $vericode_expiry=date("Y-m-d H:i:s", strtotime("+$settings->join_vericode_expiry hours", strtotime(date("Y-m-d H:i:s"))));
                                    $db->update('users', $userId, ['email_new'=>$email,'vericode' => $vericode,'vericode_expiry' => $vericode_expiry]);
                                    //Send the email
                                    $options = array(
                                  'fname' => $user->data()->fname,
                                  'email' => rawurlencode($user->data()->email),
                                  'vericode' => $vericode,
                                                'join_vericode_expiry' => $settings->join_vericode_expiry
                                );
                                    $encoded_email=rawurlencode($email);
                                    $subject = lang("EML_VER");
                                    $body =  email_body('_email_template_verify_new.php', $options);
                                    $email_sent=email($email, $subject, $body);
                                    if (!$email_sent) {
                                        $errors[] = lang("ERR_EMAIL");
                                    } else {
                                        $successes[]=lang("EML_CHK")." ".$settings->join_vericode_expiry." ".lang("T_HOURS");
                                    }
                                    if ($emailR->email_act==1) {
                                        logger($user->data()->id, "User", "Requested change email from $userdetails->email to $email. Verification email sent.");
                                    }
                                }
                            } else {
                                $errors[] = lang("EML_MAT");
                            }
                        } else {
                            //validation did not pass
                            foreach ($validation->errors() as $error) {
                                $errors[] = $error;
                            }
                        }
                    } else {
                        $email=$userdetails->email;
                    }
                    if (!empty($_POST['password'])) {
                        $validation->check($_POST, array(
                    'password' => array(
                        'display' => lang("NEW_PW"),
                        'required' => true,
                        'min' => $settings->min_pw,
                    'max' => $settings->max_pw,
                    ),
                    'confirm' => array(
                        'display' => lang("PW_CONF"),
                        'required' => true,
                        'matches' => 'password',
                    ),
                ));
                        foreach ($validation->errors() as $error) {
                            $errors[] = $error;
                        }
                        if (empty($errors) && Input::get('old')!=Input::get('password')) {
                            //process
                            $new_password_hash = password_hash(Input::get('password'), PASSWORD_BCRYPT, array('cost' => 12));
                            $user->update(array('password' => $new_password_hash,'force_pr' => 0,'vericode' => randomstring(15),), $user->data()->id);
                            $successes[]=lang("PW_UPD");
                            logger($user->data()->id, "User", "Updated password.");
                            if ($settings->session_manager==1) {
                                $passwordResetKillSessions=passwordResetKillSessions();
                                if (is_numeric($passwordResetKillSessions)) {
                                    if ($passwordResetKillSessions==1) {
                                        $successes[] = lang("SESS_SUC")." 1 ".lang("GEN_SESSION");
                                    }
                                    if ($passwordResetKillSessions >1) {
                                        $successes[] = lang("SESS_SUC").$passwordResetKillSessions.lang("GEN_SESSIONS");
                                    }
                                } else {
                                    $errors[] = lang("ERR_FAIL_ACT").$passwordResetKillSessions;
                                }
                            }
                        } else {
                            if (Input::get('old')==Input::get('password')) {
                                $errors[] = lang("ERR_PW_SAME");
                            }
                        }
                    }
                    if (!empty($_POST['resetPin']) && Input::get('resetPin')==1) {
                        $user->update(['pin'=>null]);
                        logger($user->data()->id, "User", "Reset PIN");
                        $successes[]=lang("SET_PIN");
                        $successes[]=lang("SET_PIN_NEXT");
                    }
                } else {
                    $errors[]=lang("ERR_PW_FAIL");
                }
            }*/
        }
        $response = array(
        "message"       => $message,
        "iTotalRecords" => $totalRecords,
        'data'          => $arr
    );
        echo json_encode($response);
        exit;
    }
} elseif ($formType == 'editNote') {
    $user_file_id   = isset($_POST['user_file_id']) ? $_POST['user_file_id'] : '';

    $data           = $db->query('SELECT count(id) AS all_count FROM plg_tbl_uf');
    $totalRecords   = $data->count();

    $sql            = 'SELECT * FROM plg_tbl_uf WHERE 1=1 AND id = '.$user_file_id;
    $data           = $db->query($sql);
    $results        = $data->results();

    $arr = [];
    $i = 0;
    foreach ($results as $result) {
        $user_file_id = isset($result->id) ? $result->id : '';
        $arr['file_type']   = isset($result->file_type) ? $result->file_type : '';
        $arr['link']        = isset($result->link) ? $result->link : '';
        $arr['note']        = isset($result->note) ? $result->note : '';
        $arr['user_id']     = isset($result->user_id) ? $result->user_id : '';
        $arr['file_name']   = isset($result->file_name) ? $result->file_name : '';
        $i++;
    }

    $response = [
        "status"                => 1,
        'data'                  => implode('%%', $arr)
    ];
    echo json_encode($response);
    exit();
} elseif ($formType == 'deleteNote') {
    $user_file_id = $_POST['user_file_id'];

    $data           = $db->query('SELECT * FROM `plg_tbl_uf` WHERE 1 = 1 AND id = '.$user_file_id);
    $totalRecords   = $data->count();
    $results        = $data->results();
    $result         = isset($results[0]) ? $results[0] : [];
    $userId         = isset($result->user_id) ? $result->user_id : '';
    $file_path      = isset($result->file_path) ? $result->file_path : '';

    if ($totalRecords > 0) {
        unlink($file_path);
        $sql            = 'DELETE FROM `plg_tbl_uf` WHERE 1 = 1 AND id = '.$user_file_id;
        $data           = $db->query($sql);
        $msg            = 'Record Deleted Successfully';
    } else {
        $msg = 'Something Went Wrong!';
    }

    $response = [
        "status"    => 1,
        'message'   => $msg
    ];
    echo json_encode($response);
    exit();
}
