<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2>Import Store Products</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
        <?php echo $msg; ?>
        <?php if ($show_form): ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                        <?php echo form_open_multipart($current_url); ?>
                        <div class="form-row">
                                <div class="form-group col-6">
                                    <label class="checkbox-inline">
                                            <?php
                                                $all_pd = set_value('all_pd',(isset($pd) ? 1 : 0));
                                                $sel = ($all_pd == 1 ? 'checked' : '');
                                            ?>
                                            <h4><input name="all_pd" class="change-checkbox" type="checkbox" value="1" <?php echo $sel; ?> >&nbspAll Product</h4>
                                    </label>
                                    <?php echo form_error('all_pd'); ?>
                                </div>
                        </div>
                        <div class="form-row">
                                <div class="form-group col-6 pd-id">
                                <h4>Select Product</h4>
                                    <select data-placeholder="Choose Products" name="pd_id[]" class="chosen-select" multiple="" style="width: 350px; display: none;" tabindex="-1">
                                        <?php
                                            foreach ($product_list as $list_product){
                                               echo '<option value="'. $list_product->pd_id .'" '. (in_array($list_product->pd_id, set_value('pd_id', $pd_id)) ? 'selected':'') .' >'. $list_product->pd_name  .'</option>';
                                            }
                                        ?>
                                    </select>
                                    <?php echo form_error('pd_id[]'); ?>
                                </div>
                        </div>
                        <div class="form-row">
                                <div class="form-group col-6">
                                    <h4>Select Store</h4>
                                    <select data-placeholder="Choose Stores" name="st_id[]" class="chosen-select" multiple="" style="width: 350px; display: none;" tabindex="-1">
                                        <?php
                                            foreach ($store_list as $list_store){ 
                                                echo '<option value="'. $list_store->st_id .'" '. (in_array($list_store->st_id, set_value('st_id', $st_id)) ? 'selected':'') .' >'. $list_store->st_name  .'</option>';
                                            }
                                        ?>
                                        </select>
                                    <?php echo form_error('st_id[]'); ?>
                                </div>
                        </div>
                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                    <?php echo form_close(); ?>
                    <span class="text-info font-bold">Selected Products will be added to selected Stores.</span>
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

    // change checkbox -> untuk action ketika di klik
    $('.change-checkbox').click(function(){
        if ($(this).is(":checked") == false) {
            $('.pd-id').css("display","block");
        }else{
            $('.pd-id').css("display","none");
        }
    });

    // change checkbox -> untuk action ketika di load
    if ($('.change-checkbox').is(":checked") == false){
        $('.pd-id').css("display","block");
    }else{
        $('.pd-id').css("display","none");
    }

});
</script>
<script src="<?php echo ADMIN_TEMPLATE_URL;?>js/plugins/chosen/chosen.jquery.js"></script>
<script src="<?php echo ADMIN_TEMPLATE_URL;?>js/plugins/select2/select2.full.min.js"></script>