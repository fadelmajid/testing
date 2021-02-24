<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Promo</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
            <?php echo $msg; ?>
            <?php if ($show_form) : ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open_multipart($current_url.'/'.($promo ? $promo->prm_id : ''), array('id'=>'theform')); ?>
                        <input type="hidden" name="prm_id" value="<?php echo set_value('prm_id', $promo ? $promo->prm_id : 0); ?>">
                        <div class="form-row">
                            <div class="form-group col-6">
                                <h5>Promo Name</h5>
                                <input class="form-control" name="prm_name" value="<?php echo set_value('prm_name', ($promo ? $promo->prm_name : ''));?>">
                                <?php echo form_error('prm_name'); ?>
                            </div>
                            <div class="form-group col-6">
                                <h5>Discount Type</h5>
                                <select class="form-control m-b" name="disc_type" <?php echo $disabled ?>>
                                    <?php foreach ($cst_promo['discount_type'] as $msg): ?>
                                        <option value="<?php echo $msg; ?>" <?php echo isset($prm_rules['disc_type']) && $prm_rules['disc_type'] === $msg ? 'selected="selected"' : ''; ?>><?php echo ucfirst($msg); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php echo form_error('disc_type'); ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-3">
                                <h5>Promo Start Date</h5>
                                <input class="form-control" name="prm_start" id="prm_start" value="<?php echo set_value('prm_start', ($promo ? $promo->prm_start : ''));?>">
                                <?php echo form_error('prm_start'); ?>
                            </div>
                            <div class="col-3">
                                <h5>Promo End Date</h5>
                                <input class="form-control" name="prm_end" id="prm_end" value="<?php echo set_value('prm_end', ($promo ? $promo->prm_end : ''));?>">
                                <?php echo form_error('prm_end'); ?>
                            </div>
                            <div class="form-group col-6">
                                <h5>Item Type</h5>
                                <select class="form-control m-b" name="item_type">
                                    <?php 
                                        foreach ($cst_promo['item_type'] as $msg_item): ?>
                                        <option value="<?php echo $msg_item; ?>" <?php echo isset($prm_rules['item_type']) && $prm_rules['item_type'] === $msg_item ? 'selected="selected"' : ''; ?>><?php echo ucfirst($msg_item); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php echo form_error('item_type'); ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-3">
                                <h5>Promo Type</h5>
                                <select id="prm_type" class="form-control m-b" name="prm_type" <?php echo $disabled ?>>
                                    <?php foreach ($cst_promo['type'] as $msg): ?>
                                        <option class="cek" value="<?php echo $msg ?>" <?php echo isset($promo->prm_type) && $promo->prm_type === $msg ? 'selected="selected"' : ''; ?>><?php echo ucfirst($msg); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php echo form_error('prm_type'); ?>
                            </div>
                            <div class="form-group col-3">
                                <h5>Promo Visible</h5>
                                <select id="prm_visible" class="form-control m-b" name="prm_visible" >
                                    <?php foreach ($cst_promo['visible'] as $msg): ?>
                                        <option class="cek" value="<?php echo $msg ?>" <?php echo isset($promo->prm_visible) && $promo->prm_visible === $msg ? 'selected="selected"' : ''; ?>><?php echo ucfirst($msg); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php echo form_error('prm_visible'); ?>
                            </div>
                            <div class="form-group col-6">
                                <h5>Item List</h5>
                                <select data-placeholder="Choose Products" name="item_list[]" class="chosen-select" multiple="" style="width: 350px; display: none;" tabindex="-1">
                                    <option value="0" <?php echo (empty($prm_rules_item_list) ? 'selected' : '' )?>> No Product</option>
                                    <?php 
                                    foreach($product as $key_list => $list_product) {
                                        echo '<option value="'. $list_product->pd_id .'" '. (in_array($list_product->pd_id, set_value('item_list', $prm_rules_item_list)) ? 'selected':'') .' >'. $list_product->pd_name  .'</option>';
                                    }; ?>
                                    </select>
                                <?php echo form_error('item_list[]'); ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <h5 class="change">Promo Code</h5>
                                <input class="form-control" name="prm_custom_code" value="<?php echo set_value('prm_custom_code', ($promo ? $promo->prm_custom_code : ''));?>" <?php echo $disabled ?>>
                                <?php echo form_error('prm_custom_code'); ?>
                            </div>
                            <div class="form-group col-6">
                                <h5>Discount Nominal</h5>
                                <input class="form-control" name="disc_nominal" value="<?php echo set_value('disc_nominal', ($promo ? $prm_rules['disc_nominal'] : ''));?>">
                                <?php echo form_error('disc_nominal'); ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <h5>Limit Usage</h5>
                                <input id= "limit_usage" class="form-control" name="limit_usage" value="<?php echo set_value('limit_usage', ($promo ? $prm_rules['limit_usage'] : ''));?>" <?php echo $disabled ?>>
                                <?php echo form_error('limit_usage'); ?>
                            </div>
                            <div class="form-group col-6">
                                <h5>Discount Maximal</h5>
                                <input class="form-control" name="disc_max" value="<?php echo set_value('disc_max', ($promo ? $prm_rules['disc_max'] : ''));?>">
                                <?php echo form_error('disc_max'); ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <h5>Limit Per User</h5>
                                <input id= "limit_per_user" class="form-control" name="limit_per_user" value="<?php echo set_value('limit_per_user', (isset($prm_rules['limit_per_user']) ? $prm_rules['limit_per_user'] : ''));?>" <?php echo $disabled ?>>
                                <?php echo form_error('limit_per_user'); ?>
                            </div>
                            <div class="form-group col-6">
                                <h5>Minimal Order</h5>
                                <input class="form-control" name="min_order" value="<?php echo set_value('min_order', ($promo ? $prm_rules['min_order'] : ''));?>">
                                <?php echo form_error('min_order'); ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <h5>Custom Function</h5>
                                <input class="form-control" name="custom_function" value="<?php echo set_value('custom_function', ($promo ? $prm_rules['custom_function'] : ''));?>">
                                <?php echo form_error('custom_function'); ?>
                            </div>
                            <div class="form-group col-6">
                                <h5>Delivery Type</h5>
                                <select class="form-control m-b" name="delivery_type">
                                    <?php foreach ($cst_promo['delivery_type'] as $key => $msg): ?>
                                        <option value="<?php echo $key; ?>" <?php echo isset($prm_rules['delivery_type']) && $prm_rules['delivery_type'] === $key ? 'selected="selected"' : ''; ?>><?php echo ucfirst($msg); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php echo form_error('delivery_type'); ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <h5>Promo Image</h5>
                                <input type="file" name="prm_img" class="form-control" />
                                <?php echo form_error('prm_img'); ?>
                                <?php
                                    if(isset($promo->prm_img) && $promo->prm_img != ''){
                                        echo '<br/><img src="'.UPLOAD_URL.$promo->prm_img.'" style="max-width:400px"/>';
                                    }
                                ?>
                            </div>
                            
                            <div class="form-group col-6">
                                <div class="form-group col-6">
                                    <h5>Include Delivery</h5>
                                    <label class="checkbox-inline">
                                        <?php
                                            $delivery_included = set_value('delivery_included', ((isset($prm_rules['delivery_included']) && $prm_rules['delivery_included'] == true) ? 1 : 0));
                                            $sel = ($delivery_included == 1 ? 'checked' : '');
                                        ?>
                                        <input name="delivery_included" type="checkbox" value="1" <?php echo $sel ;?> > Yes
                                    </label>
                                    <?php echo form_error('delivery_included'); ?>
                                </div>
                                <div class="form-group col-6">
                                    <h5>Free Delivery</h5>
                                    <label class="checkbox-inline">
                                        <?php
                                            $free_delivery = set_value('free_delivery', ((isset($prm_rules['free_delivery']) && $prm_rules['free_delivery'] == true) ? 1 : 0));
                                            $sel = ($free_delivery == 1 ? 'checked' : '');
                                        ?>
                                        <input name="free_delivery" type="checkbox" value="1" <?php echo $sel; ?>> Yes
                                    </label>
                                    <?php echo form_error('free_delivery'); ?>
                                </div>
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

