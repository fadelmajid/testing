<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Barista</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
            <?php echo $msg; ?>
            <?php
                if($show_form){
            ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open(ADMIN_URL.'setup/barista_add/'.($detail ? $detail->admin_id : ''), array('id'=>'theform')); ?>
                        <input type="hidden" name="admin_id" value="<?php echo ($detail ? $detail->admin_id : '');?>" />
                        <div class="form-group">
                            <h5>Username</h5>
                            <input class="form-control" name="barista_username" value="<?php echo set_value('barista_username', ($detail ? $detail->admin_username : ''));?>">
                            <?php echo form_error('barista_username'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Fullname</h5>
                            <input class="form-control" name="barista_fullname" value="<?php echo set_value('barista_fullname', ($detail ? $detail->admin_fullname : ''));?>">
                            <?php echo form_error('barista_fullname'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Email</h5>
                            <input class="form-control" name="barista_email" type="email" value="<?php echo set_value('barista_email', ($detail ? $detail->admin_email : ''));?>">
                            <?php echo form_error('barista_email'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Password</h5>
                            <input class="form-control" name="barista_password" type="password">
                            <?php echo form_error('barista_password'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Allow Login</h5>
                            <?php
                                $sel = set_value('barista_allowlogin', ($detail ? $detail->admin_allowlogin : '' ));
                            ?>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="barista_allowlogin" id="barista_allowlogin1" value="1" <?php echo ($sel === '1' ? 'checked' : '');?>> &nbsp; Yes
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="barista_allowlogin" id="barista_allowlogin0" value="0" <?php echo ($sel === '0' ? 'checked' : '');?>> &nbsp; No
                                </label>
                            </div>
                            <?php echo form_error('barista_allowlogin'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Store Permits</h5>
                            <div class="input-group select" id="select_store">
                                <select name="st_id" class="form-control m-b" id="select_store">
                                    <option value="0">All Store</option>
                                <?php
                                    foreach($store_data as $stores) {
                                        $st_id = set_value('st_id', ($detail ? $detail->st_id : ''));
                                        echo '<option value="'. $stores->st_id .'" '. ($stores->st_id == $st_id ? "selected" : "").'>'. $stores->st_name .'</option>';
                                    }
                                ?>
                                </select>
                            </div>
                        </div>
                        <?php
                            if( ! empty($detail)){
                        ?>
                        
                            <div class="form-group">
                                <h5>Last Login</h5>
                                <p class="form-control-static"><?php echo ($detail ? $detail->admin_lastlogin : '');?></p>
                            </div>
                            <div class="form-group">
                                <h5>Created By</h5>
                                <p class="form-control-static"><?php echo ($detail ? $arr_barista[ $detail->created_by ]: '');?></p>
                            </div>
                            <div class="form-group">
                                <h5>Created Date</h5>
                                <p class="form-control-static"><?php echo ($detail ? $detail->created_date : '');?></p>
                            </div>
                            
                        <?php
                            }
                        ?>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary"><?php echo $title_form;?></button>
                            <button type="reset" class="btn btn-warning">Reset</button>
                        </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
            <?php
                }
            ?>
        </div>
    </div>
</div>
<!-- /.content-wrapper -->
