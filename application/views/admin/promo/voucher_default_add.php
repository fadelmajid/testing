<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?></h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
            <?php echo $msg; ?>
            <?php if ($show_form) : ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open_multipart($current_url.'/'.($vcdef ? $vcdef->vcdef_id : ''), array('id'=>'theform')); ?>
                        <input type="hidden" name="vcdef_id" value="<?php echo set_value('vcdef_id', $vcdef ? $vcdef->vcdef_id : 0); ?>">
                        <div class="form-group">
                            <div class="form-group col-6">
                                <h5>Voucher Default Code</h5>
                                <input class="form-control" name="vcdef_code" value="<?php echo set_value('vcdef_code', ($vcdef ? $vcdef->vcdef_code : ''));?>">
                                <?php echo form_error('vcdef_code'); ?>
                            </div>
                            <div class="form-group col-6">
                                <h5>Vouchder Default Type</h5>
                                <select class="form-control m-b" name="item_type">
                                    <?php foreach ($cst_type['item_type'] as $msg_item): ?>
                                        <option value="<?php echo $msg_item; ?>" <?php echo isset($vcdef->vcdef_type) && set_value('item_type', $vcdef->vcdef_type) === $msg_item ? 'selected="selected"' : ''; ?>><?php echo $msg_item; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php echo form_error('vcdef_type'); ?>
                            </div>
                            <div class="form-group col-6">
                                <h5>Item List</h5>
                                <select data-placeholder="Choose Products" name="item_list[]" class="chosen-select" multiple="" style="width: 350px; display: none;" tabindex="-1">
                                    <?php 
                                    $item_list = isset($vcdef->vcdef_list) ? json_decode($vcdef->vcdef_list) : [];
                                    echo '<option value="0"  '. (empty($prm_rules_item_list) ? 'selected' : '' ).' > No Product</option>';
                                    foreach($product as $key_list => $list_product) {
                                        echo '<option value="'. $list_product->pd_id .'" '. (in_array($list_product->pd_id, set_value('item_list', $item_list)) ? 'selected':'') .' >'. $list_product->pd_name  .'</option>';
                                    }; ?>
                                    </select>
                                <?php echo form_error('item_list[]'); ?>
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
    $('.chosen-select').chosen({width: "100%"});
});

</script>
<script src="<?php echo ADMIN_TEMPLATE_URL;?>js/plugins/chosen/chosen.jquery.js"></script>
<script src="<?php echo ADMIN_TEMPLATE_URL;?>js/plugins/select2/select2.full.min.js"></script>