<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Voucher</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
        <?php echo $msg; ?>
        <?php if ($show_form): ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open($current_url.'/', ['id' => 'theform']); ?>
                    <div class="form-group">
                        <h5>Search User</h5>
                        <input type="email" class="form-control" data-parsley-type="email" value="<?php echo set_value('user_email');?>" id="autocomplete-email" name="user_email" require />
                        <input type="hidden" name="user_id" id="user_id" value="<?php echo set_value('user_id');?>">
                        <input type="hidden" name="user_email" id="user_email" value="<?php echo set_value('user_email');?>">
                        <?php echo form_error('user_id'); ?>
                    </div>
                    <div class="form-group">
                        <h5>User ID</h5>
                        <input type="text" class="form-control" value="<?php echo set_value('user_id');?>" id="user_id_disable" data-parsley-type="text" disabled>
                    </div>
                    <div class="form-group">
                        <h5>Username</h5>
                        <input type="text" class="form-control" value="<?php echo set_value('user_name', (isset($user->user_name) ? $user->user_name : ''));?>" id="user_name" disabled>
                    </div>
                    <div class="form-group">
                        <h5>Phone Number</h5>
                        <input type="text" class="form-control" value="<?php echo set_value('user_phone', (isset($user->user_phone) ? $user->user_phone : ''));?>" id="user_phone" data-parsley-type="text" disabled>
                    </div>
                    <div class="form-group">
                        <h5>Quantity</h5>
                        <input type="text" class="form-control" name="qty" placeholder="max quantity is 5" value="<?php echo set_value('qty');?>" id="qty" require>
                        <?php echo form_error('qty'); ?>
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
		$(function () {
			let $auto_email = $("#autocomplete-email")
			let $id = $("#user_id")
			let $email = $("#user_email")
			let $name = $("#user_name")
            let $phone = $("#user_phone")
            let $user_id_disable = $('#user_id_disable')
            
			$auto_email.autocomplete({
				minLength: 3,
				source: "<?php echo base_url();?>ajax/get_user_is_active",
				select: function (event, ui) {
					let { user_id, user_name, user_phone, user_email } = ui.item
					$name.val(user_name)
					$phone.val(user_phone)
					$email.val(user_email)
                    $id.val(user_id)
                    $user_id_disable.val(user_id)
				},
				response: function (event, ui) {
					ui.content.forEach((row) => {
						row.label = row.user_email
						row.value = row.user_email
					})
				}
            })
            
        })
        
        $("#submit").click(function () {
			let name   = $("#user_name").val()
            let qty    = $("#qty").val()

            var confirm_action  = confirm('Are you sure create '+ qty +' vouchers to '+ name +' ?');

            if (!confirm_action) {
                return false;
            }
        })
</script>