<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Payment Method</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
            <?php echo $msg; ?>
            <?php if ($show_form) : ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open($current_url.'/'.($static_pymtd ? $static_pymtd->pymtd_id : ''), array('id'=>'theform')); ?>
                        <input type="hidden" name="pymtd_id" value="<?php echo set_value('pymtd_id', $static_pymtd ? $static_pymtd->pymtd_id : 0); ?>">
                        <div class="form-group">
                            <h5>Payment Method Name</h5>
                            <input class="form-control" name="pymtd_name" value="<?php echo set_value('pymtd_name', ($static_pymtd ? $static_pymtd->pymtd_name : ''));?>">
                            <?php echo form_error('pymtd_name'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Payment Code</h5>
                            <input class="form-control" name="pymtd_code" value="<?php echo set_value('pymtd_code', ($static_pymtd ? $static_pymtd->pymtd_code : ''));?>">
                            <?php echo form_error('pymtd_code'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Payment Label</h5>
                            <input class="form-control" name="pymtd_label" value="<?php echo set_value('pymtd_label', ($static_pymtd ? $static_pymtd->pymtd_label : ''));?>">
                            <?php echo form_error('pymtd_label'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Payment Order</h5>
                            <input class="form-control" name="pymtd_order" value="<?php echo set_value('pymtd_order', ($static_pymtd ? $static_pymtd->pymtd_order : ''));?>">
                            <?php echo form_error('pymtd_order'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Description</h5>
                            <textarea class="form-control" name="pymtd_desc"><?php echo set_value('pymtd_desc', ($static_pymtd ? $static_pymtd->pymtd_desc : '')); ?></textarea>
                            <?php echo form_error('pymtd_desc'); ?>
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