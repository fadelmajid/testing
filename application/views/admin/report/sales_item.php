
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>


<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2>Sales per item</h2>
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
                        <div class="form-group col-3">
                            <div class="input-group date" id="search_date">
                                <input type="text" class="form-control" name="from" id="from" placeholder="Start date" value="<?php echo $from; ?>"/>
                                <span class="input-group-addon">to</span>
                                <input type="text" class="form-control" name="to" id="to" placeholder="End date" value="<?php echo $to; ?>" />
                            </div>
                        </div>
                        <div class="form-group col-2">
                            <div class="input-group select" id="select_store">
                                <select name="st_id" class="form-control m-b" id="select_store">
                                <?php
                                    if($store_permits == 0){
                                        echo '<option value="0">All Store</option>';
                                    }
                                    foreach($store_data as $stores) {
                                        if($store_permits == $stores->st_id) {
                                            echo '<option value="'. $stores->st_id .'" '. ($stores->st_id == $st_id ? "selected" : "").'>'. $stores->st_name .'</option>';
                                        } else if($store_permits == 0){
                                            echo '<option value="'. $stores->st_id .'" '. ($stores->st_id == $st_id ? "selected" : "").'>'. $stores->st_name .'</option>';
                                        }
                                    }
                                ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-2">
                            <div class="input-group select" id="select_store">
                                <select name="delivery_type" class="form-control m-b" id="select_delivery_type">
                                    <option value="all">All Delivery Status</option>
                                <?php
                                    foreach($delivery_type as $type) {
                                        echo '<option value="'. $type.'" '. ($type == $curr_delivery ? "selected" : "") .' >'. ucfirst($type) .'</option>';
                                    }
                                ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-1">
                            <button type="submit" class="btn btn-default">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>

                        <div class="pull-right">
                            <a href="<?php echo set_export_url($form_url,'export=xls');?>" class="btn btn-success btn-sm">&nbsp;<i class="fa fa-file-excel-o"></i>&nbsp;</a>
                        </div>
                    <?php echo form_close(); ?>
                    <div class="hr-line-dashed"></div>
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
                    <table class="table table-sticky table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo sort_table_icon($page_url, 'date', 'Date', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'name', 'Customer', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'order', 'Order', $xtravar);?></th>
                                <th>Payment Method</th>
                                <th><?php echo sort_table_icon($page_url, 'status', 'Status', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'product', 'Product', $xtravar);?></th>
                                <th>COGS</th>
                                <th>Price</th>
                                <th>Disc Per Item</th>
                                <th>Qty</th>
                                <th>Total Price</th>
                                <th>Total Disc Per Item</th>
                                <th>Grand Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($all_data as $value){
                                    echo '
                                        <tr>
                                            <td>'. show_date($value->uor_date, true) .'</td>
                                            <td>'. $value->user_id .'</td>
                                            <td>'. $value->uor_code .' ['. $value->uor_delivery_type .']<br>'. $value->st_name .'<br/>'. 
                                            (isset($value->vc_code) && !empty($value->vc_code) ? $value->vc_code : '') .'</td>
                                            <td>'. $value->pymtd_name .'</td>
                                            <td>'. $value->uor_status .'</td>
                                            <td>'. $value->uorpd_name .'</td>
                                            <td align="right">'. ( isset($cogs[$value->pd_id][date("Y-m-d", strtotime($value->uor_date))]) ?  number_format($cogs[$value->pd_id][date("Y-m-d", strtotime($value->uor_date))]) : 0 ).'</td>
                                            <td align="right">'. number_format($value->uorpd_final_price) .'</td>
                                            <td align="right">'. number_format($value->discount) .'</td>
                                            <td align="right">'. number_format($value->uorpd_qty) .'</td>
                                            <td align="right">'. number_format($value->uorpd_final_price * $value->uorpd_qty) .'</td>
                                            <td align="right">'. number_format($value->discount * $value->uorpd_qty) .'</td>
                                            <td align="right">'. number_format($value->total) .'</td>
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