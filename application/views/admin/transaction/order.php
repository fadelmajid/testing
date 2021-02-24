<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Orders</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
            <div class="ibox">
                <div class="ibox-content">
                    <?php echo form_open($form_url, array('method'=>'get', 'class'=>'row form-inline')); ?>
                        <div class="form-group col-3">
                            <input type="text" name="search" id="search" placeholder="Search" value="<?php echo $search;?>" class="form-control col-12">
                        </div>
                        <div class="form-group col-5">
                            <div class="input-group date" id="search_date">
                                <input type="text" class="form-control" name="start" id="start_date" placeholder="Start date" value="<?php echo $start_date; ?>"/>
                                <span class="input-group-addon">to</span>
                                <input type="text" class="form-control" name="end" id="end_date" placeholder="End date" value="<?php echo $end_date; ?>" />
                            </div>
                        </div>
                        <div class="form-group col-2">
                            <button type="submit" class="btn btn-default">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    <?php echo form_close(); ?>
                    <div class="hr-line-dashed"></div>
                    <div id="order_alert" class="col-12 alert alert-danger b-r-xl" style="display: none;"></div>
                    <table class="table table-sticky table-bordered">
                        <thead>
                            <tr>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'id', 'ID', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'code', 'Order No.', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'name', 'Customer', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'date', 'Date', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'voucher', 'Voucher', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'total', 'Total', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'type', 'Type', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'status', 'Status', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'updated_date', 'Updated', $xtra_var); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($all_data as $key => $value) :
                            $detail_url = $current_url.'_detail/'.$value->uor_id;

                            // BEGIN ACTION URL
                            $action_str = "";
                            $btn_edit   = "";
                            $btn_status = "";
                            $btn_cancel = "";
                            $status     = "";

                            $next_status = $cst_next_status[$value->uor_status];
                            if(isset($cst_status_name[$next_status])) {
                                $btn_status = $cst_status_name[$next_status];
                            }

                            if(in_array('edit', $permits) && $btn_status !== ''){

                                if(($value->uor_delivery_type === $cst_delivery_type['delivery'] && $value->uor_status === $cst_status["paid"]) || $value->uor_delivery_type === $cst_delivery_type['pickup'] ){
                                    //set button edit in_process, ready_for_pickup and completed
                                    $btn_edit .= '<a href="#" data-url="'. ADMIN_URL .'ajax/order_'. $value->uor_delivery_type .'_'. $next_status .'" data-code="'. $value->uor_code .'" data-status="'. $btn_status .'" id="pick_'. $value->uor_id .'" class="btn btn-primary btn-block btn-xs change-status">Set to '. $btn_status .'</a>';
                                }


                                //if delivery_type is delivery and it will check courier data exist
                                if(isset($courier[$value->uor_id]) && $courier[$value->uor_id] !== NULL){
                                    if(($courier[$value->uor_id]->uorcr_status === $gosend_status['cancelled'] || $courier[$value->uor_id]->uorcr_status === $gosend_status['no_driver']) && $value->uor_status === $cst_status['in_process'] ){
                                        $btn_edit = '<a href="#" id="reb_'. $value->uor_id .'" data-code="'. $value->uor_code .'" class="btn btn-warning btn-block btn-xs btn_rebooking">Rebooking</a>';
                                    }
                                }
                                $action_str .= $btn_edit;
                            }

                            //check permit cancel
                            if(in_array('cancel', $permits)){
                                //check status cancelled and completed
                                if ($value->uor_status === 'cancelled' || $value->uor_status === 'completed') {
                                    $btn_cancel .= '';
                                } else {
                                    $btn_cancel .= '<a href="#" data-code="'. $value->uor_code .'"  data-status="Cancelled" id="cancel_'. $value->uor_id .'" class="btn btn-danger btn-block btn-xs change-status-cancel">Cancel Order</a>';
                                }
                                $action_str .= $btn_cancel;
                            }

                            if(in_array('edit', $permits) || in_array('cancel', $permits)){
                                $action_str = '<td>'.$btn_edit.$btn_cancel.'</td>';
                            }
                            // END ACTION URL
                        ?>
                            <tr>
                                <td><?php echo $value->uor_id; ?></td>
                                <td>
                                    <?php echo '<a href="'.$detail_url.'">'.$value->uor_code.'</a>'; ?><br>
                                    <?php echo $value->st_name?>
                                </td>
                                <td>
                                    <?php echo $value->user_name; ?><br>
                                    <?php echo $value->user_phone; ?><br>
                                    <?php echo $value->user_email; ?>
                                </td>
                                <td>
                                <?php
                                    $date = explode(', ', show_date($value->uor_date, true));
                                    echo $date[0].'<br>'.$date[1];
                                ?>
                                </td>
                                <td>
                                    <?php if(isset($voucher[$value->uor_id])) {
                                        echo implode(", ", $voucher[$value->uor_id]). "<br />";
                                    } else {
                                        echo '';
                                    } ?>
                                    Payment : <strong><?php echo $value->pymtd_name; ?></strong>
                                    </td>
                                <td class="text-right"><?php echo number_format($value->uor_total, 0, ',', '.'); ?></td>
                                <td><?php echo ucfirst($value->uor_delivery_type); ?></td>
                                <td>
                                <?php
                                    $status = $cst_status_name[$value->uor_status];
                                    if ($value->uor_status === $cst_status['paid'] || $value->uor_status === $cst_status['in_process'] || $value->uor_status === $cst_status['ready_for_pickup'] || $value->uor_status === $cst_status['on_delivery']) {
                                        $color = 'text-warning';
                                    } elseif ($value->uor_status === $cst_status['completed']) {
                                        $color = 'text-info';
                                    } else {
                                        $color = 'text-danger';
                                    }

                                    echo '<span class="'.$color.'"><strong>'.$status.'</strong></span><br/>';
                                    if(isset( $order_trk[$value->uor_id] )){
                                        echo $order_trk[ $value->uor_id ]->uortr_text.'<br>';
                                    }else{
                                        echo '';
                                    }

                                    if(isset( $courier[$value->uor_id] )){
                                        echo '<span class="'.$color.'"><strong>BOOKING ID : '.$courier[ $value->uor_id ]->booking_id.'</strong></span><br>';
                                        if(empty($courier[$value->uor_id]->uorcr_url)){
                                            echo $courier[ $value->uor_id ]->uorcr_driver_name.'-'.$courier[$value->uor_id]->uorcr_driver_phone.'<br>';
                                        }else{
                                            echo '<a href="'.$courier[$value->uor_id]->uorcr_url.'" target="_blank">'.$courier[ $value->uor_id ]->uorcr_driver_name.'-'.$courier[$value->uor_id]->uorcr_driver_phone.'</a><br>';
                                        }
                                    }else{
                                        echo '';
                                    }
                                ?>
                                </td>
                                <td>
                                <?php
                                    //update by igo 23 dec 2018, jadikan 1 kolom karena kepanjangan untuk laptop admnin
                                    echo isset($arr_admin[$value->updated_by]) ? $arr_admin[$value->updated_by] : 'System';
                                    echo '<br/>'. show_date($value->updated_date, true);
                                ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                        <?php if (empty($all_data)) : ?>
                            <tr>
                                <td class="error" colspan="100%">Data not found!</td>
                            </tr>
                        <?php endif ?>
                        </tbody>
                    </table>
                    <?php echo $pagination;?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.content-wrapper -->
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
<script>
$(document).ready(function(){
    // set cursor on search column
    $('#search').focus()

    // timepicker for search
    $('#start_date, #end_date').datetimepicker({
        timepicker:false,
        format:'Y-m-d',
        lang:'en'
    });

    // update order status
    $(".change-status").click(function () {
        var target          = $(this).attr('id').split("_");
        var url             = $(this).data('url');
        var status          = $(this).data('status');
        var uor_code        = $(this).data('code');
        var uor_id          = target[1];
        var confirm_action  = confirm('Are you sure you want to update Order No.' + uor_code + ' to "' + status + '"?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {uor_id: uor_id, status: status},
                function(res) {
                    $("#loading").fadeOut();
                    if (res == "Success") {
                        location.reload();
                    } else {
                        alert(res);
                    }
                }
            );
        }

        return false
    });

    $(".change-status-cancel").click(function () {
        var target          = $(this).attr('id').split("_");
        var url             = '<?php echo ADMIN_URL; ?>ajax/cancel_order_status';
        var status          = $(this).data('status');
        var uor_code        = $(this).data('code');
        var uor_id          = target[1];
        var confirm_action  = confirm('Are you sure you want to update Order No.' + uor_code + ' to "' + status + '"?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {uor_id: uor_id},
                function(res) {
                    $("#loading").fadeOut();
                    if (res == "Success") {
                        location.reload();
                    } else {
                        alert(res);
                    }
                }
            );
        }

        return false
    });

    // rebooking
    $(".btn_rebooking").click(function () {
        var url             = '<?php echo ADMIN_URL; ?>ajax/order_rebooking';
        var target          = $(this).attr('id').split("_");
        var uor_code        = $(this).data('code');
        var uor_id          = target[1];
        var confirm_action = confirm('Are you sure you want to rebooking courier for Order No.' + uor_code + '?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {uor_id: uor_id},
                function(data) {
                    $("#loading").fadeOut();
                    if (data == "Success") {
                        location.reload();
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

        $("#loading").fadeIn();
        var ajax = $.post(url, {uor_id: uor_id, uortr_date: uortr_date, uortr_text: uortr_text},
            function(data) {
                $("#loading").fadeOut();
                if (data == "Success") {
                    $('#trackmodal').modal('toggle');
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

    // add order track preset message
    $('#track_msg').change(function () {
        var msg = $(this).val();
        $('#uortr_text').val(msg);
    });

    // alert unprocessed orders
    setInterval(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/order_count_unprocessed_delivery';
        var ajax = $.post(url,
            function(data) {
                $('#order_alert').fadeOut();
                if (data > 0) {
                    $('#order_alert')
                    .html('<audio class="sound-player" autoplay="autoplay" style="display:none;">'
                    + '<source src="<?php echo ASSETS_URL;?>sound/chimes-glassy.mp3"/>'
                    + '<embed src="<?php echo ASSETS_URL;?>sound/chimes-glassy.mp3" hidden="true" autostart="true" loop="false"/></audio>'
                    + 'You have <strong>' + data + '</strong> unprocessed order(s).'
                    + '<a href="<?php echo $current_url; ?>" id="refresh">Click here to refresh the list.</a>')
                    .fadeIn();
                } else {
                    $('#order_alert').css('display', 'none');
                }
            }
        );
    },30000);
});
</script>