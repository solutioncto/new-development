<?php
if(file_exists("install/index.php")){
	//perform redirect if installer files exist
	//this if{} block may be deleted once installed
	header("Location: install/index.php");
}

require_once 'users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';
if(isset($user) && $user->isLoggedIn()){
}
?>
<div id="page-wrapper">
    <div class="container">
        <div class="jumbotron">
            <!-- <h1 align="center"><?=lang("JOIN_SUC");?> <?php echo $settings->site_name;?></h1> -->
            <p align="center">
                <?php
				if($user->isLoggedIn()){
					require_once 'fetch_content.php';?>
                <!-- <a class="btn btn-primary" href="users/account.php" role="button"><?=lang("ACCT_HOME");?> &raquo;</a> -->
            <table>
                <tr colspan="2">
                    <td style="padding-top: 8px;padding-right: 20px">
                        <h2>Client Files & Links</h2>
                    </td>
                    <td>
                        <input type="button" name="addcontent" class="btn btn-primary" value="&plus; Add" id="addbtn"
                            data-toggle="modal" data-target="#addcontent">
                    </td>
                </tr>
            </table>
			<?php if (count($user_content) > 10) { ?>
				<table align="right" style="margin: 20px;">
					<tr colspan="1">
						<td>
							<input type="button" name="showall" class="btn btn-primary" value="Show All" id="showall" onclick="showall()">
							<input type="button" name="showrecent" class="btn btn-primary" hidden="true" value="Show Most Recent" id="showrecent" onclick="showrecent()">
						</td>
					</tr>
				</table>
			<?php } ?>
            <?php if (count($user_content) > 0) {?>
            <table id="filetable" style="width: 96%;margin: 35px;"  class="sortable" cellspacing="0" >
				<thead>
                <tr>
                    <th style="width: 18%;">
                        Name
                    </th>
                    <th style="width: 46%;" class="sorttable_nosort"></th>
                    <th>
                        Updated
                    </th>
                    <th class="sorttable_nosort">
                        Actions
                    </th>
                </tr>
				</thead>
				<?php
				$i = 1;
				foreach ($user_content as $content){
					if ($i > 10) {
				?>
					<tr class="hide">
					<?php } else { ?>
					<tr>
					<?php } ?>
						<td>
							<?php echo $content["title"];?>
						</td>
						<td class="truncate">
							<?php 
								$end = strpos($content["content"],"\n");
								if(!$end)
									$end = 20;
								echo $content["type_id"] != "3" ? $content["type_id"] == "2" ? "<a href='". (similar_text($content["content"],"http") === 0 ? "http://".$content["content"]: $content["content"]) ."' target='_blank'>".
								$content["content"].
								"</a>" : "<a href='". $content["content"] ."' download>".
								basename($content["content"]).
								"</a>" : substr($content["content"],0,$end);
							?>
						</td>
						<td>
							<?php echo $content["updated_date"];?>
						</td>
						<td>
							<table>
								<tr colspan="3">
									<td>
										<button type="button" class="btn btn-default btn-sm editbtn" data-id="<?php echo $content["id"]; ?>"  data-type="<?php echo $content["type_id"]; ?>"  data-title="<?php echo $content["title"]; ?>"  data-content="<?php echo $content["content"]; ?>">
											<span class="glyphicon glyphicon-pencil"></span> 
										</button>
									</td>
									<td>
										<button type="button" class="btn btn-default btn-sm remcontent" data-id="<?php echo $content["id"]; ?>"  data-type="<?php echo $content["type_id"]; ?>"  data-content="<?php echo $content["content"]; ?>">
											<span class="glyphicon glyphicon-trash"></span> 
										</button>
									</td>
									<td>
										<?php 
										if($content["type_id"] == "1"){
											echo "<a href='". $content["content"] ."' class='btn btn-default btn-sm' download>
											 <span class='glyphicon glyphicon-download-alt'></span>
											</a>";
											/* echo "<form method='get' action='".$content["content"]."'>
											<button type='submit' class='btn btn-default btn-sm'><span class='glyphicon glyphicon-download-alt'></span></button>
											</form>"; */
										} else if($content["type_id"] == "3"){?>
											<button type="button" class="btn btn-default btn-sm viewnote" data-content="<?php echo $content["content"]; ?>" data-toggle="modal" data-target="#shownote">
												<span class="glyphicon glyphicon-eye-open"></span> 
											</button>
										<?php } ?>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				<?php
				$i++;
				}
				?>
            </table>
            <?php }else{?>
            <table align="center" style="margin-top: 40px">
                <tr colspan="1">
                    <td>
                        <h4>No Data Found!</h4>
                    </td>
                </tr>
            </table>
            <?php }?>
            <?php }else{?>
            <a class="btn btn-warning" href="users/login.php" role="button"><?=lang("SIGNIN_TEXT");?> &raquo;</a>
            <a class="btn btn-info" href="users/join.php" role="button"><?=lang("SIGNUP_TEXT");?> &raquo;</a>
            <?php }?>
            </p>
            <br>
        </div>
        <?php  languageSwitcher();?>
    </div>
