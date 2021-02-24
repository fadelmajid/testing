<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Order Detail</h2>
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
                                #<?php echo $data['order']->uor_code; ?>
                            </h2>
                            <hr>
                        </div>
                        <div class="col-md-2">
                            <h3>Customer</h3>
                            <dd>
                                <strong><?php echo $data['order']->user_name; ?></strong><br>
                                <?php echo $data['order']->user_phone; ?><br>
                                <?php echo $data['order']->user_email; ?>
                            </dd>
                        </div>
                        <div class="col-md-2">
                            <h3>Date</h3>
                            <dd>
                            <?php
                                $datetime = explode(', ', show_date($data['order']->uor_date, true));
                                echo $datetime[0].'<br>';
                                echo $datetime[1];
                            ?>
                            </dd>
                            <?php echo "<strong>". $data['order']->pymtd_name ."</strong><br />"; ?>
                            <dd>
                            <?php
                                $pymtb_data = json_decode($data['order']->pyhis_data);
                                if(isset($pymtb_data)) {
                                    if($data['order']->pymtd_code == "dana"){
                                        echo "DANA Transaction ID : ".$pymtb_data->uod_code."<br />";
                                    }else{
                                        echo "Refersal No : ". $pymtb_data->ref_no ."<br />";
                                        echo "Batch No    : ". $pymtb_data->batch_no ."<br />";
                                        echo "Number      : ". $pymtb_data->number ."<br />";
                                    }
                                };
                            ?>
                            </dd>
                        </div>
                        <div class="col-md-2">
                        <?php
                        if ($data['order']->uor_delivery_type === $cst_delivery_type['delivery']) {
                            $delivery_map = 'https://maps.google.com/?q='.$data['order']->uadd_lat.','.$data['order']->uadd_long;
                            $delivery_label = 'Deliver to';
                            $delivery_name = '<strong>'.$data['order']->uadd_person.'</strong>';
                            $delivery_phone = $data['order']->uadd_phone;
                            $delivery_address = nl2br($data['order']->uadd_street);
                            $delivery_address .= ($data['order']->uadd_notes == '' ? '' : '<br/>Note : '.$data['order']->uadd_notes);
                        } else {
                            $delivery_map = 'https://maps.google.com/?q='.$data['order']->st_lat.','.$data['order']->st_long;
                            $delivery_label = 'Pickup at';
                            $delivery_name = '<strong>'.$data['order']->st_name.'</strong>';
                            $delivery_phone = $data['order']->st_phone;
                            $delivery_address = nl2br($data['order']->st_address);
                            $delivery_address .= '';
                        }
                        ?>
                            <h3><?php echo $delivery_label; ?></h3>
                            <dd>
                            <?php echo $delivery_name.'<br>'.$delivery_phone.'<br><a href="'.$delivery_map.'" target="_blank">'.$delivery_address.'</a>'; ?>
                            </dd>
                        </div>
                        <div class="col-md-2">
                            <?php
                                if ($data['order']->uor_delivery_type === $cst_delivery_type['delivery'] && $data['order']->st_name != null){
                                    echo '<h3>Deliver from</h3><dd><strong>'. $data['order']->st_name .'</strong></dd>';
                                }
                            ?>
                            <h3>Status</h3>
                            <?php
                                $status = $cst_status_name[$data['order']->uor_status];
                                if ($data['order']->uor_status === $cst_status['paid'] || $data['order']->uor_status === $cst_status['in_process'] || $data['order']->uor_status === $cst_status['ready_for_pickup'] || $data['order']->uor_status === $cst_status['on_delivery']) {
                                    $label_color = 'text-warning';
                                } elseif ($data['order']->uor_status === $cst_status['completed']) {
                                    $label_color = 'text-info';
                                } else {
                                    $label_color = 'text-danger';
                                }
                            ?>
                            <dd><span class="<?php echo $label_color?>"><strong><?php echo $status; ?></strong></span></dd>
                            Updated By <?php echo isset($arr_admin[$data['order']->updated_by]) ? $arr_admin[$data['order']->updated_by] : 'System'; ?><br />
                            <?php echo show_date($data['order']->updated_date, true); ?>
                        </div>
                        <div class="col-md-2">
                            <?php
                                if ($data['order']->uor_status === $cst_status['ready_for_pickup'] || $data['order']->uor_status === $cst_status['on_delivery'] || $data['order']->uor_status === $cst_status['completed'] || $data['order']->uor_status === $cst_status['cancelled']){
                                    if (isset($data['courier']->uorcr_driver_name)){
                                        echo '
                                            <h3>Driver</h3>
                                            <dd>
                                                <strong>'.$data['courier']->uorcr_driver_name.'</strong>
                                        ';
                                    }if(isset($data['courier']->uorcr_driver_phone)){
                                        echo'
                                                <br>'.$data['courier']->uorcr_driver_phone.'<br>
                                        ';
                                    }if(isset($data['courier']->uorcr_status)){
                                        echo'
                                                <h3>Status</h3>
                                                <strong>'.ucfirst($data['courier']->uorcr_status).'</strong>
                                            </dd>
                                        ';
                                    }
                                }
                            ?>
                        </div>
                        <div class="col-md-12"><hr></div>
                        <div class="col-md-2 font-bold">
                            <dd>Voucher Used<br>&nbsp;</dd>
                            <dd>Subtotal</dd>
                            <dd>Delivery Fee</dd>
                            <dd>Actual Delivery Fee</dd>
                            <dd>Discount</dd>
                            <dd>Total</dd>
                        </div>
                        <div class="col-md-2">
                            <dd class="font-bold text-right"><?php echo isset($data['voucher']) ? implode(", ", $data['voucher']) : '-'; ?><br>&nbsp;</dd>
                            <dd class="text-right"><?php echo number_format($data['order']->uor_subtotal, 0, ',', '.'); ?></dd>
                            <dd class="text-right"><?php echo number_format($data['order']->uor_actual_delivery_fee, 0, ',', '.'); ?></dd>
                            <dd class="text-right"><?php echo number_format($data['order']->uor_delivery_fee, 0, ',', '.'); ?></dd>
                            <dd class="text-right"><?php echo number_format($data['order']->uor_discount * -1, 0, ',', '.'); ?></dd>
                            <dd class="text-right"><?php echo number_format($data['order']->uor_total, 0, ',', '.'); ?></dd>
                        </div>
                        <div class="col-md-9"></div>
                    </div>
                </div>
            </div>
            <div class="ibox order-detail">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-12">
                            <h3 class="font-bold m-b-xs">Products</h3>
                        </div>
                        <div class="col-md-12">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th class="text-right">Price</th>
                                    <th class="text-right">Quantity</th>
                                    <th class="text-right">Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $no = 1;
                                    foreach ($data['product'] as $product):
                                        $product_price = $product->uorpd_is_free ? 0 : number_format($product->uorpd_final_price, 0, ',', '.');
                                        $product_total = $product->uorpd_is_free ? 0 : number_format($product->uorpd_total, 0, ',', '.');
                                        $type = $product->uorpd_is_free ? 'Free' : '-';
                                ?>
                                <tr>
                                    <td style="width:10%"><?php echo $no; ?></td>
                                    <td style="width:20%"><?php echo ucwords($product->uorpd_name); ?></td>
                                    <td style="width:10%"><?php echo $type?></td>
                                    <td style="width:20%" class="text-right"><?php echo $product_price; ?></td>
                                    <td style="width:20%" class="text-right"><?php echo $product->uorpd_qty; ?></td>
                                    <td style="width:20%" class="text-right"><?php echo $product_total; ?></td>
                                </tr>
                                <?php
                                        $no++;
                                    endforeach
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="ibox order-detail">
                <div class="ibox-content">
                <div class="row">
                        <div class="col-md-6">
                            <h3 class="font-bold m-b-xs">Order Track</h3>
                        </div>
                        <div class="col-md-6 text-right">
                        <?php /*
                        if ($data['order']->uor_status === 'cancelled' || $data['order']->uor_status === 'completed') {
                            echo '';
                        } else {
                            if (in_array('edit', $permits)) {
                                echo '<a href="#" id="trk_'. $data['order']->uor_code.'_'.$data['order']->uor_id.'" class="btn btn-primary btn-s btn_track" data-toggle="modal" data-target="#trackmodal" >Add Order Track</a>';
                            }
                        } */
                        ?>
                        </div>
                        <div class="col-md-12">
                            <?php
                                if (!$data['track']):
                                    echo '<h4 class="text-center">Order Track not found.</h4>';
                                else:
                            ?>
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Created by</th>
                                    <?php /*<th>&nbsp;</th> */ ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                    foreach ($data['track'] as $order_track):
                                        $datetime = explode(', ', show_date($order_track->uortr_date, true));
                                ?>
                                    <tr id ="tr_<?php echo $order_track->uortr_id; ?>">
                                        <td><?php echo $order_track->uortr_id; ?></td>
                                        <td>
                                        <?php echo $datetime[0]; ?> <br>
                                        <?php echo $datetime[1]; ?>
                                        </td>
                                        <td><?php echo $order_track->uortr_text; ?></td>
                                        <td><?php echo (isset($arr_admin[$order_track->created_by]) ? $arr_admin[$order_track->created_by] : 'system');?><br></td>
                                        <?php /* <td class="text-right"><a href="#" id="del_<?php echo $order_track->uortr_id; ?>" class="btn btn-danger btn-s btn_delete">Delete</a></td> */ ?>
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
<?php /*
<div class="modal inmodal" id="trackmodal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
    <div class="modal-content">
            <div class="modal-body">
                <div class="form-group">
                    <h5>Order Code</h5>
                    <div class="input-group date" style="float:left !important">
                        <input class="form-control" id="uor_code" readonly value=""/>
                        <input type="hidden" id="uor_id" value=""/>
                    </div>
                    <br/><br/>
                </div>
                <div class="form-group">
                    <h5>Track Date</h5>
                    <div class="input-group date" id="trackdate" style="float:left !important">
                        <input class="form-control" id="uortr_date" value=""/>
                    </div>
                </div>
                <br><br>
                <div class="form-group">
                    <h5>Preset Message</h5>
                    <div class="input-group" style="float:left !important">
                        <select name="trackmsg" id="track_msg" class="form-control">
                            <option value="">-</option>
                        <?php foreach ($this->config->item('order_track')['message'] as $msg): ?>
                            <option value="<?php echo $msg; ?>"><?php echo $msg; ?></option>
                        <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <br><br>
                <div class="form-group">
                    <h5>Remarks / Info</h5>
                    <div class="input-group date" id="trackdate" style="float:left !important">
                        <textarea type="text" id="uortr_text" class="form-control"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="save_track">Save changes</button>
            </div>
        </div>
    </div>
</div>
*/ ?>

