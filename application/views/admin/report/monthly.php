<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-sm-12">
        <h2>Report Monthly</h2>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <!-- /.row -->
    <div class="row">
        <div class="col-12">
            <div class="ibox">
                <div class="ibox-content">
                <?php echo form_open($form_url, array('method'=>'get', 'class'=>'row form-inline')); ?>
                <div class="form-group col-4">
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
                <?php 
                // BEGIN PERMISSION ACTION
                if(in_array('export', $permits)){ 
                    echo '<div class="pull-right">
                        <a href="'.set_export_url($form_url, "export=xls").'" class="btn btn-success btn-sm">&nbsp;<i class="fa fa-file-excel-o"></i>&nbsp;</a>
                    </div>';
                }

                // END PERMISSION ACTION
                echo form_close(); ?>
                    <div class="hr-line-dashed"></div>
                    <h2><strong>Download Apps</strong></h2>
                    <div class="hr-line-dashed"></div>

                    <!-- START DOWNLOAD -->
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="ibox ">  
                                    <h4>Download Android Total <div class="fa fa-question-circle"  data-toggle="tooltip" data-placement="bottom" title="Total aplikasi yang telah di download dari android <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>    
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo $total_android; ?></h1>
                                    <small>Apss</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="ibox ">
                                    <h4>Download Ios Total <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total aplikasi yang telah di download dari ios <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo $total_ios; ?></h1>
                                    <small>Apss</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="ibox ">
                                    <h4>Download Total <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total aplikasi yang telah di download <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo $total_download; ?></h1>
                                    <small>Apss</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END DOWNLOAD -->

                    <div class="hr-line-solid"></div>
                    <h2><strong>Orders</strong></h2>
                    <div class="hr-line-dashed"></div>
                    
                    <!-- START ORDER -->
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="ibox ">  
                                    <h4>Order Completed <div class="fa fa-question-circle"  data-toggle="tooltip" data-placement="bottom" title="Total Order yang telah selesai periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>    
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($total_order); ?></h1>
                                    <small>Orders</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="ibox ">
                                    <h4>Delivery Completed <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total Order yang telah selesai dengan Metode Delivery periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($total_delivery); ?></h1>
                                    <small>Orders</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="ibox ">
                                    <h4>Pick Up Completed <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total Order yang telah selesai dengan Metode Pickup periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($total_pickup); ?></h1>
                                    <small>Orders</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END ORDER -->

                    <div class="hr-line-solid"></div>
                    <h2><strong>Users</strong></h2>
                    <div class="hr-line-dashed"></div>

                    <!-- START USER -->
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="ibox ">  
                                    <h4>Total User <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total semua user periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>    
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($user->total); ?></h1>
                                    <small>Users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="ibox ">
                                    <h4>Never Order <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total semua user yang tidak pernah order periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($user_not_order->total); ?></h1>
                                    <small>Users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="ibox ">
                                    <h4>Topup in Same Month <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total user yang Top up saldo dan langsung pakai di bulan yang sama periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($user_topup_in_same_month->total); ?></h1>
                                    <small>Users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="ibox ">
                                    <h4>Topup not in Same Month <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total user yang Top up saldo tidak langsung pakai di bulan yang sama periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($user_topup_not_in_same_month->total); ?></h1>
                                    <small>Users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="ibox ">
                                    <h4>Has a Balance Remaining <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total user yang Top up saldo dan masih mempunyai sisa saldo di bulan yang sama periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($user_have_balance->total); ?></h1>
                                    <small>Users</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END USER -->

                    <div class="hr-line-solid"></div>
                    <h2><strong>Referral</strong></h2>
                    <div class="hr-line-dashed"></div>
          
                    <!-- START USER REFERRAL -->
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="ibox ">  
                                    <h4>Total User Referral <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total user yang tereferensi dari teman periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>    
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($user_reff->total); ?></h1>
                                    <small>Users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="ibox ">
                                    <h4>Not Claim <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total user yang tereferensi dari teman tetapi tidak claim periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($reff_not_claim->total); ?></h1>
                                    <small>Users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="ibox ">
                                    <h4>Have Claim <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total user yang tereferensi dari teman dan ada claim periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($reff_claim->total); ?></h1>
                                    <small>Users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="ibox ">
                                    <h4>Claim Not Repeat Order <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total user yang tereferensi dari teman dan ada claim tapi tidak repeat order periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($reff_claim_not_repeat->total); ?></h1>
                                    <small>Users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="ibox ">
                                    <h4>Claim and Repeat Order <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total user yang tereferensi dari teman dan ada claim dan repeat order periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($reff_claim_repeat->total); ?></h1>
                                    <small>Users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="ibox ">
                                    <h4>Immediately buy <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total user yang tereferensi dari teman dan langsung beli periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($reff_not_free->total); ?></h1>
                                    <small>Users</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END USER REFERRAL -->

                    <div class="hr-line-solid"></div>
                    <h2><strong>Claim</strong></h2>
                    <div class="hr-line-dashed"></div>
                    
                    <!-- START CLAIM -->
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="ibox ">  
                                    <h4>Claim Free <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total user yang claim free periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>    
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($claim_free->total); ?></h1>
                                    <small>Users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="ibox ">
                                    <h4>Claim Free and Repeat Order <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total user yang claim free dan repeat order periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($claim_free_repeat->total); ?></h1>
                                    <small>Users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="ibox ">
                                    <h4>Claim Free Not Repeat Order <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total user yang claim free dan tidak repeat order periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($claim_free_not_order->total); ?></h1>
                                    <small>Users</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="ibox ">
                                    <h4>Not Claim Free <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total user yang tidak claim free periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($not_claim_free->total); ?></h1>
                                    <small>Users</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END CLAIM -->

                    <div class="hr-line-solid"></div>
                    <h2><strong>Topup Balance</strong></h2>
                    <div class="hr-line-dashed"></div>

                    <!-- START TOP UP -->
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="ibox ">
                                    <h4>Topup Balance Total <div class="fa fa-question-circle" data-toggle="tooltip" data-placement="bottom" title="Total saldo topup periode <?php echo $from; ?> - <?php echo $to; ?>"></div></h4>  
                                <div class="ibox-content">
                                    <h1 class="no-margins"><?php echo number_format($user_topup->total); ?></h1>
                                    <small>Rupiah</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- END TOP UP -->
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