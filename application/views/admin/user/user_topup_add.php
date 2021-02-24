<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> User Topup</h2>
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
                        <h5>Email/Phone Number</h5>
                        <input type="email" class="form-control" value="<?php echo ($user ? $user->user_email : '');?>" data-parsley-type="email" id="autocomplete-email" name="user_email" require />
                        <input type="hidden" name="user_id" id="user_id" value="<?php echo set_value('user_id', ($user ? $user->user_id : ''));?>">
                        <input type="hidden" name="user_email" id="user_email" value="<?php echo set_value('user_email', ($user ? $user->user_email : ''));?>">
                        <?php echo form_error('user_id'); ?>
                    </div>
                    <div class="form-group">
                        <h5>Phone Number</h5>
                        <input type="text" class="form-control" name="user_phone" value="<?php echo set_value('user_phone', ($user ? $user->user_phone : ''));?>" id="user_phone" data-parsley-type="text" disabled>
                    </div>
                    <div class="form-group">
                        <h5>Username</h5>
                        <input type="text" class="form-control" name="user_name" value="<?php echo set_value('user_name', ($user ? $user->user_name : ''));?>" id="user_name" disabled>
                    </div>
                    <div class="form-group">
                        <h5>Current Balance</h5>
                        <input type="text" class="form-control" value="<?php echo set_value('uwal_balance', ($user ? $user->uwal_balance : ''));?>" id="uwal_balance" name="uwal_balance" disabled>
                    </div>
                    <div class="form-group">
                        <h5>Nominal</h5>
                        <select class="form-control m-b" name="utop_nominal" id="utop_nominal">
                            <?php
                                foreach($cst_user['nominal'] as $key => $val){ 
                                    echo '<option value="'. $key.'" '. (set_value('utop_nominal') == $val ? 'selected' : '').'>'. number_format($val) .'</option>';
                                }
                            ?>
                        </select>
                        <?php echo form_error('utop_nominal'); ?>
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
			let $balance = $("#uwal_balance")
            
			$auto_email.autocomplete({
				minLength: 3,
				source: "<?php echo base_url();?>ajax/get_user_is_active",
				select: function (event, ui) {
					let { user_id, user_name, uwal_balance, user_phone, user_email } = ui.item
					$name.val(user_name)
					$phone.val(user_phone)
                    $balance.val(uwal_balance)
					$email.val(user_email)
					$id.val(user_id)
				},
				response: function (event, ui) {
					ui.content.forEach((row) => {
						row.label = row.user_email
						row.value = row.user_email
					})
				}
            })
            if($balance.val() != "") {
                $("#uwal_balance").val(convertToRupiah(parseInt($balance.val())))
            }
            
        })
        
        $("#submit").click(function () {
			let name            = $("#user_name").val()
            let utop_nominal    = convertToRupiah(parseInt($("#utop_nominal").val()))

            var confirm_action  = confirm('Top Up '+ utop_nominal +' to '+ name +'\'s wallet. This process can\'t be undo. Are you sure want to process this Top Up?');

            if (!confirm_action) {
                return false;
            }
        })
	</script>