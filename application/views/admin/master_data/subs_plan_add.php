<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Subcription Plan</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
        <?php echo $msg; ?>
        <?php if ($show_form): ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open_multipart($current_url.'/'.($subs_plan ? $subs_plan->subsplan_id : ''), array('id'=>'theform')); ?>
                    <input type="hidden" name="subsplan_id" value="<?php echo set_value('subsplan_id', $subs_plan ? $subs_plan->subsplan_id : 0); ?>">
                    
                    <!-- **1** -->
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Subs Plan Name</h5>
                            <input class="form-control" name="subsplan_name" value="<?php echo set_value('subsplan_name', ($subs_plan ? $subs_plan->subsplan_name : '')); ?>">
                            <?php echo form_error('subsplan_name'); ?>
                        </div>
                        <div class="form-group col-6">
                            <h5>Expired Day</h5>
                            <input class="form-control" name="expired_day" value="<?php echo set_value('expired_day', ($prm_rules ? $prm_rules['expired_day'] : ''));?>">
                            <?php echo form_error('expired_day'); ?>
                        </div>
                    </div>

                    <!-- **2** -->
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Subs Plan Code</h5>
                            <input class="form-control" name="subsplan_code" value="<?php echo set_value('subsplan_code', ($subs_plan ? $subs_plan->subsplan_code : '')); ?>">
                            <?php echo form_error('subsplan_code'); ?>
                        </div>
                        <div class="form-group col-6">
                            <h5>Custom Function</h5>
                            <input class="form-control" name="custom_function" value="<?php echo set_value('custom_function', ($prm_rules ? $prm_rules['custom_function'] : ''));?>">
                            <?php echo form_error('custom_function'); ?>
                        </div>
                    </div>

                    <!-- **3** -->
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Base Price</h5>
                            <input class="form-control" name="subsplan_baseprice" value="<?php echo set_value('subsplan_baseprice', ($subs_plan ? $subs_plan->subsplan_baseprice : ''));?>">
                            <?php echo form_error('subsplan_baseprice'); ?>
                        </div>
                        <div class="form-group col-6">
                            <h5>Discount Type</h5>
                            <select class="form-control m-b" name="disc_type" >
                                <?php 
                                    $sel = set_value('disc_type', ($prm_rules ? $prm_rules['disc_type'] : ''));
                                    foreach ($cst_type['discount_type'] as $key => $msg):
                                    echo '<option value="'. $key.'" '. ($sel == $key ? 'selected' : '') .'>'. $msg .'</option>';
                                endforeach; ?>
                            </select>
                            <?php echo form_error('disc_type'); ?>
                        </div>
                    </div>

                    <!-- **4** -->
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Final Price</h5>
                            <input class="form-control" name="subsplan_finalprice" value="<?php echo set_value('subsplan_finalprice', ($subs_plan ? $subs_plan->subsplan_finalprice : ''));?>">
                            <?php echo form_error('subsplan_finalprice'); ?>
                        </div>
                        <div class="form-group col-6">
                            <h5>Discount Nominal</h5>
                            <input class="form-control" name="disc_nominal" value="<?php echo set_value('disc_nominal', ($prm_rules ? $prm_rules['disc_nominal'] : ''));?>">
                            <?php echo form_error('disc_nominal'); ?>
                        </div>
                    </div>

                    <!-- **5** -->
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Duration in day</h5>
                            <input class="form-control" name="subsplan_duration" value="<?php echo set_value('subsplan_duration', ($subs_plan ? $subs_plan->subsplan_duration : '')); ?>">
                            <?php echo form_error('subsplan_duration'); ?>
                        </div>
                        <div class="form-group col-6">
                            <h5>Discount Maximal</h5>
                            <input class="form-control" name="disc_max" value="<?php echo set_value('disc_max', ($prm_rules ? $prm_rules['disc_max'] : ''));?>">
                            <?php echo form_error('disc_max'); ?>
                        </div>
                    </div>

                    <!-- **6** -->
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Subs Plan Show</h5>
                            <select class="form-control m-b" name="subsplan_show">
                                <?php
                                    $sel = set_value('subsplan_show', ($subs_plan ? $subs_plan->subsplan_show : ''));
                                    foreach($cst_show['subsplan_show'] as $key => $val){
                                        echo '<option value="'. $key.'" '. ($sel == $key ? 'selected' : '') .'>'. $val .'</option>';
                                    }
                                ?>
                            </select>
                            <?php echo form_error('subsplan_show'); ?>
                        </div>
                        <div class="form-group col-6">
                            <h5>Minimal Order</h5>
                            <input class="form-control" name="min_order" value="<?php echo set_value('min_order', ($prm_rules ? $prm_rules['min_order'] : ''));?>">
                            <?php echo form_error('min_order'); ?>
                        </div>
                    </div>

                    <!-- **7** -->
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Promo Image</h5>
                            <input type="file" name="image" class="form-control" />
                            <?php echo form_error('image'); ?>
                            <?php
                                if(isset($prm_rules['image']) && $prm_rules['image'] != ''){
                                    echo '<br/><img src="'.UPLOAD_URL.$prm_rules['image'].'" style="max-width:100px; max-hight:100px;"/>';
                                }
                            ?>
                        </div>
                        <div class="form-group col-6">
                            <h5>Item Type</h5>
                            <select class="form-control m-b" name="item_type">
                                <?php 
                                    $sel = set_value('item_type', ($prm_rules ? $prm_rules['item_type'] : ''));
                                    foreach ($cst_type['item_type'] as $key => $msg_item): 
                                        echo '<option value="'. $key.'" '. ($sel == $key ? 'selected' : '') .'>'. $msg_item .'</option>';
                                    endforeach; ?>
                            </select>
                            <?php echo form_error('item_type'); ?>
                        </div>
                    </div>

                    <!-- **8** -->
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Subs Plan Image</h5>
                            <input type="file" name="subsplan_img" class="form-control" />
                            <?php echo form_error('subsplan_img'); ?>
                            <?php
                                if(isset($subs_plan->subsplan_img) && $subs_plan->subsplan_img != ''){
                                    echo '<br/><img src="'.UPLOAD_URL.$subs_plan->subsplan_img.'" style="max-width:100px; max-hight:100px;"/>';
                                }
                            ?>
                        </div>
                        <div class="form-group col-6">
                            <h5>Item List</h5>
                            <select data-placeholder="Choose Products" name="item_list[]" class="chosen-select" multiple="" style="width: 100px; display: none;" tabindex="-1">
                                <option value="0" <?php echo (empty($prm_rules_item_list) ? 'selected' : '' )?>> No Product</option>
                                <?php
                                foreach($product as $key_list => $list_product) {
                                    echo '<option value="'. $list_product->pd_id .'" '. (in_array($list_product->pd_id, set_value('item_list', $prm_rules_item_list)) ? 'selected':'') .' >'. $list_product->pd_name  .'</option>';
                                }; ?>
                            </select>
                            <?php echo form_error('item_list[]'); ?>
                        </div>
                    </div>

                    <!-- **9** -->
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Subs Plan Image Detail</h5>
                            <input type="file" name="subsplan_img_detail" class="form-control" />
                            <?php echo form_error('subsplan_img_detail'); ?>
                            <?php
                                if(isset($subs_plan->subsplan_img_detail) && $subs_plan->subsplan_img_detail != ''){
                                    echo '<br/><img src="'.UPLOAD_URL.$subs_plan->subsplan_img_detail.'" style="max-width:100px; max-hight:100px;"/>';
                                }
                            ?>
                        </div>
                        <div class="form-group col-1">
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
                        <div class="form-group col-3">
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

                    <!-- **10** -->
                    <div class="form-row">
                        <div class="form-group col-6">
                        </div>
                    </div>

                    <!-- ***** -->
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