<script>
$(document).ready(function(){
    // delete order track
    $(".btn_delete").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/order_track_delete';
        var target = $(this).attr("id").split("_");
        var uortr_id = target[1];
        var delete_row = $("#tr_" + uortr_id);
        var confirm_action = confirm('Are you sure you want to delete the Track ID ' + uortr_id + '?');

        if(confirm_action){
            $("#loading").fadeIn();
            var ajax = $.post(url, {uortr_id: uortr_id},
                function(data) {
                    $("#loading").fadeOut();
                    if (data == "Success") {
                        delete_row.fadeTo(400, 0, function () {
                            delete_row.remove();
                        });
                    } else {
                        alert(data);
                    }
                }
            );
        }

        return false;
    });

    // save order track info
    $("#save_track").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/order_track_add';
        var uor_code = $("#uor_code").val();
        var uor_id = $("#uor_id").val();
        var uortr_date = $("#uortr_date").val();
        var uortr_text = $("#uortr_text").val();

        $("#loading").fadeIn(); //show when submitting
        var ajax = $.post(url, {uor_id: uor_id, uortr_date: uortr_date, uortr_text: uortr_text},
            function(data) {
                $("#loading").fadeOut(); //hide when data's ready
                if (data == "Success") {
                    $('#trackmodal').modal('toggle');
                    location.reload();
                } else {
                    alert(data);
                }
            }
        );

        return false;
    });

    //timepicker for order track modal
    $('#uortr_date').datetimepicker({
        format:'Y-m-d H:i',
        lang:'en'
    });

    // open order track modal
    $('.btn_track').click(function () {
        var target = $(this).attr('id').split("_");
        var uor_code = target[1];
        var uor_id = target[2];
        var date = new Date();
        var str = date.getFullYear() + "-" + ('0' + (date.getMonth() + 1)).slice(-2) + "-" + ('0' + date.getDate()).slice(-2) + " " + ('0' + date.getHours()).slice(-2) + ":" + ('0' + date.getMinutes()).slice(-2);

        $('#uor_code').val(uor_code);
        $('#uor_id').val(uor_id);
        $('#uortr_date').val(str);
    })

    // add order track preset message
    $('#track_msg').change(function () {
        var msg = $(this).val();
        $('#uortr_text').val(msg);
    });
});
</script>