<script>
$(document).ready(function(){
    // timepicker for search
    $('#prm_start, #prm_end').datetimepicker({
        datepicker:true,
        format:'Y-m-d H:i:s',
        lang:'en'
    });

    // enable / disable limit usage
    $('select[name="prm_type"]').on('change', function() {
            if($(this).val() == 'generated'){
                $('.change').text('Prefix');  
                $('#limit_usage').attr('disabled', false);
                $('#limit_per_user').attr('disabled', false);
            }else if($(this).val() == 'unlimited'){
                $('.change').text('Promo Code');
                $('#limit_usage').attr('disabled', true);
                $('#limit_usage').val(0);
                $('#limit_per_user').attr('disabled', true);
                $('#limit_per_user').val(0);
            }else{
                $('.change').text('Promo Code');  
                $('#limit_usage').attr('disabled', false);  
                $('#limit_per_user').attr('disabled', false);
            }
    });
    <?php
        echo ($disabled == "" ?  "$('select[name=\"prm_type\"]').change();" : '')
    ?>


    $('.chosen-select').chosen({width: "100%"});
});

</script>
<script src="<?php echo ADMIN_TEMPLATE_URL;?>js/plugins/chosen/chosen.jquery.js"></script>
<script src="<?php echo ADMIN_TEMPLATE_URL;?>js/plugins/select2/select2.full.min.js"></script>