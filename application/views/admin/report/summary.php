
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>


<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2>Summary</h2>
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
                            <div class="input-group date" id="search_date">
                                <input type="text" class="form-control" name="from" id="from" placeholder="Start date" value="<?php echo $from; ?>"/>
                                <span class="input-group-addon">to</span>
                                <input type="text" class="form-control" name="to" id="to" placeholder="End date" value="<?php echo $to; ?>" />
                            </div>
                        </div>
                        <div class="form-group col-5">
                            <button type="submit" class="btn btn-default">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>

                        <div class="pull-right">
                            <a href="<?php echo set_export_url($form_url,'export=xls');?>" class="btn btn-success btn-sm">&nbsp;<i class="fa fa-file-excel-o"></i>&nbsp;</a>
                        </div>
                    <?php echo form_close(); ?>

                    <div class="hr-line-dashed"></div>
                    <h2><strong>Order</strong></h2>
                    <div class="col-md-12"><hr></div>
                    <table border="0">
                        <tr>
                            <td align="left"><strong>Total Order : </strong></td>
                            <td align="right"><?php echo number_format($total_report->total_order); ?></td>
                            <td width="10%"></td>
                            <td align="left"><strong>Total Cups Free: </strong></td>
                            <td align="right"><?php echo number_format($total_report->total_cups_free); ?></td>
                            <td width="10%"></td>
                            <td align="left"><strong>Total Cups Pickup: </strong></td>
                            <td align="right"><?php echo number_format($total_report->total_cups_pickup); ?></td>
                            <td width="10%"></td>
                            <td align="left"><strong>Total Espresso Base: </strong></td>
                            <td align="right"><?php echo number_format($total_report->total_espresso_base); ?></td>
                            <td width="10%"></td>
                        </tr>
                        <tr>
                            <td align="left"><strong>Total Cups : </strong></td>
                            <td align="right"><?php echo number_format($total_report->total_cups); ?></td>
                            <td width="10%"></td>
                            <td align="left"><strong>Total Cups Paid: </strong></td>
                            <td align="right"><?php echo number_format($total_report->total_cups_paid); ?></td>
                            <td width="10%"></td>
                            <td align="left"><strong>Total Cups Delivery: </strong></td>
                            <td align="right"><?php echo number_format($total_report->total_cups_delivery); ?></td>
                            <td width="10%"></td>
                            <td align="left"><strong>Total Tea by TWG : </strong></td>
                            <td align="right"><?php echo number_format($total_report->total_tea_by_twg); ?></td>
                            <td width="10%"></td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>        
                        </tr>
                        <tr>
                            <td align="left"><strong>Sub Total : </strong></td>
                            <td align="right"><?php echo number_format($total_report->sub_total); ?></td>
                            <td width="10%"></td>
                            <td align="left"><strong>Discount : </strong></td>
                            <td align="right"><?php echo number_format($total_report->total_disc); ?></td>
                            <td width="10%"></td>
                            <td align="left"><strong>Total : </strong></td>
                            <td align="right"><?php echo number_format($total_report->total); ?></td>
                        </tr>
                        <tr>
                            <td align="left"><strong>Delivery Fee : </strong></td>
                            <td align="right"><?php echo number_format($total_report->total_delivery_fee); ?></td>
                            <td width="10%"></td>
                            <td align="left"><strong>Disc Delivery Fee : </strong></td>
                            <td align="right"><?php echo number_format($total_report->disc_delivery_fee); ?></td>
                            <td width="10%"></td>
                            <td align="left"><strong>Grand Total : </strong></td>
                            <td align="right"><?php echo number_format($total_report->grand_total); ?></td>
                        </tr>
                    </table>

                    <div class="hr-line-dashed"></div>
                    <h2><strong>Subscriptions</strong></h2>
                    <div class="col-md-12"><hr></div>
                    <?php 
                        $jum_data = count($all_data);
                        $total_cash_in = $total_report->grand_total;
                        foreach($all_data as $key => $total_subs){ 
                            if(!empty($all_data)){
                                if($key % 2 == 0){ 
                    ?> 
                                    <div class="row">
                                        <div class="col-md-6">
                                            <dd><h3><?php echo $total_subs->subsplan_name; ?></h3></dd>
                                            <div class="col-md-6"><hr></div>
                                            <div class="row">
                                                <div class="col-md-3 font-bold">
                                                    <dd><strong>Total</strong></dd>
                                                    <dd><strong>Total Price</strong></dd>
                                                    <dd><strong>Total Subscriber</strong></dd>
                                                    <dd><strong>Voucher Used</strong></dd>
                                                    <dd><strong>Voucher Expired</strong></dd>
                                                    <dd><strong>Voucher Unused</strong></dd>
                                                </div>
                                                <div class="col-md-2">
                                                    <dd class="text-right"><?php echo number_format($total_subs->total_order); ?></dd>
                                                    <dd class="text-right"><?php echo number_format($total_subs->total_price); ?></dd>
                                                    <dd class="text-right"><?php echo number_format($total_subs->total_subscriber); ?></dd>
                                                    <dd class="text-right"><?php echo number_format($total_subs->total_voucher_used); ?></dd>
                                                    <dd class="text-right"><?php echo number_format($total_subs->total_voucher_expired); ?></dd>
                                                    <dd class="text-right"><?php echo number_format($total_subs->total_voucher_unused); ?></dd>
                                                </div>
                                            </div>
                                        </div>
                        <?php   }else{ ?>
                                        <div class="col-md-6">
                                            <dd><h3><?php echo $total_subs->subsplan_name; ?></h3></dd>
                                            <div class="col-md-6"><hr></div>
                                            <div class="row">
                                                <div class="col-md-3 font-bold">
                                                    <dd><strong>Total</strong></dd>
                                                    <dd><strong>Total Price</strong></dd>
                                                    <dd><strong>Total Subscriber</strong></dd>
                                                    <dd><strong>Voucher Used</strong></dd>
                                                    <dd><strong>Voucher Expired</strong></dd>
                                                    <dd><strong>Voucher Unused</strong></dd>
                                                </div>
                                                <div class="col-md-2">
                                                    <dd class="text-right"><?php echo number_format($total_subs->total_order); ?></dd>
                                                    <dd class="text-right"><?php echo number_format($total_subs->total_price); ?></dd>
                                                    <dd class="text-right"><?php echo number_format($total_subs->total_subscriber); ?></dd>
                                                    <dd class="text-right"><?php echo number_format($total_subs->total_voucher_used); ?></dd>
                                                    <dd class="text-right"><?php echo number_format($total_subs->total_voucher_expired); ?></dd>
                                                    <dd class="text-right"><?php echo number_format($total_subs->total_voucher_unused); ?></dd>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12"><hr></div>
                        <?php      }

                            }
                            $total_cash_in = $total_cash_in + $total_subs->total_price;    
                        }
                        ?>
                        
                        <div class="col-md-12"><hr></div>
                        <div class="col-md-2"><h3>Total Cash-In : <?php echo number_format($total_cash_in); ?></h3></div>
                    
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