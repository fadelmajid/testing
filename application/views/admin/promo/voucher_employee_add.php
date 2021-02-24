<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Voucher Employee</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
            <?php echo $msg; ?>
            <?php if ($show_form) : ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open_multipart($current_url.'/'.(isset($vce->vce_id) ? $vce->vce_id : ''), array('id'=>'theform')); ?>
                    <input type="hidden" name="vce_id" value="<?php echo set_value('vce_id', (isset($vce->vce_id) ? $vce->vce_id : 0)); ?>">
                    <div class="form-group">
                        <h5>Search User</h5>
                        <input type="text" class="form-control" data-parsley-type="text" value="<?php echo set_value('user_name');?>" id="autocomplete-name" name="user_name" require />
                        <input type="hidden" name="user_id" id="user_id" value="<?php echo set_value('user_id', (isset($vce->user_id) ? $vce->user_id : ''));?>">
                        <input type="hidden" name="vce_name" id="vce_name" value="<?php echo set_value('vce_name', (isset($vce->vce_name) ? $vce->vce_name : ''));?>">
                        <input type="hidden" name="vce_email" id="vce_email" value="<?php echo set_value('vce_email', (isset($vce->vce_email) ? $vce->vce_email : ''));?>">
                        <input type="hidden" name="vce_phone" id="vce_phone" value="<?php echo set_value('vce_phone', (isset($vce->vce_phone) ? $vce->vce_phone : ''));?>">             
                        <?php echo form_error('user_id'); ?>
                    </div>
                    <div class="form-group">
                        <h5>User ID</h5>
                        <input type="text" class="form-control" value="<?php echo set_value('user_id', (isset($vce->user_id) ? $vce->user_id : ''));?>" id="user_id_disable" data-parsley-type="text" disabled>
                    </div>
                    <div class="form-group">
                        <h5>Employee Name</h5>
                        <input type="text" class="form-control" value="<?php echo set_value('vce_name', (isset($vce->vce_name) ? $vce->vce_name : ''));?>" id="vce_name_disable" disabled>
                    </div>
                    <div class="form-group">
                        <h5>Email</h5>
                        <input type="text" class="form-control" value="<?php echo set_value('vce_email', (isset($vce->vce_email) ? $vce->vce_email : ''));?>" id="vce_email_disable" data-parsley-type="text" disabled>
                    </div>
                    <div class="form-group">
                        <h5>Phone Number</h5>
                        <input type="text" class="form-control" value="<?php echo set_value('vce_phone', (isset($vce->vce_phone) ? $vce->vce_phone : ''));?>" id="vce_phone_disable" data-parsley-type="text" disabled>
                    </div>
                    <div class="form-group">
                        <h5>Organize</h5>
                        <select class="form-control m-b" name="vce_organize_name">
                            <?php
                                $sel = set_value('vce_organize_name', (isset($vce->vce_organize_name) ? $vce->vce_organize_name : ''));
                                foreach($cst_vce['organize'] as $key => $val){
                                    echo '<option value="'. $key.'" '. ($sel == $key ? 'selected' : '') .'>'. $val .'</option>';
                                }
                            ?>
                        </select>
                        <?php echo form_error('vce_organize_name'); ?>
                    </div>
                    <div class="form-group">
                        <h5>Position</h5>
                        <select class="form-control m-b" name="vce_position">
                            <?php
                                $sel = set_value('vce_position', (isset($vce->vce_position) ? $vce->vce_position : ''));
                                foreach($cst_vce['position'] as $key => $val){
                                    echo '<option value="'. $key.'" '. ($sel == $key ? 'selected' : '') .'>'. $val .'</option>';
                                }
                            ?>
                        </select>
                        <?php echo form_error('vce_position'); ?>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" id="submit"><?php echo $title_form;?></button>
                        <button type="reset" class="btn btn-warning">Reset</button>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
            <?php endif ?>
        </div>
    </div>
</div>
<!-- /.content-wrapper -->
<script src="<?php echo ADMIN_TEMPLATE_URL;?>js/jquery-ui.js"></script>
<script>
$(document).ready(function(){
		$(function () {
			let $auto_name          = $("#autocomplete-name")
			let $id                 = $("#user_id")
			let $email              = $("#vce_email")
			let $name               = $("#vce_name")
            let $phone              = $("#vce_phone")
            let $user_id_disable    = $('#user_id_disable')
			let $email_disable              = $("#vce_email_disable")
			let $name_disable               = $("#vce_name_disable")
            let $phone_disable              = $("#vce_phone_disable")
            
			$auto_name.autocomplete({
				minLength: 3,
				source: "<?php echo base_url();?>ajax/get_user_is_active",
				select: function (event, ui) {
					let { user_id, user_name, user_phone, user_email } = ui.item
                    $id.val(user_id)
					$name.val(user_name)
					$phone.val(user_phone)
					$email.val(user_email)
                    $user_id_disable.val(user_id)
					$name_disable.val(user_name)
					$phone_disable.val(user_phone)
					$email_disable.val(user_email)
				},
				response: function (event, ui) {
					ui.content.forEach((row) => {
						row.label = row.user_name
						row.value = row.user_name
					})
				}
            })
            
        })
});
</script>