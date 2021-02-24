<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper bdesc-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Courier</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
        <?php echo $msg; ?>
        <?php if ($show_form): ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open_multipart($current_url.'/'.($courier ? $courier->courier_id : ''), ['id' => 'theform']); ?>
                    <input type="hidden" name="id" value="<?php echo set_value('courier_id', $courier ? $courier->courier_id : 0); ?>">
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Courier Code</h5>
                            <input class="form-control" name="courier_code" value="<?php echo set_value('courier_code', ($courier ? $courier->courier_code : '')); ?>">
                            <?php echo form_error('courier_code'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Courier Vendor</h5>
                            <input class="form-control" name="courier_vendor" value="<?php echo set_value('courier_vendor', ($courier ? $courier->courier_vendor : '')); ?>">
                            <?php echo form_error('courier_vendor'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Courier Description</h5>
                            <input class="form-control" name="courier_desc" value="<?php echo set_value('courier_desc', ($courier ? $courier->courier_desc : '')); ?>">
                            <?php echo form_error('courier_desc'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <h5>Is Default</h5>
                                <div class="form-group">
                                    <label class="checkbox-inline">
                                        <?php
                                            $is_default = set_value('is_default', ((isset($courier->is_default) && $courier->is_default == 1) ? 1 : 0));
                                            $sel = ($is_default == 1 ? 'checked' : '');
                                        ?>
                                        <input name="is_default" type="checkbox" value="1" <?php echo $sel ;?> > Yes
                                    </label>
                                    <?php echo form_error('delivery_included'); ?>
                                </div>
                            <?php echo form_error('is_default'); ?>
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