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
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';

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
// //Forms posted
$formType = isset($_POST['formType']) ? $_POST['formType'] : '';
if ($formType == 'dataTable') {
    $data = $db->query('SELECT * FROM plg_tbl_uf WHERE 1=1');
    $allDataCount = count($data->result());
    dump($allDataCount);
    die;

    $data = $db->query('SELECT * FROM plg_tbl_uf WHERE 1=1');
    $data->result();
    exit();
}
if (!empty($_POST)) {
    $token = $_POST['csrf'];
    if (!Token::check($token)) {
        include($abs_us_root.$us_url_root.'usersc/scripts/token_error.php');
    } else {
        includeHook($hooks, 'post');
        $name       = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
        $type       = isset($_REQUEST["type"]) ? $_REQUEST["type"] : '';
        $note       = isset($_REQUEST["note"]) ? $_REQUEST["note"] : '';
        $url        = isset($_REQUEST["url"]) ? $_REQUEST["url"] : '';
        $document   = isset($_REQUEST["document"]) ? $_REQUEST["document"] : '';

        if (count($_FILES['document']) > 0) {
            $fileTmpPath    = $_FILES['document']['tmp_name'];
            $fileName       = $_FILES['document']['name'];
            $fileSize       = $_FILES['document']['size'];
            $fileType       = $_FILES['document']['type'];
            $fileNameCmps   = explode(".", $fileName);
            $fileExtension  = strtolower(end($fileNameCmps));
            $newFileName    = md5(time() . $fileName) . '.' . $fileExtension;
            $allowedfileExtensions = array('pdf');
            if (in_array($fileExtension, $allowedfileExtensions)) {
            }
            // directory in which the uploaded file will be moved
            error_reporting(E_ALL);
            $uploadFileDir = '../uploads/' . $userId;
            if (!file_exists('../uploads/')) {
                mkdir('../uploads/', 0777, true);
            }
            if (!file_exists('../uploads/' . $userId)) {
                mkdir('../uploads/' . $userId, 0777, true);
            }
            $dest_path = $uploadFileDir .'/'. $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $message ='File is successfully uploaded.';
            } else {
                $message = 'There was some error moving the file to upload directory. Please make sure the upload directory is writable by web server.';
            }
        }
        $userFilesArr = [
            'file_type'     => $type,
            'user_id'       => $userId,
            'file_name'     => $name,
            'file_path'     => trim($dest_path, '.'),
            'link'          => $url,
            'note'          => $note,
            'created_at'    => "NOW()",
        ];
        // $query = "INSERT INTO `plg_tbl_uf` (`file_type`, `file_name`, `file_path`, `link`, `note`, `created_at`, `updated_at`, `deleted_at`) VALUES (1,     'sadasd', 'asdasd', 'asdasd', 'asdasdasdasd');";
        // $userUploads = $db->query($query);
        $userUploads = $db->insert('plg_tbl_uf', $userFilesArr);
        if ($userUploads) {
            $message ='File is successfully uploaded.';
        } else {
            $message = 'There was some error moving the file to upload directory. Please make sure the upload directory is writable by web server.';
        }

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
        }
    }
    Redirect::to('user_uploads.php?err=Saved');
}
// mod to allow edited values to be shown in form after update
$user2 = new User();
$userdetails=$user2->data();
?>
<link rel="stylesheet" href="//cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
<div class="row">
    <div class="col-sm-12 col-md-12">
        <h1 class="h3 p-2 text-gray-800">Files & Links</h1>
        <?php if(!$adminUserTrue) { ?>
        <div class="p-2 float-right">
            <button onclick="addNote()" type="button" class="btn btn-primary">Add Files & Links</button>
        </div>
        <?php } ?>
    </div>

    <div class="col-sm-12 col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Files & Links</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="file_uploads" class="table" style="width:100%">
                        <thead>
                            <tr>
                                <th width="10%">Sr No.</th>
                                <th width="10%">File Type</th>
                                <th width="10%">File Name</th>
                                <th width="10%">Link | Document</th>
                                <th>Note</th>
                                <th>Created Date</th><?php if($adminUserTrue) { ?><th>Actions</th><?php } ?>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<form id="fupForm" name='updateAccount' action='user_uploads.php' method='post' enctype="multipart/form-data">
    <input type="hidden" name="formType" value="addNote">
    <input id="user_id" type="hidden" name="user_id" value="">
    <input id="user_file_id" type="hidden" name="user_file_id" value="">
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Edit File and Link</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12 col-md-12">
                            <?php if (!$errors=='') { ?><div class="alert alert-danger"><?=display_errors($errors);?> </div>
                            <?php } ?>
                            <?php if (!$successes=='') { ?><div class="alert alert-success"><?=display_successes($successes);?></div><?php } includeHook($hooks, 'body'); ?>
                            <div class="form-group">
                                <label for="exampleInputEmail1">File Type</label>
                                <select required id="fileType" class="form-control accordion-dropdpwn" name="type">
                                    <option value="Text" data-parent="#File">Text</option>
                                    <option value="File" data-parent="#Text">Document</option>
                                    <option value="Link" data-parent="#Link">Link</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input autocomplete="off" required type="text" class="form-control" id="name" name="name" placeholder="Name" maxlength="50">
                            </div>
                            <div id="Text" class="form-group">
                                <label for="exampleInputPassword1">Note</label>
                                <textarea autocomplete="off" required id="note" class="form-control" rows="5" name="note" maxlength="500"></textarea>
                            </div>
                            <div id="Link" class="form-group" style="display:none;">
                                <label for="name">Link</label>
                                <input autocomplete="off" disabled required type="url" class="form-control" id="url" name="url" placeholder="https://wwww.example.com">
                            </div>
                            <div id="File" class="form-group" style="display:none;">
                                <label for="document">Upload File</label>
                                <input autocomplete="off" disabled required type="file" id="document" name="document">
                            </div>
                            <?php includeHook($hooks, 'form');?>
                            <input type="hidden" name="csrf" value="<?=Token::generate();?>" />
                            <div class="row">
                                <div class="col-md-12"><div class="statusMsg"></div></div>
                            </div>
                            <?php
                                if (isset($user->data()->oauth_provider) && $user->data()->oauth_provider != null) {
                                    echo lang("ERR_GOOG");
                                }
                                includeHook($hooks, 'bottom');
                            ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="col-6 text-left">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <!-- <a class="btn btn-secondary" href="../users/account.php"><?=lang("GEN_CANCEL");?></a> -->
                        </div>
                        <div class="col-6 text-right">
                            <input class='btn btn-primary' type='submit' value='<?=lang("GEN_SUBMIT");?>' class='submit' />
                        </div>
                    </div>
                    <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" type='submit' class="btn btn-primary">Save changes</button> -->
                </div>
            </div>
        </div>
    </div>
