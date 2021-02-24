<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2>Bulk Perstore</h2>
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
                        <div class="form-row">
                            <div class="form-group col-6">
                                <h4>Search Store</h4>
                                <input type="text" class="form-control" data-parsley-type="text" value="<?php echo set_value('st_name', (isset($st_name) && !empty($st_name)) ? $st_name : '');?>" id="st_name" name="st_name" require />
                                <input type="hidden" name="st_id" id="st_id" data-parsley-type="text" value="<?php echo set_value('st_id', (isset($st_name) && !empty($st_name)) ? $st_id : '');?>">
                                <?php
                                        if(form_error('st_id') == form_error('st_name')){
                                            //untuk validasi jika name kosong, id kosong, dan id tdk valid
                                            echo form_error('st_id');
                                        }else{
                                            //untuk validasi jika id tdk valid
                                            echo form_error('st_id');
                                            //untuk validasi jika name kosong
                                            echo form_error('st_name');
                                        }
                                ?>
                            </div>
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
                            <?php
                                foreach($list_matrix as $key => $value){
                            ?>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <h4>
                                    <input type="checkbox"  name="cat_id[]" id="<?php echo 'cat_'.$key; ?>" value="<?php echo $value['cat_id'] ?>" <?php echo (in_array($value['cat_id'], set_value('cat_id', $cat_id)) ? 'checked':''); ?> />&nbspSelect All <?php echo $value['cat_name']; ?>
                                </h4>
                            </div>
                        </div>
                        <div class="form-row">
                            <?php
                                echo '
                                    <div class="form-group pd_'.$key.' col-6">
                                        <select data-placeholder="Choose Products" name="pd_id[]" class="chosen-select" multiple="" style="width: 350px; display: block;" tabindex="-1">';
                                            //start looping select
                                            foreach($value['product'] as $value_product){
                                                echo '<option value="'. $value_product->pd_id .'" '. (in_array($value_product->pd_id, set_value('pd_id', $pd_id)) ? 'selected':'') .' >'. $value_product->pd_name  .'</option>';
                                            }
                                    echo'</select>';
                                    echo form_error('pd_id[]');
                                    echo'
                                    </div>
                                    </div>';
                                }
                            ?>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Process</button>
                            <button type="reset" class="btn btn-warning">Reset</button>
                        </div>
                    </div>
                    <?php echo form_close(); ?> 
                    <span class="text-info font-bold">Selected Store will be added to selected Products.</span>
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
			let $id                 = $("#st_id")
			let $name               = $("#st_name")
            
			$name.autocomplete({
				minLength: 3,
				source: "<?php echo base_url();?>ajax/get_store",
				select: function (event, ui) {
					let { st_id, st_name} = ui.item
					$name.val(st_name)
					$id.val(st_id)
				},
				response: function (event, ui) {
					ui.content.forEach((row) => {
						row.label = row.st_name
						row.value = row.st_name
					})
				}
            })
            
        });
    
    $('.chosen-select').chosen({width: "100%"});
    
    // change checkbox
    $("input[id^=cat_]").each(function(i){

        //-> untuk action ketika di klik
        $(this).on("click", {x:i}, function(event){
            if ($(this).is(":checked") == false) {
                $('.pd_'+ event.data.x).css("display","block");
            }else{
                $('.pd_'+ event.data.x).css("display","none");
            }
        });

        //-> untuk action ketika di load
        if ($(this).is(":checked") == false) {
            $('.pd_'+ i).css("display","block");
        }else{
            $('.pd_'+ i).css("display","none");
        }

    });

});
</script>
<script src="<?php echo ADMIN_TEMPLATE_URL;?>js/plugins/chosen/chosen.jquery.js"></script>
<script src="<?php echo ADMIN_TEMPLATE_URL;?>js/plugins/select2/select2.full.min.js"></script>