</div>

<div class="modal fade" id="addcontent" tabindex="-1" role="dialog" aria-labelledby="addcontentLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addcontentLabel">Add Content</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
			<table class="add-modal">
				<tr colspan="2">
					<td>
						<label for="atype">Type:</label>
					</td>
					<td>
						<select id="atype" name="atype" onchange="showDiv(this.value)">
							<option value="" selected hidden>Select...</option>
                    	<?php
						foreach ($content_types as $type){
						?>
                    		<option value="<?php echo $type["id"];?>"><?php echo ucfirst($type["type"]);?></option><?php
						}?>
                		</select>
					</td>
				</tr>
				<tr colspan="2">
					<td>
                		<label for="name">Name:</label>
					</td>
					<td>
						<input type="text" id="name" name="name">
					</td>
				</tr>
				<?php
				foreach ($content_types as $type){
				?>
					<tr id="tr_<?php echo $type["id"];?>" class="hidden" colspan="2">
						<td>
							<label for="<?php echo $type["type"];?>"><?php echo ucfirst($type["type"]);?>:</label>
						</td>
						<td>
							<?php if ($type["type"] == "file") { ?>
								<input type="<?php echo $type["type"];?>" id="<?php echo $type["type"];?>"
								name="<?php echo $type["type"];?>">
							<?php } else if ($type["type"] == "link") { ?>
								<input type="text" id="<?php echo $type["type"];?>" name="<?php echo $type["type"];?>">
							<?php } else { ?>
								<textarea id="<?php echo $type["type"];?>" name="<?php echo $type["type"];?>" rows="5" cols="20"></textarea>
							<?php } ?>
						</td>
					</tr>
				<?php
				}
				?>
			</table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addcontent()">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editcontent" tabindex="-1" role="dialog" aria-labelledby="editcontentLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editcontentLabel">Edit Content</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
			<table class="add-modal">
				<tr colspan="2">
					<td>
						<label for="etype">Type:</label>
					</td>
					<td>
						<select id="etype" name="etype" disabled>
                    	<?php
						foreach ($content_types as $type){
						?>
                    		<option value="<?php echo $type["id"];?>"><?php echo ucfirst($type["type"]);?></option><?php
						}?>
                		</select>
					</td>
				</tr>
				<tr colspan="2">
					<td>
                		<label for="ename">Name:</label>
					</td>
					<td>
						<input type="text" id="ename" name="ename">
						<input type="text" id="eid" name="eid" hidden>
						<input type="text" id="efilename" name="efilename" hidden>
					</td>
				</tr>
				<?php
				foreach ($content_types as $type){
				?>
					<tr id="etr_<?php echo $type["id"];?>" class="hidden" colspan="2">
						<td>
							<?php if ($type["type"] == "file") { ?>
								<label for="<?php echo $type["type"];?>"><?php echo "New ".ucfirst($type["type"]);?>:</label>
							<?php } else { ?>
								<label for="<?php echo $type["type"];?>"><?php echo ucfirst($type["type"]);?>:</label>
							<?php } ?>
						</td>
						<td>
							<?php if ($type["type"] == "file") { ?>
								<input type="<?php echo $type["type"];?>" id="<?php echo "e".$type["type"];?>"
								name="<?php echo "e".$type["type"];?>" title="This file will replace the previously uploaded file!">
							<?php } else if ($type["type"] == "link") { ?>
								<input type="text" id="<?php echo "e".$type["type"];?>" name="<?php echo "e".$type["type"];?>">
							<?php } else { ?>
								<textarea id="<?php echo "e".$type["type"];?>" name="<?php echo "e".$type["type"];?>" rows="5" cols="20"></textarea>
							<?php } ?>
						</td>
					</tr>
				<?php
				}
				?>
			</table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="editcontent()">Update</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="shownote" tabindex="-1" role="dialog" aria-labelledby="shownoteLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="shownoteLabel">Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" align="center">
			<textarea id="usernote" rows="5" cols="40" disabled></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

