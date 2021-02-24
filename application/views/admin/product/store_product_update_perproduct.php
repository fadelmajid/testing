<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2>Bulk Perproduct</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
        <?php echo $msg; ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <div class="hr-line-dashed"></div>
                        <?php echo form_open_multipart($current_url); ?>
                        <input type="hidden" name="stpd_id">
                        <div class="form-row">
                            <div class="form-group col-6">
                                <h4>Search Product</h4>
                                <input type="text" class="form-control" data-parsley-type="text" value="<?php echo set_value('pd_name', (isset($pd_name) && !empty($pd_name)) ? $pd_name : '');?>" id="pd_name" name="pd_name" require />
                                <input type="hidden" name="pd_id" id="pd_id" data-parsley-type="text" value="<?php echo set_value('pd_id', (isset($pd_name) && !empty($pd_name)) ? $pd_id : '');?>">
                                <?php
                                        if(form_error('pd_id') == form_error('pd_name')){
                                            //untuk validasi jika name kosong, id kosong, dan id tdk valid
                                            echo form_error('pd_id');
                                        }else{
                                            //untuk validasi jika id tdk valid
                                            echo form_error('pd_id');
                                            //untuk validasi jika name kosong
                                            echo form_error('pd_name');
                                        }
                                ?>                            </div>
                            <div class="form-group col-6">
                                <h4>Status</h4>
                                <select class="form-control m-b" name="stpd_status" id="stpd_status">
                                    <?php
                                        foreach($cst_status as $key => $val){
                                            echo '<option value="'. $key.'" '. (set_value('stpd_status', $key) == $key ? 'selected' : '') .'>'.$val.'</option>';
                                        }
                                    ?>
                                </select>
                                <?php echo form_error('stpd_status'); ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label class="checkbox-inline">
                                    <?php
                                        $all_st = set_value('all_st',(isset($st) ? 1 : 0));
                                        $sel = ($all_st == 1 ? 'checked' : '');
                                    ?>
                                    <h4><input name="all_st" class="change-checkbox all-st"  id="all-st" type="checkbox" value="1" <?php echo $sel; ?> >&nbspAll Store</h4>
                                </label>
                                <?php echo form_error('all_st'); ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6 all-ste">
                                <label class="checkbox-inline">
                                    <?php
                                        $all_ste = set_value('all_ste',(isset($ste) ? 1 : 0));
                                        $sel = ($all_ste == 1 ? 'checked' : '');
                                    ?>
                                    <h4><input name="all_ste" class="change-checkbox all-ste" id="all-ste" type="checkbox" value="1" <?php echo $sel; ?> >&nbspAll Store with Exception</h4>
                                </label>
                                <?php echo form_error('all_ste'); ?>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6 st-id">
                                <h4 id="title"></h4>
                                <select data-placeholder="Choose Stores" name="st_id[]" class="chosen-select st-id-disabled" multiple="" style="width: 350px; display: none;" tabindex="-1">
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
                            <button type="submit" class="btn btn-primary">Process</button>
                            <button type="reset" class="btn btn-warning">Reset</button>
                        </div>
                    <?php echo form_close(); ?> 
                    <span class="text-info font-bold">Selected Product will be added to selected Stores.</span>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.content-wrapper -->
<script src="<?php echo ADMIN_TEMPLATE_URL;?>js/jquery-ui.js"></script>
<script>
  $(document).ready(function() {
		$(function () {
			let $id                   = $("#pd_id")
			let $name                 = $("#pd_name")
            
			$name.autocomplete({
				minLength: 3,
				source: "<?php echo base_url();?>ajax/get_product",
				select: function (event, ui) {
					let { pd_id, pd_name} = ui.item
					$name.val(pd_name)
					$id.val(pd_id)
				},
				response: function (event, ui) {
					ui.content.forEach((row) => {
						row.label = row.pd_name
						row.value = row.pd_name
					})
				}
            })
            
        });



    // change checkbox -> untuk action ketika di klik
    $('#all-ste').click(function(){
        if ($(this).is(":checked") == true) {
            $('.all-st').attr('disabled', true);
            $('.st-id').css("display","block");
            $('#title').text("Select Store");
            $('.chosen-select').chosen({width: "100%"});
        }else{
            $('.all-st').attr('disabled', false);
            $('.st-id').css("display","none");
        }
    });

    $('#all-st').click(function(){
        if ($(this).is(":checked") == true) {
            $('.all-ste').attr('disabled', true);
            $('.st-id-disabled').attr('disabled', true);
        }else{
            $('.all-ste').attr('disabled', false);
            $('.st-id-disabled').attr('disabled', false);
        }
    });  

    // change checkbox -> untuk action ketika di load
    if ($('#all-ste').is(":checked") == true){
        $('.all-st').attr('disabled', true);
        $('.st-id').css("display","block");
        $('#title').text("Select Store");
        $('.chosen-select').chosen({width: "100%"});
    }else{
        $('.all-st').attr('disabled', false);
        $('.st-id').css("display","none");
    }

    if ($('#all-st').is(":checked") == true) {
        $('.all-ste').attr('disabled', true);
        $('.st-id-disabled').attr('disabled', true);
    }else{
        $('.all-ste').attr('disabled', false);
        $('.st-id-disabled').attr('disabled', false);
    }  

});
</script>
<script src="<?php echo ADMIN_TEMPLATE_URL;?>js/plugins/chosen/chosen.jquery.js"></script>
<script src="<?php echo ADMIN_TEMPLATE_URL;?>js/plugins/select2/select2.full.min.js"></script>