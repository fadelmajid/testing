<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> App Version</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
        <?php echo $msg; ?>
        <?php if ($show_form): ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open($current_url.'/'.($appversion ? $appversion->ver_id : ''), ['id' => 'theform']); ?>
                    <input type="hidden" name="ver_id" value="<?php echo set_value('ver_id', $appversion ? $appversion->ver_id : 0); ?>">
                    <div class="form-group">
                        <h5>Version Code</h5>
                        <input class="form-control" name="ver_code" value="<?php echo set_value('ver_code', ($appversion ? $appversion->ver_code : ''));?>">
                        <?php echo form_error('ver_code'); ?>
                    </div>
                    <div class="form-group">
                        <h5>Version Platform</h5>
                        <select class="form-control m-b" name="ver_platform">
                            <?php
                                $sel = set_value('ver_platform', ($appversion ? $appversion->ver_platform : ''));
                                foreach($cst_version['platform'] as $key => $val){
                                    echo '<option value="'. $key.'" '. ($sel == $key ? 'selected' : '') .'>'. $val .'</option>';
                                }
                            ?>
                        </select>
                        <?php echo form_error('ver_platform'); ?>
                    </div>
                    <div class="form-group">
                        <h5>Version Status</h5>
                        <select class="form-control m-b" name="ver_status">
                            <?php
                                $sel = set_value('ver_status', ($appversion ? $appversion->ver_status : ''));
                                foreach($cst_version['status'] as $key => $val){
                                    echo '<option value="'. $key.'" '. ($sel == $key ? 'selected' : '') .'>'. $val .'</option>';
                                }
                            ?>
                        </select>
                        <?php echo form_error('ver_status'); ?>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><?php echo $title_form;?></button>
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