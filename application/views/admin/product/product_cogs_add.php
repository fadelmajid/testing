<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Product COGS</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
        <?php echo $msg; ?>
        <?php if ($show_form): ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open_multipart($current_url.'/'.($data ? $data->pdcogs_id : ''), ['id' => 'theform']); ?>
                    <input type="hidden" name="pdcogs_id" value="<?php echo set_value('pdcogs_id', $data ? $data->pdcogs_id : 0); ?>">
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Product Name</h5>
                            <select class="form-control m-b" name="pd_id">
                            <?php
                                $sel = set_value('pd_id', ($data ? $data->pd_id : ''));
                                foreach ($product as $product): ?>
                                <option value="<?php echo $product->pd_id; ?>" <?php echo $sel === $product->pd_id ? 'selected="selected"' : ''; ?>><?php echo $product->pd_name; ?></option>
                            <?php endforeach; ?>
                            </select>
                            <?php echo form_error('pd_id'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>COGS</h5>
                            <input class="form-control" name="pdcogs_price" value="<?php echo set_value('pdcogs_price', ($data ? $data->pdcogs_price : ''));?>">
                            <?php echo form_error('pdcogs_price'); ?>
                        </div>
                    </div>
                    <?php if(isset($data->start_date) OR !isset($data)){ ?>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>Start Date</h5>
                            <input class="form-control date" name="start_date" value="<?php echo set_value('start_date', ($data ? $data->start_date : '')); ?>"/>
                            <?php echo form_error('start_date'); ?>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>End Date</h5>
                            <input class="form-control date" name="end_date" value="<?php echo set_value('end_date', ($data ? $data->end_date : '')); ?>"/>
                            <?php echo form_error('end_date'); ?>
                        </div>
                    </div>
                    <?php }else{ ?>
                    <div class="form-row">
                        <div class="form-group col-6">
                            <h5>COGS Date</h5>
                            <input class="form-control" name="cogs_date" disabled value="<?php echo show_date(set_value('cogs_date', ($data ? $data->cogs_date : ''))); ?>"/>
                            <?php echo form_error('cogs_date'); ?>
                        </div>
                    </div>
                    <?php } ?>
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
        $('.date').datetimepicker({
            timepicker:false,
            format:'Y-m-d',
            lang:'en'
        });
    });
</script>