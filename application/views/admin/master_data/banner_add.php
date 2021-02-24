<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Banner</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
            <?php echo $msg; ?>
            <?php if ($show_form) : ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open_multipart($current_url.'/'.($static_banner ? $static_banner->ban_id : ''), array('id'=>'theform')); ?>
                        <input type="hidden" name="ban_id" value="<?php echo set_value('ban_id', isset($static_banner->ban_id) ? $static_banner->ban_id : 0); ?>">
                        <div class="form-group">
                            <h5>Banner Name</h5>
                            <input class="form-control" name="ban_name" value="<?php echo set_value('ban_name', (isset($static_banner->ban_name) ? $static_banner->ban_name : ''));?>"/>
                            <?php echo form_error('ban_name'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Banner Description</h5>
                            <textarea class="form-control" name="ban_desc"><?php echo set_value('ban_desc', (isset($static_banner->ban_desc) ? $static_banner->ban_desc : '')); ?></textarea>
                            <?php echo form_error('ban_desc'); ?>
                        </div>
                        <div class="form-group">
                                <h5>Banner Image</h5>
                                <input type="file" name="ban_url" class="form-control" />
                                <?php echo form_error('ban_url'); ?>
                                <?php
                                    if(isset($static_banner->ban_url) && $static_banner->ban_url != ''){
                                        echo '<br/><img src="'.UPLOAD_URL.$static_banner->ban_url.'" style="max-width:400px"/>';
                                    }
                                ?>
                            </div>
                        <div class="form-group">
                            <h5>Banner Link</h5>
                            <input class="form-control" name="ban_link" placeholder="https://" value="<?php echo set_value('ban_link', (isset($static_banner->ban_link) ? $static_banner->ban_link : ''));?>"/>
                            <?php echo form_error('ban_link'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Navigation</h5>
                            <input class="form-control" name="ban_nav" value="<?php echo set_value('ban_nav', (isset($static_banner->ban_nav) ? $static_banner->ban_nav : ''));?>"/>
                            <?php echo form_error('ban_nav'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Banner Order</h5>
                            <input class="form-control" name="ban_order" value="<?php echo set_value('ban_order', (isset($static_banner->ban_order) ? $static_banner->ban_order : ''));?>"/>
                            <?php echo form_error('ban_order'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Start Date</h5>
                            <input class="form-control" name="start_date" id="start_date" value="<?php echo set_value('start_date', (isset($static_banner->start_date) ? $static_banner->start_date : ''));?>"/>
                            <?php echo form_error('start_date'); ?>
                        </div>
                        <div class="form-group">
                            <h5>End Date</h5>
                            <input class="form-control" name="end_date" id="end_date" value="<?php echo set_value('end_date', (isset($static_banner->end_date) ? $static_banner->end_date : ''));?>"/>
                            <?php echo form_error('end_date'); ?>
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
<script>
$(document).ready(function(){
    // datepicker for search
    $('#start_date, #end_date').datetimepicker({
        timepicker: false,
        format:'Y-m-d',
        lang:'en'
    });
});
</script>
