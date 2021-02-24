<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Bank</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
            <?php echo $msg; ?>
            <?php if ($show_form) : ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open_multipart($current_url.'/'.($static_bank ? $static_bank->bank_id : ''), array('id'=>'theform')); ?>
                        <input type="hidden" name="bank_id" value="<?php echo set_value('bank_id', $static_bank ? $static_bank->bank_id : 0); ?>">
                        <div class="form-group">
                            <h5>Bank Code</h5>
                            <input class="form-control" name="bank_code" value="<?php echo set_value('bank_code', ($static_bank ? $static_bank->bank_code : ''));?>">
                            <?php echo form_error('bank_code'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Bank Name</h5>
                            <input class="form-control" name="bank_name" value="<?php echo set_value('bank_name', ($static_bank ? $static_bank->bank_name : ''));?>">
                            <?php echo form_error('bank_name'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Bank Guidances</h5>
                            <textarea class="form-control" name="bank_guidances" value="<?php echo set_value('bank_guidances');?>"><?php echo $static_bank ? $static_bank->bank_guidances : ''; ?></textarea>
                            <?php echo form_error('bank_guidances'); ?>
                        </div>
                        <div class="form-group">
                                <h5>Bank Image</h5>
                                <input type="file" name="bank_img" class="form-control" />
                                <?php echo form_error('bank_img'); ?>
                                <?php
                                    if(isset($static_bank->bank_img) && $static_bank->bank_img != ''){
                                        echo '<br/><img src="'.UPLOAD_URL.$static_bank->bank_img.'" style="max-width:400px"/>';
                                    }
                                ?>
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