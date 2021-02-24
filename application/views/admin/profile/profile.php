<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2>Edit Profile</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
            <?php echo $msg; ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open(ADMIN_URL.'profile', array("role"=>"form")); ?>
                        <div class="form-group">
                            <h5>Role</h5>
                            <select class="form-control" name="role_id" disabled="">
                                <?php
                                    $sel = $detail_admin->role_id;
                                    foreach($all_roles as $key => $val){
                                        echo '<option value="'. $val->role_id .'" '. ($sel == $val->role_id ? 'selected' : '') .'>'. $val->role_name .'</option>';
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <h5>Username</h5>
                            <input class="form-control" name="admin_username" required value="<?php echo set_value('admin_username', $detail_admin->admin_username);?>">
                            <?php echo form_error('admin_username'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Fullname</h5>
                            <input class="form-control" name="admin_fullname" required value="<?php echo set_value('admin_fullname', $detail_admin->admin_fullname);?>">
                            <?php echo form_error('admin_fullname'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Email</h5>
                            <input class="form-control" name="admin_email" type="email" required value="<?php echo set_value('admin_email', $detail_admin->admin_email);?>">
                            <?php echo form_error('admin_email'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Last Login</h5>
                            <p class="form-control-static"><?php echo $detail_admin->admin_lastlogin;?></p>
                        </div>
                        <div class="form-group">
                            <h5>Created By</h5>
                            <p class="form-control-static"><?php echo $arr_admin[ $detail_admin->created_by ];?></p>
                        </div>
                        <div class="form-group">
                            <h5>Created Date</h5>
                            <p class="form-control-static"><?php echo $detail_admin->created_date;?></p>
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