function showall() {
	var tr = document.getElementsByClassName("hide");
	for (var i = 0; i < tr.length; i++) {
		tr[i].classList.remove("hide");
	}
	document.getElementById("showall").classList.add("hide");
	document.getElementById("showrecent").hidden = false;
}

function showrecent() {
	location.reload();
}

$('.editbtn').click(function(e) {
	e.preventDefault();
  	var id = $(this).data('id');
  	var type = $(this).data('type');
  	var title = $(this).data('title');
  	var content = $(this).data('content');

	document.getElementById("eid").value = id;
	document.getElementById("ename").value = title;
	document.getElementById("etype").value = type;
	if(type == "1"){
		document.getElementById("efilename").value = content;
	}
	else if(type == "2"){
		document.getElementById("elink").value = content;
	}
	else if(type == "3"){
		document.getElementById("enote").value = content;
	}

	document.getElementById("etr_"+type).style.display = 'table-row';
	for (var i = 1; i <= 3; i++) {
		if (type != i) {
			document.getElementById("etr_"+i).style.display = 'none';
		}
	}
  
  	$('#editcontent').modal('show');
});

function showDiv(type)
{
    document.getElementById("tr_"+type).style.display = 'table-row';
	for (var i = 1; i <= 3; i++) {
		if (type != i) {
			document.getElementById("tr_"+i).style.display = 'none';
		}
	}
}

function addcontent() {
	var title = document.getElementById("name").value;
    var type = document.getElementById("atype").value;
	var file = document.getElementById("file").value;
	var link = document.getElementById("link").value;
	var note = document.getElementById("note").value;
	if(title == '' || type == '')
	{
		swal("Error!", "All fields are Required!!", "error");
	}
	else
	{
		if (type == "1") {
			if (file == "") {
				swal("Error!", "No file selected!!", "error");
			}
			else {
				var form_data = new FormData();
				var oFReader = new FileReader();
				oFReader.readAsDataURL(document.getElementById("file").files[0]);
				var f = document.getElementById("file").files[0];
				var fsize = f.size||f.fileSize;
				if(fsize > 2000000)
				{
					swal("Error!", "File Size is too large!", "error");
				}
				else
				{
					form_data.append("file", document.getElementById('file').files[0]);
					form_data.append("title", title);
					form_data.append("type", type);
					$.ajax({
						url:"add_content.php",
						method:"POST",
						data: form_data,
						contentType: false,
						cache: false,
						processData: false,
						success:function(data)
						{
							if (data.includes("Saved!"))
							{ 	
								swal({
									title: 'Saved!',
									text: "Content saved Successfully.",
									icon: 'success'
								})
								.then((done) => {
									location.reload();
								});
							}
							else
								swal("Error!", data, "error");
						}
					});
				}
			}
		} else if (type == "2") {
			if (link == "") {
				swal("Error!", "Link cannont be empty!!", "error");	
			}
			else if (!validURL(link)) {
				swal("Error!", "Link not a valid URL!!", "error");
			}
			else {
				$.post("add_content.php",
				{
					title : title,
					type : type,
					link : link
				},
				function(data,status){
					if (status == "success")
					{ 	
						swal({
								title: 'Saved!',
								text: "Content saved Successfully.",
								icon: 'success'
						})
						.then((done) => {
							location.reload();
						});
					}
					else
						swal("Error!", data, "error");
				});
			}
		} else if (type == "3") {
			if (note == "") {
				swal("Error!", "Note cannont be empty!!", "error");
			}
			else {
				$.post("add_content.php",
				{
					title : title,
					type : type,
					note : note
				},
				function(data,status){
					if (status == "success")
					{ 	
						swal({
								title: 'Saved!',
								text: "Content saved Successfully.",
								icon: 'success'
						})
						.then((done) => {
							location.reload();
						});
					}
					else
						swal("Error!", data, "error");
				});
			}
		}
	}
}

