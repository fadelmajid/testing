<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> FAQ</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-6">
            <?php echo $msg; ?>
            <?php if ($show_form) : ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open($current_url.'/'.($static_faq ? $static_faq->faq_id : ''), array('id'=>'theform')); ?>
                        <input type="hidden" name="faq_id" value="<?php echo set_value('faq_id', $static_faq ? $static_faq->faq_id : 0); ?>">
                        <div class="form-group">
                            <h5>FAQ Question</h5>
                            <input class="form-control" name="faq_question" value="<?php echo set_value('faq_question', ($static_faq ? $static_faq->faq_question : ''));?>">
                            <?php echo form_error('faq_question'); ?>
                        </div>
                        <div class="form-group">
                            <h5>FAQ Answer</h5>
                            <input class="form-control" name="faq_answer" value="<?php echo set_value('faq_answer', ($static_faq ? $static_faq->faq_answer : ''));?>">
                            <?php echo form_error('faq_answer'); ?>
                        </div>
                        <div class="form-group">
                            <h5>FAQ Order</h5>
                            <input class="form-control" name="faq_order" value="<?php echo set_value('faq_order', ($static_faq ? $static_faq->faq_order : ''));?>">
                            <?php echo form_error('faq_order'); ?>
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