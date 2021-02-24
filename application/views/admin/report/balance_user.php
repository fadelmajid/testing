
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>


<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2>Balance per user</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <!-- /.row -->
    <div class="row">
        <div class="col-12">
            <div class="ibox">
                <div class="ibox-content">
                    <?php echo form_open($form_url, array('method'=>'get', 'class'=>'row form-inline')); ?>
                        <div class="form-group col-3">
                            <input type="text" name="search" placeholder="Search"value="<?php echo $search;?>" class="form-control col-12">
                        </div>
                        <div class="form-group col-5">
                            <div class="input-group date" id="search_date">
                                <input type="text" class="form-control" name="from" id="from" placeholder="Start date" value="<?php echo $from; ?>"/>
                                <span class="input-group-addon">to</span>
                                <input type="text" class="form-control" name="to" id="to" placeholder="End date" value="<?php echo $to; ?>" />
                            </div>
                        </div>
                        <div class="form-group col-2">
                            <button type="submit" class="btn btn-default">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>

                        <div class="pull-right">
                            <a href="<?php echo set_export_url($form_url,'export=xls');?>" class="btn btn-success btn-sm">&nbsp;<i class="fa fa-file-excel-o"></i>&nbsp;</a>
                        </div>
                    <?php echo form_close(); ?>
                    <div class="hr-line-dashed"></div>
                    <table class="table table-sticky table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo sort_table_icon($page_url, 'id', 'ID', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'name', 'Name', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'email', 'Email', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'phone', 'Phone', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'opening', 'Opening', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'topup', 'Topup', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'cashback', 'Cashback', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'transaction', 'Transaction', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'refund', 'Refund', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'withdraw', 'Withdraw', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'closing', 'Closing', $xtravar);?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($all_data as $value){
                                    echo '
                                        <tr>
                                            <td>'. $value->user_id .'</td>
                                            <td>'. $value->user_name .'</td>
                                            <td>'. $value->user_email .'</td>
                                            <td>'. $value->user_phone .'</td>
                                            <td align="right">'. number_format($value->total_opening_balace) .'</td>
                                            <td align="right">'. number_format($value->total_topup) .'</td>
                                            <td align="right">'. number_format($value->total_cashback) .'</td>
                                            <td align="right">'. number_format($value->total_transaction) .'</td>
                                            <td align="right">'. number_format($value->total_refund) .'</td>
                                            <td align="right">'. number_format($value->total_withdraw) .'</td>
                                            <td align="right">'. number_format($value->total_closing_balance) .'</td>
                                        </tr>
                                    ';
                                }
                                if(empty($all_data)){
                                    echo '
                                        <tr>
                                            <td class="error" colspan="100%">Data not found!</td>
                                        </tr>
                                    ';
                                }
                            ?>
                        </tbody>
                    </table>
                    <?php echo $pagination;?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.content-wrapper -->

<script type="text/javascript">
    $(function () {

        $('#from, #to').datetimepicker({
            timepicker:false,
            format:'Y-m-d',
            lang:'en'
        });

    });
</script>