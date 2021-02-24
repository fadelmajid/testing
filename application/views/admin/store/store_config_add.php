<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Store Config</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
        <?php echo $msg;?>
        <?php if ($show_form): ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open($current_url.'/'.($store_config? $store_config->stcf_id : '')); ?>
                    <input type="hidden" name="stcf_id" value="<?php echo set_value('stcf_id', $store_config ? $store_config->stcf_id : 0); ?>">
                    <div class="form-row">
                        <div class="col">
                                <h5>Store Name</h5>
                                <?php if ($store_config): ?>
                                    <label><?php echo $store[0]->st_name; ?></label>
                                    <input type="hidden" name="st_id" value="<?php echo $store_config->st_id; ?>">
                                <?php else: ?>
                                    <select class="form-control m-b" name="st_id">
                                        <?php 
                                        $st_id = set_value('st_id', ($store_config ? $store_config->st_id : ''));
                                        foreach ($store as $st): ?>
                                            <option value="<?php echo $st->st_id; ?>"><?php echo $st->st_name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php endif; ?>
                            <?php echo form_error('st_id'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col">
                            <h5>Minimal Cup</h5>
                            <input type="number" min="1" class="form-control" name="stcf_min_cup" id="stcf_min_cup" value="<?php echo set_value('stcf_min_cup', ($store_config ? $store_config->stcf_min_cup : ''));?>">
                            <?php echo form_error('stcf_min_cup'); ?>
                        </div>
                        <div class="col">
                            <h5>Maximal Cup</h5>
                            <input type="number" min="1" class="form-control" name="stcf_max_cup" id="stcf_max_cup" value="<?php echo set_value('stcf_max_cup', ($store_config ? $store_config->stcf_max_cup : ''));?>">
                            <?php echo form_error('stcf_max_cup'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col">
                            <h5>Minimal Order</h5>
                            <input type="number" min="1" class="form-control" name="stcf_min_order" id="stcf_min_order" value="<?php echo set_value('stcf_min_order', ($store_config ? $store_config->stcf_min_order : ''));?>">
                            <?php echo form_error('stcf_min_order'); ?>
                        </div>
                        <div class="col">
                            <h5>Maximal Order</h5>
                            <input type="number" min="1" class="form-control" name="stcf_max_order" id="stcf_max_order" value="<?php echo set_value('stcf_max_order', ($store_config ? $store_config->stcf_max_order : ''));?>">
                            <?php echo form_error('stcf_max_order'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col">
                            <h5>Range Data (in minute)</h5>
                            <input type="number" min="1" class="form-control" name="stcf_range_data" id="stcf_range_data" value="<?php echo set_value('stcf_range_data', ($store_config ? $store_config->stcf_range_data : ''));?>">
                            <?php echo form_error('stcf_range_data'); ?>
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