</form>
<form id="fupFormDelete" name='updateAccount' action='user_uploads.php' method='post' enctype="multipart/form-data">
	<input type="hidden" name="formType" value="deleteNote">
	<input id="delete_user_id" type="hidden" name="user_id" value="">
	<input id="delete_user_file_id" type="hidden" name="user_file_id" value="">
	<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="exampleModalLabel">Delete Record</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
					<div class="row">
						<div class="col-md-12">
							<div class="text">
								<p>Press Submit to Delete Record!</p>
							</div>
						</div>
					</div>
					<div class="row">
							<div class="col-md-12"><div class="statusMsg"></div></div>
					</div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
	        <input class='btn btn-primary' type='submit' value='<?=lang("GEN_SUBMIT");?>' class='submit' />
	      </div>
	    </div>
	  </div>
	</div>
</form>

<?php require_once $abs_us_root . $us_url_root . 'usersc/templates/' . $settings->template . '/container_close.php'; //custom template container?>
<?php require_once $abs_us_root . $us_url_root . 'users/includes/page_footer.php'; ?>

<!-- Place any per-page javascript here -->
<script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
function addNote(){
  $("#fupForm")[0].reset();
  $('#exampleModal').modal('show');
}
function deleteNote(user_file_id){
    $('#delete_user_file_id').val(user_file_id);
    $('#deleteModal').modal('show');
}
function editNote(user_file_id){
    $('#user_file_id').val(user_file_id);
    $.ajax({
        type: "POST",
        url: 'ajax_users_files.php',
        data: {"user_file_id": user_file_id, "formType": 'editNote'},
        // beforeSend: function(){
        //     $('.submitBtn').attr("disabled","disabled");
        //     $('#fupForm').css("opacity",".5");
        // },
        success: function (dataHtml) {
            dataHtml = dataHtml.trim();
            if (dataHtml != "") {
                var obj = jQuery.parseJSON(dataHtml);
                dataHtml = obj.data;
                $("#user_type").val('registered');
                var result = dataHtml.split('%%');
                var file_type       = (typeof result[0]) != 'undefined' ? result[0] : '';
                var link            = (typeof result[1]) != 'undefined' ? result[1] : '';
                var note            = (typeof result[2]) != 'undefined' ? result[2] : '';
                var iUserId         = (typeof result[3]) != 'undefined' ? result[3] : '';
                var name            = (typeof result[4]) != 'undefined' ? result[4] : '';

                $('#fileType').val(file_type);
                $('#url').val(link);
                $('#note').val(note);
                $('#user_id').val(iUserId);
                $('#note').val(note);
                $('#name').val(name);

                var selectValue = $('#fileType').val();
                if (selectValue == 'File') {
                    $('#File').css('display', 'block');
                    $('#Link').css('display', 'none');
                    $('#Text').css('display', 'none');
                    document.getElementById('document').disabled = false;
                    document.getElementById('url').setAttribute('disabled', true);
                    document.getElementById('note').setAttribute('disabled', true);
                } else if (selectValue == 'Link') {
                    $('#Link').css('display', 'block');
                    $('#File').css('display', 'none');
                    $('#Text').css('display', 'none');
                    document.getElementById('url').disabled = false;
                    document.getElementById('document').setAttribute('disabled', true);
                    document.getElementById('note').setAttribute('disabled', true);
                } else if (selectValue == 'Text') {
                    $('#Text').css('display', 'block');
                    $('#Link').css('display', 'none');
                    $('#File').css('display', 'none');
                    document.getElementById('note').disabled = false;
                    document.getElementById('url').setAttribute('disabled', true);
                    document.getElementById('document').setAttribute('disabled', true);
                }

            } else {
                $('#fileType').val('');
                $('#url').val('');
                $('#note').val('');
                $('#user_id').val('');
                $('#note').val('');
                $('#name').val('');
            }
        }
    });
    $('#exampleModal').modal('show');
}
$(document).ready(function() {
    var dtable;
    $.fn.dataTable.ext.errMode = 'none';
    dtable = $('#file_uploads').DataTable({
        pageLength: 25,
        order: [
            [5, "desc"]
        ],
        dom: 'Bfrtip',
        columnDefs: [{
                searchPanes: {
                    show: true,
                },
                targets: [1]
            },
            {
                searchPanes: {
                    cascadePanes: true,
                    columns: [0, 1, 2, 3, 5]
                },
            }
        ],
        buttons: {
            buttons: false,
            dom: {
                button: {
                    className: 'btn'
                }
            }
        },
        processing: true,
        serverSide: true,
        serverMethod: 'post',
        ajax: {
            "url": 'ajax_users_files.php',
            "data": function(d) {
                return $.extend({}, d, {
                    "formType": 'dataTable',
                    // "fBookingNo": $('.bookingNoSelect>option:selected').val(),
                    // "fProvider": $('.providerSelect>option:selected').val(),
                    // "fCustomer": $('.customerSelect>option:selected').val(),
                    // "fStartDate": $('#dp4').val(),
                    // "fEndDate": $('#dp5').val(),
                    // "userid": $('#userId').val()
                });
            }
        },
        columns: [
            {data: 'serial'},
            {data: 'file_type'},
            {data: 'file_name'},
            {data: 'link',"orderable": false,},
            {data: 'note',"orderable": false,},
            {data: 'created_at'},
            <?php if($adminUserTrue) { ?>{data: 'actions',"orderable": false,},<?php } ?>

        ],
    });
    $("#fupForm").on('submit', function(e){
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'ajax_users_files.php',
            data: new FormData(this),
            // data: $("#fupForm").serialize(),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData:false,
            beforeSend: function(){
                $('.submitBtn').attr("disabled","disabled");
                $('#fupForm').css("opacity",".5");
            },
            success: function(response){ //console.log(response);
                $('.statusMsg').html('');
                if(response.status == 1){
                    $('#fupForm')[0].reset();
                    $('.statusMsg').html('<p class="alert alert-success">'+response.message+'</p>');
                }else{
                    $('.statusMsg').html('<p class="alert alert-danger">'+response.message+'</p>');
                }
                $('#fupForm').css("opacity","");
                $(".submitBtn").removeAttr("disabled");
                dtable.ajax.reload();
                setTimeout(function() {$('#exampleModal').modal('hide');$('.statusMsg').html('');}, 1500);
            }
        });
    });
    // File type validation
    $("#document").change(function() {
        var file = this.files[0];
        var fileType = file.type;
        var match = ['application/pdf', 'application/msword', 'application/vnd.ms-office', 'image/jpeg', 'image/png', 'image/jpg'];
        if(!((fileType == match[0]) || (fileType == match[1]) || (fileType == match[2]) || (fileType == match[3]) || (fileType == match[4]) || (fileType == match[5]))){
            alert('Sorry, only PDF, DOC, JPG, JPEG, & PNG files are allowed to upload.');
            $("#document").val('');
            return false;
        }
    });
    $('#fileType').change(function() {
        var selectValue = $(this).val();
        if (selectValue == 'File') {
            $('#File').css('display', 'block');
            $('#Link').css('display', 'none');
            $('#Text').css('display', 'none');
            document.getElementById('document').disabled = false;
            document.getElementById('url').setAttribute('disabled', true);
            document.getElementById('note').setAttribute('disabled', true);
        } else if (selectValue == 'Link') {
            $('#Link').css('display', 'block');
            $('#File').css('display', 'none');
            $('#Text').css('display', 'none');
            document.getElementById('url').disabled = false;
            document.getElementById('document').setAttribute('disabled', true);
            document.getElementById('note').setAttribute('disabled', true);
        } else if (selectValue == 'Text') {
            $('#Text').css('display', 'block');
            $('#Link').css('display', 'none');
            $('#File').css('display', 'none');
            document.getElementById('note').disabled = false;
            document.getElementById('url').setAttribute('disabled', true);
            document.getElementById('document').setAttribute('disabled', true);
        }
    });
    $('.password_view_control').hover(function() {
        $('#old').attr('type', 'text');
        $('#password').attr('type', 'text');
        $('#confirm').attr('type', 'text');
    }, function() {
        $('#old').attr('type', 'password');
        $('#password').attr('type', 'password');
        $('#confirm').attr('type', 'password');
    });
    $("#fupFormDelete").on('submit', function(e){
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'ajax_users_files.php',
            data: new FormData(this),
            // data: $("#fupFormDelete").serialize(),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData:false,
            beforeSend: function(){
                $('.submitBtn').attr("disabled","disabled");
                $('#fupForm').css("opacity",".5");
            },
            success: function(response){ //console.log(response);
                $('.statusMsg').html('');
                if(response.status == 1){
                    $('#fupForm')[0].reset();
                    $('.statusMsg').html('<p class="alert alert-success">'+response.message+'</p>');
                }else{
                    $('.statusMsg').html('<p class="alert alert-danger">'+response.message+'</p>');
                }
                $('#fupForm').css("opacity","");
                $(".submitBtn").removeAttr("disabled");
                dtable.ajax.reload();
                setTimeout(function() {$('#deleteModal').modal('hide');$('.statusMsg').html('');}, 1500);

            }
        });
    });
    //
    // $("#fupForm").on('submit', function(e){
    //     e.preventDefault();
    //     $.ajax({
    //         type: 'POST',
    //         url: 'ajax_users_files.php',
    //         data: new FormData(this),
    //         // data: $("#fupForm").serialize(),
    //         dataType: 'json',
    //         contentType: false,
    //         cache: false,
    //         processData:false,
    //         beforeSend: function(){
    //             $('.submitBtn').attr("disabled","disabled");
    //             $('#fupForm').css("opacity",".5");
    //         },
    //         success: function(response){ //console.log(response);
    //             $('.statusMsg').html('');
    //             if(response.status == 1){
    //                 $('#fupForm')[0].reset();
    //                 $('.statusMsg').html('<p class="alert alert-success">'+response.message+'</p>');
    //             }else{
    //                 $('.statusMsg').html('<p class="alert alert-danger">'+response.message+'</p>');
    //             }
    //             $('#fupForm').css("opacity","");
    //             $(".submitBtn").removeAttr("disabled");
    //             dtable.ajax.reload();
    //             setTimeout(function() {$('#exampleModal').modal('hide');}, 1500);
    //         }
    //     });
    // });
    // // File type validation
    // $("#document").change(function() {
    //     var file = this.files[0];
    //     var fileType = file.type;
    //     var match = ['application/pdf', 'application/msword', 'application/vnd.ms-office', 'image/jpeg', 'image/png', 'image/jpg'];
    //     if(!((fileType == match[0]) || (fileType == match[1]) || (fileType == match[2]) || (fileType == match[3]) || (fileType == match[4]) || (fileType == match[5]))){
    //         alert('Sorry, only PDF, DOC, JPG, JPEG, & PNG files are allowed to upload.');
    //         $("#document").val('');
    //         return false;
    //     }
    // });
});
$(function() {
    $('[data-toggle="popover"]').popover()
})
$('.pwpopover').popover();
$('.pwpopover').on('click', function(e) {
    $('.pwpopover').not(this).popover('hide');
});
</script>
<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>
