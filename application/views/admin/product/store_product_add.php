<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Store Product</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
        <?php echo $msg; ?>
        <?php if ($show_form): ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open($current_url.'/', ['id' => 'theform']); ?>
                    <div class="form-row">
                        <div class="form-group col-4">
                            <h5>Store</h5>
                            <input type="text" data-provide="typeahead" data-source='[<?php echo $store_list; ?>]' name="st_name" placeholder="Insert Store Name..."  class="form-control" value="<?php echo set_value('st_name', ''); ?>" autocomplete="off" />
                            <?php echo form_error('st_name'); ?>
                        </div>
                        <div class="form-group col-4">
                            <h5>Product</h5>
                            <input type="text" data-provide="typeahead" data-source='[<?php echo $product_list; ?>]' name="pd_name" placeholder="Insert Product Name..." class="form-control" value="<?php echo set_value('pd_name', ''); ?>" autocomplete="off" />
                            <?php echo form_error('pd_name'); ?>
                        </div>
                        <div class="form-group col-2">
                            <h5>&nbsp;</h5>
                            <button type="submit" class="btn btn-primary"><?php echo $title_form;?></button>
                        </div>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        <?php endif ?>
        </div>
    </div>
</div>
<!-- /.content-wrapper -->
