<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Product</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
        <?php echo $msg; ?>
        <?php if ($show_form): ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open_multipart($current_url.'/'.($product['data'] ? $product['data']->pd_id : 0), ['id' => 'theform']); ?>
                    <input type="hidden" name="pd_id" value="<?php echo set_value('pd_id', $product['data'] ? $product['data']->pd_id : 0); ?>">
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Product Name</h5>
                            <input class="form-control" name="pd_name" value="<?php echo set_value('pd_name', ($product['data'] ? $product['data']->pd_name : '')); ?>">
                            <?php echo form_error('pd_name'); ?>
                        </div>
                        <div class="form-group col-6">
                            <h5>Category</h5>
                            <select class="form-control m-b" name="cat_id">
                            <?php foreach ($product['category'] as $category): ?>
                                <option value="<?php echo $category->cat_id; ?>" <?php echo $product['data'] && $product['data']->cat_id === $category->cat_id ? 'selected="selected"' : ''; ?>><?php echo $category->cat_name; ?></option>
                            <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Base Price</h5>
                            <input class="form-control" name="pd_base_price" value="<?php echo set_value('pd_base_price', ($product['data'] ? $product['data']->pd_base_price : ''));?>">
                            <?php echo form_error('pd_base_price'); ?>
                        </div>
                        <div class="form-group col-6">
                            <h5>Final Price</h5>
                            <input class="form-control" name="pd_final_price" value="<?php echo set_value('pd_final_price', ($product['data'] ? $product['data']->pd_final_price : ''));?>">
                            <?php echo form_error('pd_final_price'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Description</h5>
                            <textarea class="form-control" name="pd_desc" id="" cols="30" rows="2"><?php echo set_value('pd_desc', ($product['data'] ? $product['data']->pd_desc : ''));?></textarea>
                            <?php echo form_error('pd_desc'); ?>
                        </div>
                        <div class="form-group col-6">
                            <h5>Product Order</h5>
                            <input class="form-control" name="pd_order" value="<?php echo set_value('pd_order', ($product['data'] ? $product['data']->pd_order : '')); ?>">
                            <?php echo form_error('pd_order'); ?>
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