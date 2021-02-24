<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>User Detail</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-12">
                            <h2 class="font-bold m-b-xs"><?php echo $data['user']->user_name; ?></h2>
                            <hr>
                        </div>
                        <div class="col-2">
                            <h4>Phone Number</h4>
                            <h4>E-mail</h4>
                            <h4>Wallet Balance</h4>
                        </div>
                        <div class="col-3">
                            <?php
                            if ($data['user']->user_status === $cst_user_status['active']) {
                                $color = 'text-success';
                            } else {
                                $color = 'text-danger';
                            }
                            ?>
                            <h4><?php echo $data['user']->user_phone; ?></h4>
                            <h4><?php echo $data['user']->user_email; ?></h4>
                            <h4><?php echo idr_format($data['wallet']->uwal_balance, ''); ?></h4>
                        </div>
                        <div class="col-2">
                            <h4>Referral Code</h4>
                            <h4>Status</h4>
                        </div>
                        <div class="col-3">
                            <h4><?php echo $data['user']->user_code; ?></h4>
                            <h4 class="<?php echo $color; ?>"><strong><?php echo ucfirst($data['user']->user_status); ?></strong></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-12">
                            <h3 class="font-bold m-b-xs">Address List</h3>
                            <hr>
                        </div>
                        <div class="col-12" style="height:350px;overflow-y:scroll">
                        <?php foreach ($data['address'] as $address): ?>
                            <div class="panel panel-default">
                                <div class="panel-heading font-bold"><?php echo $address->uadd_title; ?></div>
                                <div class="panel-body">
                                    <p>
                                        <?php echo $address->uadd_person; ?><br>
                                        <?php echo $address->uadd_phone; ?>
                                    </p>
                                    <p><?php echo nl2br($address->uadd_street); ?></p>
                                </div>
                            </div>
                        <?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-12">
                            <h3 class="font-bold m-b-xs">Wallet History</h3>
                            <hr>
                        </div>
                        <div class="col-12" style="max-height:350px;overflow-y:scroll">
                            <?php
                                if (!$data['wallet_history']):
                                    echo '<h4 class="text-center">Wallet History not found.</h4>';
                                else:
                            ?>
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th width="10%">Nominal</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                    foreach ($data['wallet_history'] as $wallet_history):
                                        $datetime = explode(', ', show_date($wallet_history->created_date, true));
                                        $nominal = idr_format($wallet_history->uwhis_nominal, '');
                                        $history_type = ucfirst($wallet_history->uwhis_type);

                                        // validate history type
                                        if ($wallet_history->uwhis_type === $cst_wallet_history_type['order'] || $wallet_history->uwhis_type === $cst_wallet_history_type['refund']) {
                                            $order_url = ADMIN_URL.'transaction/order_detail/'.$wallet_history->uor_id;
                                            $history_type = $history_type." (<a href=\"{$order_url}\">#{$wallet_history->uor_code})</a>";
                                        }
                                ?>
                                    <tr>
                                        <td><?php echo $wallet_history->uwhis_id; ?></td>
                                        <td>
                                            <?php echo $datetime[0]; ?><br>
                                            <?php echo $datetime[1]; ?>
                                        </td>
                                        <td><?php echo $history_type; ?></td>
                                        <td class="text-right"><?php echo $nominal; ?></td>
                                    </tr>
                                <?php
                                    endforeach;
                                ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.content-wrapper -->