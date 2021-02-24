<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-sm-12">
        <h2>Cohort</h2>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <!-- /.row -->
    <div class="row">
        <div class="col-12">
            <div class="ibox">
                <div class="ibox-content">
                <?php echo form_open($form_url, array('method'=>'get', 'class'=>'row form-inline')); ?>
                <div class="form-row" style="padding-left:15px;">
                    <div class="col-4" style="padding-bottom:15px;">
                        <div class="input-group date" id="search_date">
                            <label>To : &nbsp;</label><input type="text" class="form-control" name="to" id="to" placeholder="End date" value="<?php echo $to; ?>" />
                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>
                    <div class="col-12" >
                        <?php echo '<button type="submit" name="export" value="total_transaction_xls" class="btn btn-success btn-sm">&nbsp;<i class="fa fa-file-excel-o"></i>&nbsp;Total Transaction</button>'; ?>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="col-12" >
                        <?php echo '<button type="submit" name="export" value="data_xls" class="btn btn-success btn-sm">&nbsp;<i class="fa fa-file-excel-o"></i>&nbsp;Cohort Data</button>'; ?>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="col-12" >
                        <?php echo '<button type="submit" name="export" value="topup_user_xls" class="btn btn-success btn-sm">&nbsp;<i class="fa fa-file-excel-o"></i>&nbsp;Cohort Topup User</button>'; ?>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="col-12" >
                        <?php echo '<button type="submit" name="export" value="referral_xls" class="btn btn-success btn-sm">&nbsp;<i class="fa fa-file-excel-o"></i>&nbsp;Cohort Referral</button>'; ?>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="col-12" >
                        <?php echo '<button type="submit" name="export" value="paid_cups_xls" class="btn btn-success btn-sm">&nbsp;<i class="fa fa-file-excel-o"></i>&nbsp;Cohort Paid Cups</button>'; ?>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="col-12" >
                        <?php echo '<button type="submit" name="export" value="free_cups_xls" class="btn btn-success btn-sm">&nbsp;<i class="fa fa-file-excel-o"></i>&nbsp;Cohort Free Cups</button>'; ?>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="col-12" >
                        <?php echo '<button type="submit" name="export" value="voucher_complimentary_xls" class="btn btn-success btn-sm">&nbsp;<i class="fa fa-file-excel-o"></i>&nbsp;Cohort Voucher Complimentary</button>'; ?>
                    </div>
                    </div>
                <?php echo form_close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.content-wrapper -->

<script type="text/javascript">
    $(function () {

        $('#to').datetimepicker({
            timepicker:false,
            format:'Y-m-d',
            lang:'en'
        });

        $('#to').change(function (){
            $('#to').val();
        });

    });
</script>	