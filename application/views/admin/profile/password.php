<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2>Change Password</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
            <?php echo $msg; ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open(ADMIN_URL.'profile/password', array("role"=>"form")); ?>
                    <div class="form-group">
                            <h5>Current Password</h5>
                            <input class="form-control" type="password" name="current_password" required value="<?php echo set_value('current_password');?>">
                            <?php echo form_error('current_password'); ?>
                        </div>
                        <div class="form-group">
                            <h5>New Password</h5>
                            <input class="form-control" type="password" name="new_password" required value="<?php echo set_value('new_password');?>">
                            <?php echo form_error('new_password'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Confirm New Password</h5>
                            <input class="form-control" type="password" name="confirm_password" required value="<?php echo set_value('confirm_password');?>">
                            <?php echo form_error('confirm_password'); ?>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <button type="reset" class="btn btn-warning">Reset</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.content-wrapper -->
