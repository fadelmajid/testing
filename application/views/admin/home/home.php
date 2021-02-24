<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-sm-12">
        <h2>Dashboard</h2>
    </div>
</div>

<div class="wrapper wrapper-content">
    <!-- START widgets box-->
    <div class="row">
        <div class="col-lg-3">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5 data-toggle="tooltip" data-placement="bottom" title="Total Cups yang telah selesai">Total Order Completed</h5>
                    <div class="ibox-tools">
                        <span class="label label-info float-right">Today</span>
                    </div>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo number_format($total_order); ?></h1>
                    <small>Orders</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5 data-toggle="tooltip" data-placement="bottom" title="Total Order yang masih status paid dengan Metode Delivery">Total Paid Delivery</h5>
                    <div class="ibox-tools">
                        <span class="label label-info float-right">Today</span>
                    </div>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo number_format($total_paid); ?></h1>
                    <small>Orders</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5 data-toggle="tooltip" data-placement="bottom" title="Total Order yang belum selesai dengan Metode Pickup">Total Pickup Uncompleted</h5>
                    <div class="ibox-tools">
                        <span class="label label-info float-right">Today</span>
                    </div>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo number_format($total_pickup); ?></h1>
                    <small>Orders</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5 data-toggle="tooltip" data-placement="bottom" title="Total Order yang belum selesai dengan Metode Delivery">Total Delivery Uncompleted</h5>
                    <div class="ibox-tools">
                        <span class="label label-info float-right">Today</span>
                    </div>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins"><?php echo number_format($total_delivery); ?></h1>
                    <small>Orders</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5 data-toggle="tooltip" data-placement="bottom" title="Total Order yang belum selesai dengan Metode Delivery">Store Product Out of Stock</h5>
                    <div class="ibox-tools">
                        <span class="label label-info float-right">Today</span>
                    </div>
                </div>
                <div class="ibox-content">
                    <?php 
                        if(!empty($list_store_out_of_stock)){
                            foreach($list_store_out_of_stock as $value){ ?>
                                <h3 class="no-margins"><a href="<?php echo $product_url; ?>?search=out_of_stock&st_id=<?php echo $value->st_id; ?>"><?php echo $value->st_name;?></a></h3>
                    <?php   }  
                        }else{ ?>
                            <h3>-</h3> <?php
                        }
                    ?>
                        
                    
                    
                </div>
            </div>
        </div>
    </div>

    <!-- END widgets box-->
</div>
<!-- /.content-wrapper -->