<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form?> Promo</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
            <?php echo $msg; ?>
            <?php if ($show_form) : ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open_multipart($current_url.'/'.($partner ? $partner->ptr_id : ''), array('id'=>'theform')); ?>
                        <input type="hidden" name="ptr_id" value="<?php echo set_value('ptr_id', $partner ? $partner->ptr_id : 0); ?>">
                        <div class="form-row">
                            <div class="form-group col-6">
                            <?php $total = count((array)$promo);?>
                                <h5>Promo List</h5>
                                <select class="form-control m-b" name="prm_id">
                                    <?php foreach ($promo as $key => $value): ?>
                                        <option value="<?php echo $value->prm_id?>"><?php echo $value->prm_name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php echo form_error('prm_id'); ?>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary"><?php echo $title_form?></button>
                        </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
            <?php endif ?>
        </div>
    </div>
</div>
<!-- /.content-wrapper -->