function editcontent() {
	var id = document.getElementById('eid').value;
	var type = document.getElementById("etype").value;
	var title = document.getElementById("ename").value;
	var file = document.getElementById("efile").value;
	var prevfile = document.getElementById("efilename").value;
	var link = document.getElementById("elink").value;
	var note = document.getElementById("enote").value;
	if(title == '')
	{
		swal("Error!", "Name is Required!!", "error");
	}
	else
	{
		if (type == "1") {
			if (file == "") {
				swal("Error!", "No file selected!!", "error");
			}
			else {
				var form_data = new FormData();
				var oFReader = new FileReader();
				oFReader.readAsDataURL(document.getElementById("efile").files[0]);
				var f = document.getElementById("efile").files[0];
				var fsize = f.size||f.fileSize;
				if(fsize > 2000000)
				{
					swal("Error!", "File Size is too large!", "error");
				}
				else
				{
					form_data.append("file", document.getElementById('efile').files[0]);
					form_data.append("title", title);
					form_data.append("id", id);
					form_data.append("prevfile", prevfile);
					form_data.append("type", type);
					$.ajax({
						url:"edit_content.php",
						method:"POST",
						data: form_data,
						contentType: false,
						cache: false,
						processData: false,
						success:function(data)
						{
							if (data.includes("Saved!"))
							{ 	
								swal({
									title: 'Updated!',
									text: "Content updated Successfully.",
									icon: 'success'
								})
								.then((done) => {
									location.reload();
								});
							}
							else
								swal("Error!", data, "error");
						}
					});
				}
			}
		} else if (type == "2") {
			if (link == "") {
				swal("Error!", "Link cannont be empty!!", "error");	
			}
			else if (!validURL(link)) {
				swal("Error!", "Link not a valid URL!!", "error");
			}
			else {
				$.post("edit_content.php",
				{
					title : title,
					type : type,
					id : id,
					link : link
				},
				function(data,status){
					if (status == "success")
					{ 	
						swal({
								title: 'Updated!',
								text: "Content updated Successfully.",
								icon: 'success'
						})
						.then((done) => {
							location.reload();
						});
					}
					else
						swal("Error!", data, "error");
				});
			}
		} else if (type == "3") {
			if (note == "") {
				swal("Error!", "Note cannont be empty!!", "error");
			}
			else {
				$.post("edit_content.php",
				{
					title : title,
					type : type,
					id : id,
					note : note
				},
				function(data,status){
					if (status == "success")
					{ 	
						swal({
								title: 'Updated!',
								text: "Content updated Successfully.",
								icon: 'success'
						})
						.then((done) => {
							location.reload();
						});
					}
					else
						swal("Error!", data, "error");
				});
			}
		}
	}
}

function validURL(str) {
  var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
    '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
    '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
    '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
    '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
    '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
  return !!pattern.test(str);
}

$('.viewnote').click(function(e) {
	e.preventDefault();
	document.getElementById("usernote").innerHTML = $(this).data('content');
});

$('.remcontent').click(function(e) {
	e.preventDefault();
	var id = $(this).data('id');
	var type = $(this).data('type');
	var filename = "";
	if(type == "1"){
		filename = $(this).data('content');
	}

	swal({
		title: "Are you sure?",
		text: "Once deleted, you will not be able to recover this record!",
		icon: "warning",
		buttons: ["Cancel", "Yes, delete it!"],
		dangerMode: true,
		cancelButtonColor: '#d33'
	})
	.then((willDelete) => {
		if (willDelete) {
		$.post("remove_content.php",
		{
			id : id,
			filename : filename
		},
		function(data,status){
			if (status == "success")
			{ 	
				swal({
						title: 'Deleted!',
						text: "Selected record has been deleted.",
						icon: 'success'
				})
				.then((willDelete) => {
					location.reload();
				});
			}
			else
				swal("Error!", data, "error");
		});
		}
	});
});

</script>

<style>
.add-modal{
	text-align: center;
	width: 100%;
}

.add-modal tr td:nth-child(1){
	text-align: right;
	width: 34%;
	padding-right: 10%;
}

.add-modal tr td:nth-child(2){
	text-align: left;
}

.hidden {
	display: none;
}

.hide {
	display: none;
}

.truncate {
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	width: 90%;
	display: inline-block;
}

table.sortable thead {
    font-weight: bold;
    cursor: pointer;
}

table.sortable th:not(.sorttable_sorted):not(.sorttable_sorted_reverse):not(.sorttable_nosort):after { 
    content: " \25B4\25BE" 
}

.btn:hover {
	color: #212529;
	text-decoration: none;
	background-color: #007bff;
}

</style>


<?php require_once $abs_us_root . $us_url_root . 'users/includes/html_footer.php'; ?>