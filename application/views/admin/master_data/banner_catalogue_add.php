<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Banner Catalogue</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
            <?php echo $msg; ?>
            <?php if ($show_form) : ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open_multipart($current_url.'/'.($banner_catalogue ? $banner_catalogue->banc_id : ''), array('id'=>'theform')); ?>
                        <input type="hidden" name="banc_id" value="<?php echo set_value('banc_id', isset($banner_catalogue->banc_id) ? $banner_catalogue->banc_id : 0); ?>">
                        <div class="form-group">
                            <h5>Banner Name</h5>
                            <input class="form-control" name="banc_name" value="<?php echo set_value('banc_name', (isset($banner_catalogue->banc_name) ? $banner_catalogue->banc_name : ''));?>"/>
                            <?php echo form_error('banc_name'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Banner Description</h5>
                            <textarea class="form-control" name="banc_desc"><?php echo set_value('banc_desc', (isset($banner_catalogue->banc_desc) ? $banner_catalogue->banc_desc : '')); ?></textarea>
                            <?php echo form_error('banc_desc'); ?>
                        </div>
                        <div class="form-group">
                                <h5>Banner Image</h5>
                                <input type="file" name="banc_url" class="form-control" />
                                <?php echo form_error('banc_url'); ?>
                                <?php
                                    if(isset($banner_catalogue->banc_url) && $banner_catalogue->banc_url != ''){
                                        echo '<br/><img src="'.UPLOAD_URL.$banner_catalogue->banc_url.'" style="max-width:400px"/>';
                                    }
                                ?>
                            </div>
                        <div class="form-group">
                            <h5>Banner Link</h5>
                            <input class="form-control" name="banc_link" placeholder="https://" value="<?php echo set_value('banc_link', (isset($banner_catalogue->banc_link) ? $banner_catalogue->banc_link : ''));?>"/>
                            <?php echo form_error('banc_link'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Navigation</h5>
                            <input class="form-control" name="banc_nav" value="<?php echo set_value('banc_nav', (isset($banner_catalogue->banc_nav) ? $banner_catalogue->banc_nav : ''));?>"/>
                            <?php echo form_error('banc_nav'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Banner Order</h5>
                            <input class="form-control" name="banc_order" value="<?php echo set_value('banc_order', (isset($banner_catalogue->banc_order) ? $banner_catalogue->banc_order : ''));?>"/>
                            <?php echo form_error('banc_order'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Start Date</h5>
                            <input class="form-control date" name="start_date" value="<?php echo set_value('start_date', (isset($banner_catalogue->start_date) ? $banner_catalogue->start_date : ''));?>"/>
                            <?php echo form_error('start_date'); ?>
                        </div>
                        <div class="form-group">
                            <h5>End Date</h5>
                            <input class="form-control date" name="end_date" value="<?php echo set_value('end_date', (isset($banner_catalogue->end_date) ? $banner_catalogue->end_date : ''));?>"/>
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
    $('.date').datetimepicker({
        timepicker: false,
        format:'Y-m-d',
        lang:'en'
    });
});
</script>
