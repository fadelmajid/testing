<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Partner</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
            <?php echo $msg; ?>
            <?php if ($show_form) : ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open($current_url.'/'.($partner ? $partner->ptr_id : ''), array('id'=>'theform')); ?>
                        <input type="hidden" name="ptr_id" value="<?php echo set_value('ptr_id', $partner ? $partner->ptr_id : 0); ?>">
                        <div class="form-group">
                            <h5>Partner Name</h5>
                            <input class="form-control" name="ptr_name" value="<?php echo set_value('ptr_name', ($partner ? $partner->ptr_name : ''));?>">
                            <?php echo form_error('ptr_name'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Partner Code</h5>
                            <input class="form-control" name="ptr_code" value="<?php echo set_value('ptr_code', ($partner ? $partner->ptr_code : ''));?>">
                            <?php echo form_error('ptr_code'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Partner Token</h5>
                            <input class="form-control" name="ptr_token" value="<?php echo set_value('ptr_token', ($partner ? $partner->ptr_token : ''));?>">
                            <?php echo form_error('ptr_token'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Partner Description</h5>
                            <textarea class="form-control" name="ptr_desc"><?php echo set_value('ptr_desc', ($partner ? $partner->ptr_desc : '')); ?></textarea>
                            <?php echo form_error('ptr_desc'); ?>
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