<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper bdesc-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> User Download</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
        <?php echo $msg; ?>
        <?php if ($show_form): ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open_multipart($current_url.'/'.($user_download ? $user_download->usrd_id : ''), ['id' => 'theform']); ?>
                    <input type="hidden" name="usrd_id" value="<?php echo set_value('usrd_id', $user_download ? $user_download->usrd_id : 0); ?>">
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Type</h5>
                            <select class="form-control m-b" name="usrd_type" >
                                <?php 
                                    $sel = set_value('usrd_type', ($user_download ? $user_download->usrd_type : ''));
                                    foreach ($cst_type['usrd_type'] as $msg):
                                        echo '<option value="'. $msg.'" '. ($sel == $msg ? 'selected' : '') .'>'. $msg .'</option>';
                                    endforeach; ?>
                            </select>
                            <?php echo form_error('usrd_type'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Date</h5>
                            <input class="form-control" name="usrd_date" id="usrd_date" value="<?php echo set_value('usrd_date', ($user_download ? $user_download->usrd_date : '')); ?>">
                            <?php echo form_error('usrd_date'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Total</h5>
                            <input class="form-control" name="usrd_total" value="<?php echo set_value('usrd_total', ($user_download ? $user_download->usrd_total : '')); ?>">
                            <?php echo form_error('usrd_total'); ?>
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
<script type="text/javascript">
    //Untuk tanggal
    $(function () {
        $('#usrd_date').datetimepicker({
            timepicker:false,
            format:'Y-m-d',
            lang:'en'
        });
    });
</script>