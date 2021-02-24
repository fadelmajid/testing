<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Store Image</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
            <?php echo $msg; ?>
            <?php if ($show_form) : ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open_multipart($current_url.'/'.(isset($store_img->sti_id) ? $store_img->sti_id : ''), array('id'=>'theform')); ?>
                        <input type="hidden" name="sti_id" value="<?php echo set_value('sti_id', isset($store_img->sti_id) ? $store_img->sti_id : 0); ?>">
                        <div class="form-group">
                            <h5>Search Store</h5>
                            <input type="text" class="form-control" data-parsley-type="text" value="<?php echo set_value('st_name', isset($store_img->st_name) ? $store_img->st_name : '');?>" id="st_name" name="st_name" require />
                            <input type="hidden" name="st_id" id="st_id" data-parsley-type="text" value="<?php echo set_value('st_id', isset($store_img->st_name) ? $store_img->st_id : '');?>">
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
                        <div class="form-group">
                            <h5>Store Image</h5>
                            <input type="file" name="sti_img[]" multiple class="form-control" />
                            <?php echo form_error('sti_img[]'); ?>
                            <?php
                                if(isset($store_img->sti_img) && $store_img->sti_img != ''){
                                    echo '<br/><img src="'.UPLOAD_URL.$store_img->sti_img.'" style="max-width:400px"/>';
                                }
                            ?>
                        </div>
                        <div class="form-group">
                            <h5>Order</h5>
                            <input class="form-control" name="sti_order" value="<?php echo set_value('sti_order', (isset($store_img->sti_order) ? $store_img->sti_order : ''));?>"/>
                            <?php echo form_error('sti_order'); ?>
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
<script src="<?php echo ADMIN_TEMPLATE_URL;?>js/jquery-ui.js"></script>
<script>
$(document).ready(function(){
		$(function () {
			let $name    = $("#st_name")
			let $id      = $("#st_id")
            
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
});
</script>
