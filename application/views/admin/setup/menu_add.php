<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Menu</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
            <?php echo $msg; ?>
            <?php
                if($show_form){
            ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open(ADMIN_URL.'setup/menu_add/'.($detail ? $detail->menu_id : ''), array('id'=>'theform')); ?>
                        <div class="form-group">
                            <h5>Menu Name</h5>
                            <input class="form-control" name="menu_name" value="<?php echo set_value('menu_name', ($detail ? $detail->menu_name : ''));?>">
                            <?php echo form_error('menu_name'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Icon Css</h5>
                            <input class="form-control" name="menu_icon" value="<?php echo set_value('menu_icon', ($detail ? $detail->menu_icon : ''));?>">
                            <?php echo form_error('menu_icon'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Description</h5>
                            <textarea class="form-control" name="menu_desc" ><?php echo set_value('menu_desc', ($detail ? $detail->menu_desc : ''));?></textarea>
                            <?php echo form_error('menu_desc'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Order No</h5>
                            <input class="form-control" name="menu_order" value="<?php echo set_value('menu_order', ($detail ? $detail->menu_order : ''));?>">
                            <?php echo form_error('menu_order'); ?>
                        </div>
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
