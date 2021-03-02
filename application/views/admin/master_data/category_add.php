<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Category</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
            <?php echo $msg; ?>
            <?php if ($show_form) : ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open_multipart($current_url.'/'.(isset($category->cat_id) ? $category->cat_id : 0), array('id'=>'theform')); ?>
                        <input type="hidden" name="cat_id" value="<?php echo set_value('cat_id', (isset($category->cat_id) ? $category->cat_id : 0)); ?>">
                        <div class="form-group">
                            <h5>Category Name</h5>
                            <input class="form-control" name="cat_name" value="<?php echo set_value('cat_name', (isset($category->cat_name) ? $category->cat_name : ''));?>">
                            <?php echo form_error('cat_name'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Category Order</h5>
                            <input class="form-control" name="cat_order" value="<?php echo set_value('cat_order', (isset($category->cat_order) ? $category->cat_order : ''));?>">
                            <?php echo form_error('cat_order'); ?>
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