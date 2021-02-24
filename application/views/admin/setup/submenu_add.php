<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Submenu</h2>
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
                    <?php echo form_open(ADMIN_URL.'setup/submenu_add/'.$menu_id.'/'.($detail ? $detail->submenu_id : ''), array('id'=>'theform')); ?>
                    <input type="hidden" name="submenu_id" value="<?php echo ($detail ? $detail->submenu_id : '0');?>" />
                        <div class="form-group">
                            <h5>Menu</h5>
                            <select class="form-control" name="menu_id">
                                <?php
                                    $sel = set_value('menu_id', ($detail ? $detail->menu_id : $menu_id));
                                    foreach($all_menu as $key => $val){
                                        echo '<option value="'. $val->menu_id .'" '. ($sel == $val->menu_id ? 'selected' : '') .'>'. $val->menu_name .'</option>';
                                    }
                                ?>
                            </select>
                            <?php echo form_error('menu_id'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Menu Code</h5>
                            <input class="form-control" name="submenu_code" value="<?php echo set_value('submenu_code', ($detail ? $detail->submenu_code : ''));?>">
                            <?php echo form_error('submenu_code'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Menu Name</h5>
                            <input class="form-control" name="submenu_name" value="<?php echo set_value('submenu_name', ($detail ? $detail->submenu_name : ''));?>">
                            <?php echo form_error('submenu_name'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Description</h5>
                            <textarea class="form-control" name="submenu_desc" ><?php echo set_value('submenu_desc', ($detail ? $detail->submenu_desc : ''));?></textarea>
                            <?php echo form_error('submenu_desc'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Permits</h5>
                            <input class="form-control" name="submenu_permits" value="<?php echo set_value('submenu_permits', ($detail ? $detail->submenu_permits : ''));?>">
                            <?php echo form_error('submenu_permits'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Order No</h5>
                            <input class="form-control" name="submenu_order" value="<?php echo set_value('submenu_order', ($detail ? $detail->submenu_order : ''));?>">
                            <?php echo form_error('submenu_order'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Url</h5>
                            <input class="form-control" name="submenu_url" value="<?php echo set_value('submenu_url', ($detail ? $detail->submenu_url : ''));?>">
                            <?php echo form_error('submenu_url'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Target</h5>
                            <select class="form-control" name="submenu_target">
                                <?php
                                $sel = set_value('submenu_target', ($detail ? $detail->submenu_target : ''));
                                ?>
                                <option value="_SELF" <?php echo ($sel == '_SELF' ? 'selected' : '');?>>_SELF</option>
                                <option value="_BLANK" <?php echo ($sel == '_BLANK' ? 'selected' : '');?>>_BLANK</option>
                            </select>
                            <?php echo form_error('submenu_target'); ?>
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
