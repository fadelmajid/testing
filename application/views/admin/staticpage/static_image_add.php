<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?>Static Image</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
            <?php echo $msg; ?>
            <?php if ($show_form) : ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open_multipart($current_url.'/'.($static_image ? $static_image->stat_id : ''), array('id'=>'theform')); ?>
                        <input type="hidden" name="stat_id" value="<?php echo set_value('stat_id', $static_image ? $static_image->stat_id : 0); ?>">
                        <div class="form-row">
                            <div class="form-group col-6">
                                <h5>Static Code</h5>
                                <input class="form-control" name="stat_code" value="<?php echo set_value('stat_code', ($static_image ? $static_image->stat_code : ''));?>">
                                <?php echo form_error('stat_code'); ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <h5>Static Title</h5>
                                <input class="form-control" name="stat_title" value="<?php echo set_value('stat_title', ($static_image ? $static_image->stat_title : ''));?>">
                                <?php echo form_error('stat_title'); ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <h5>Image</h5>
                                <input type="file" name="stat_img" class="form-control" />
                                <?php echo form_error('stat_img'); ?>
                                <?php
                                    if(isset($static_image->stat_img) && $static_image->stat_img != ''){
                                        echo '<br/><img src="'.UPLOAD_URL.$static_image->stat_img.'" style="max-width:400px"/>';
                                    }
                                ?>
                            </div>
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