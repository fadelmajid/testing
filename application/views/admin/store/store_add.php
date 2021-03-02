<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Store</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
        <?php echo $msg; ?>
        <?php if ($show_form): ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open($current_url.'/'.($store ? $store->st_id : 0), ['id' => 'theform']); ?>
                    <input type="hidden" name="st_id" value="<?php echo set_value('st_id', $store ? $store->st_id : 0); ?>">
                    <div class="form-group">
                        <h5>Store Code</h5>
                        <input class="form-control" name="st_code" value="<?php echo set_value('st_code', ($store ? $store->st_code : ''));?>">
                        <?php echo form_error('st_code'); ?>
                    </div>
                    <div class="form-group">
                        <h5>Store Name</h5>
                        <input class="form-control" name="st_name" value="<?php echo set_value('st_name', ($store ? $store->st_name : ''));?>">
                        <?php echo form_error('st_name'); ?>
                    </div>
                    <div class="form-group">
                        <h5>Address</h5>
                        <textarea name="st_address" class="form-control" id="" cols="30" rows="6"><?php echo set_value('st_address', ($store ? $store->st_address : '')); ?></textarea>
                        <?php echo form_error('st_address'); ?>
                    </div>
                    <div class="form-group">
                        <h5>Phone Number</h5>
                        <input class="form-control" name="st_phone" value="<?php echo set_value('st_phone', ($store ? $store->st_phone : ''));?>">
                        <?php echo form_error('st_phone'); ?>
                    </div>
                    <div class="form-group">
                        <h5>Download Link</h5>
                        <input class="form-control" name="st_dllink" value="<?php echo set_value('st_dllink', ($store ? $store->st_dllink : ''));?>">
                        <?php echo form_error('st_dllink'); ?>
                    </div>
                    <div class="form-group">
                        <h5>Store Description</h5>
                        <textarea name="st_desc" class="form-control" id="" cols="30" rows="6"><?php echo set_value('st_desc', ($store ? $store->st_desc : '')); ?></textarea>
                        <?php echo form_error('st_desc'); ?>
                    </div>
                    <div class="form-group">
                        <h5>Store Concept</h5>
                        <select class="form-control m-b" name="st_concept">
                            <?php
                                $sel = set_value('st_concept', ($store ? $store->st_concept : ''));
                                foreach($cst_store['st_concept'] as $key => $val){
                                    echo '<option value="'. $key.'" '. ($sel == $key ? 'selected' : '') .'>'. $val .'</option>';
                                }
                            ?>
                        </select>
                        <?php echo form_error('st_concept'); ?>
                    </div>
                    <div class="form-row">
                        <div class="col">
                            <h5>Latitude</h5>
                            <input class="form-control" name="st_lat" id="st_lat" value="<?php echo set_value('st_lat', ($store ? $store->st_lat : ''));?>">
                            <?php echo form_error('st_lat'); ?>
                        </div>
                        <div class="col">
                            <h5>Longitude</h5>
                            <input class="form-control" name="st_long" id="st_long" value="<?php echo set_value('st_long', ($store ? $store->st_long : ''));?>">
                            <?php echo form_error('st_long'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col">
                            <h5>Open Pickup Time</h5>
                            <input class="form-control" name="st_open" id="st_open" value="<?php echo set_value('st_open', ($store ? $store->st_open : ''));?>">
                            <?php echo form_error('st_open'); ?>
                        </div>
                        <div class="col">
                            <h5>Close Pickup Time</h5>
                            <input class="form-control" name="st_close" id="st_close" value="<?php echo set_value('st_close', ($store ? $store->st_close : ''));?>">
                            <?php echo form_error('st_close'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col">
                            <h5>Open Delivery Time</h5>
                            <input class="form-control" name="st_delivery_open" id="st_delivery_open" value="<?php echo set_value('st_delivery_open', ($store ? $store->st_delivery_open : ''));?>">
                            <?php echo form_error('st_delivery_open'); ?>
                        </div>
                        <div class="col">
                            <h5>Close Delivery Time</h5>
                            <input class="form-control" name="st_delivery_close" id="st_delivery_close" value="<?php echo set_value('st_delivery_close', ($store ? $store->st_delivery_close : ''));?>">
                            <?php echo form_error('st_delivery_close'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col">
                            <h5>Store Type</h5>
                            <select id="st_type" class="form-control m-b" name="st_type">
                                <?php
                                    $sel = set_value('st_type', ($store ? $store->st_type : ''));
                                    foreach($cst_store['type'] as $key => $val){
                                        echo '<option value="'. $key.'" '. ($sel == $key ? 'selected' : '') .'>'. $val .'</option>';
                                    }
                                ?>
                            </select>
                            <?php echo form_error('st_type'); ?>
                            <label class="checkbox-inline" <?php if(!isset($store->st_id)) { echo 'hidden'; }?> >
                                <input id="st_default_type" name="st_default_type" type="checkbox" value="1" > Set Store Type Permanently
                            </label>
                        </div>
                        <div class="col">
                            <h5>Store Status</h5>
                            <select id="st_status" class="form-control m-b" name="st_status">
                                <?php
                                    $sel = set_value('st_status', ($store ? $store->st_status : ''));
                                    foreach($cst_store['status'] as $key => $val){
                                        echo '<option value="'. $key.'" '. ($sel == $key ? 'selected' : '') .'>'. $val .'</option>';
                                    }
                                ?>
                            </select>
                            <?php echo form_error('st_status'); ?>
                            <label class="checkbox-inline" <?php if(!isset($store->st_id)) { echo 'hidden'; }?> >
                                <input id="st_default_status" name="st_default_status" type="checkbox" value="1" > Set Store Status Permanently
                            </label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col">
                            <h5>Courier Code</h5>
                            <select class="form-control m-b" name="courier_code">
                                <?php
                                    $sel = set_value('courier_code', (isset($st_courier['courier_code']) ? $st_courier['courier_code'] : ''));
                                    foreach($arr_code as $courier){
                                        echo '<option value="'. $courier->courier_code.'" '. ($sel == $courier->courier_code ? 'selected' : '') .'>'. $courier->courier_code .'</option>';
                                    }
                                ?>
                            </select>
                            <?php echo form_error('courier_code'); ?>
                        </div>
                        <div class="col">
                            <h5>Is Visibility</h5>
                            <select class="form-control m-b" name="is_visibility">
                                <?php
                                    $sel = set_value('is_visibility', ($store ? $store->is_visibility : ''));
                                    foreach($cst_store['is_visible'] as $key => $val){
                                        echo '<option value="'. $key.'" '. ($sel == $key ? 'selected' : '') .'>'. $val .'</option>';
                                    }
                                ?>
                            </select>
                            <?php echo form_error('is_visibility'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <h5>Barista</h5>
                        <label class="checkbox-inline">
                            <?php
                                $barista = set_value('barista', ((isset($st_courier['barista']) && $st_courier['barista'] == true ) ? 1 : 0));
                                $sel = ($barista == 1 ? 'checked' : '');
                            ?>
                            <input name="barista" type="checkbox" value="1" <?php echo $sel ;?> > Yes
                        </label>
                        <?php echo form_error('barista'); ?>
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
    $('#st_open, #st_close').datetimepicker({
        datepicker:false,
        format:'H:i',
        lang:'en'
    });
    // timepicker for search
    $('#st_delivery_open, #st_delivery_close').datetimepicker({
        datepicker:false,
        format:'H:i',
        lang:'en'
    });
    // If store type changed
    $("#st_type").change(function(){
        $("#st_default_type").prop("checked", true);
    });
    //  If store status changed
    $("#st_status").change(function(){
        $("#st_default_status").prop("checked", true);
    });
});
</script>