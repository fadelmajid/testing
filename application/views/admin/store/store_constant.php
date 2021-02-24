<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Store Constant</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
        <?php echo $msg; ?>
        <?php if ($show_form): ?>
            <div class="ibox">
                <div class="ibox-content">
                    <?php echo form_open($current_url); ?>
                    <input type="hidden" name="stct_id" value="<?php echo set_value('stct_id', $store_constant ? $store_constant->stct_id : 0); ?>">
                    <div class="form-row">
                        <div class="col">
                            <h5>Minimal Cup</h5>
                            <input type="number" min="1" class="form-control" name="stct_min_cup" id="stct_min_cup" value="<?php echo set_value('stct_min_cup', ($store_constant ? $store_constant->stct_min_cup : ''));?>">
                            <?php echo form_error('stct_min_cup'); ?>
                        </div>
                        <div class="col">
                            <h5>Maximal Cup</h5>
                            <input type="number" min="1" class="form-control" name="stct_max_cup" id="stct_max_cup" value="<?php echo set_value('stct_max_cup', ($store_constant ? $store_constant->stct_max_cup : ''));?>">
                            <?php echo form_error('stct_max_cup'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col">
                            <h5>Minimal Order</h5>
                            <input type="number" min="1" class="form-control" name="stct_min_order" id="stct_min_order" value="<?php echo set_value('stct_min_order', ($store_constant ? $store_constant->stct_min_order : ''));?>">
                            <?php echo form_error('stct_min_order'); ?>
                        </div>
                        <div class="col">
                            <h5>Maximal Order</h5>
                            <input type="number" min="1" class="form-control" name="stct_max_order" id="stct_max_order" value="<?php echo set_value('stct_max_order', ($store_constant ? $store_constant->stct_max_order : ''));?>">
                            <?php echo form_error('stct_max_order'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col">
                            <h5>Range Data (in minute)</h5>
                            <input type="number" min="1" class="form-control" name="stct_range_data" id="stct_range_data" value="<?php echo set_value('stct_range_data', ($store_constant ? $store_constant->stct_range_data : ''));?>">
                            <?php echo form_error('stct_range_data'); ?>
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