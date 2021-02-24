<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Subs Order Detail</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox order-detail">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-12">
                            <h2 class="font-bold m-b-xs">
                                #<?php echo $data['subs_order']->subsorder_code; ?>
                            </h2>
                            <hr>
                        </div>
                        <div class="col-md-3">
                            <h3>Customer</h3>
                            <dd>
                                <?php
                                    echo
                                        '<strong>'.$data['subs_order']->user_name.'<br></strong>'.
                                        $data['subs_order']->user_phone.'<br>'.
                                        $data['subs_order']->user_email
                                    ;
                                ?>
                            </dd>
                        </div>
                        <div class="col-md-3">
                            <h3>Date</h3>
                            <dd>
                                <?php
                                    $date = explode(', ', show_date($data['subs_order']->subsorder_date, true));
                                    echo
                                        $date[0].'<br>'.
                                        $date[1].'<br>'.
                                        '<strong>'.$data['subs_order']->pymtd_name.'</strong>'
                                    ;
                                ?>
                            </dd>
                        </div>
                        <div class="col-md-12"><hr></div>
                        <div class="col-md-2 font-bold">
                            <dd>Subtotal</dd>
                            <dd>Discount</dd>
                            <dd>Total</dd>
                        </div>
                        <div class="col-md-2">
                            <dd class="font-bold text-right"></dd>
                            <dd class="text-right"><?php echo number_format($data['subs_order']->subsorder_subtotal, 0, ',', '.'); ?></dd>
                            <dd class="text-right"><?php echo number_format($data['subs_order']->subsorder_discount, 0, ',', '.'); ?></dd>
                            <dd class="text-right"><?php echo number_format($data['subs_order']->subsorder_total, 0, ',', '.'); ?></dd>
                        </div>
                        <div class="col-md-9"></div>
                        <div class="col-md-12"><hr></div>
                    </div>
                </div>
            </div>
            <div class="ibox order-detail">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-12">
                            <h3 class="font-bold m-b-xs">Subscription List</h3>
                        </div>
                        <div class="col-md-12">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subs Plan Name</th>
                                    <th>Base Price</th>
                                    <th>Final Price</th>
                                    <th>Qty</th>
                                </tr>
                                </thead>
                                <tbody><?php
                                    $no = 1;
                                    foreach ($data['subs_order_detail'] as $list){
                                    ?>
                                <tr>
                                    <td style="width:10%"><?php echo $no; ?></td>
                                    <td style="width:20%"><?php echo ucwords($list->subsplan_name); ?></td>
                                    <td style="width:20%"><?php echo number_format($list->subsdetail_baseprice, 0, ',', '.'); ?></td>
                                    <td style="width:20%"><?php echo number_format($list->subsdetail_finalprice, 0, ',', '.'); ?></td>
                                    <td style="width:20%"><?php echo $list->subsdetail_qty; ?></td>
                                </tr>
                                <?php
                                        $no++;
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.content-wrapper -->