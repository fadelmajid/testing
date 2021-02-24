<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Ongoing</h2>
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
                        <?php
                            if(in_array("bulk_process", $permits)){
                        ?>
                        <div class="form-group col-2">
                            <button type="button" data-toggle="modal" data-target="#trackmodal" class="btn btn-primary">
                                Bulk Process
                            </button>
                        </div>
                        <?php } ?>
                    <?php echo form_close(); ?>
                    <div class="hr-line-dashed"></div>
                    <div id="order_alert" class="col-12 alert alert-danger b-r-xl" style="display: none;"></div>
                    <table class="table table-sticky table-bordered">
                        <thead>
                            <tr>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'code', 'Order No.', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'name', 'Customer', $xtra_var); ?>
                                </th>
                                <th>
                                    Items
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'date', 'Date', $xtra_var); ?>
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
                                <?php echo (in_array('edit', $permits) || in_array('cancel', $permits)  ? '<th>Action</th>' : '' );?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($all_data as $key => $value) {
                            $detail_url = $detail_path.'_detail/'.$value->uor_id;

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

                                $courier_code = json_decode($value->st_courier);
                                if(($value->uor_delivery_type === $cst_delivery_type['delivery'] && $value->uor_status === $cst_status["paid"])){
                                    //set button edit in_process, ready_for_pickup and completed
                                    $btn_edit .= '<a href="#" data-url="'. ADMIN_URL .'ajax/ongoing_'. $value->uor_delivery_type .'_'. $next_status .'" data-delivery="'. $value->uor_delivery_type .'" data-courier="'. $courier_code->courier_code .'" data-st="'. $value->st_id .'" data-code="'. $value->uor_code .'" data-status="'. $btn_status .'" id="pick_'. $value->uor_id .'" class="btn btn-primary btn-block btn-xs change-status">Set to '. $btn_status .'</a>';
                                }

                                // if($value->uor_delivery_type === $cst_delivery_type['delivery'] && !in_array($value->uor_status, [$cst_status["paid"], $cst_status["waiting_for_payment"]]) && $courier_code->courier_code === $cst_courier_code['sicepat']){
                                //     $btn_edit .= '<a href="#" class="btn btn-block btn-xs btn-default"  onclick="print_struck('. $value->uor_id .')" target="_blank">Reprint Struck</a>';
                                // }

                                //if delivery_type is delivery and it will check courier data exist
                                /*
                                if(isset($courier[$value->uor_id]) && $courier[$value->uor_id] !== NULL){
                                    if(($courier[$value->uor_id]->uorcr_status === $gosend_status['cancelled'] || $courier[$value->uor_id]->uorcr_status === $gosend_status['no_driver']) && $value->uor_status === $cst_status['in_process'] ){
                                        $btn_edit = '<a href="#" id="reb_'. $value->uor_id .'" data-code="'. $value->uor_code .'" class="btn btn-warning btn-block btn-xs btn_rebooking">Rebooking</a>';
                                    }
                                }
                                */
                                $action_str .= $btn_edit;
                            }

                            //check permit cancel
                            if(in_array('confirm', $permits)){

                                if($value->uor_delivery_type === $cst_delivery_type['delivery'] && ($value->uor_status === $cst_status["in_process"] || $value->uor_status === $cst_status["on_delivery"])) {
                                    $btn_edit .= '<a href="#" class="btn btn-block btn-xs btn-primary btn_completed" data-url="'. ADMIN_URL .'ajax/ongoing_delivery_completed" data-delivery="'. $value->uor_delivery_type .'" data-st="'. $value->st_id .'" data-code="'. $value->uor_code .'" data-status="completed" id="pick_'. $value->uor_id .'" >Set to Completed</a>';
                                }

                                //check status cancelled and completed
                                if ($value->uor_status === $cst_status['cancelled'] || $value->uor_status === $cst_status['completed']) {
                                    $btn_cancel .= '';
                                } else {
                                    $btn_cancel .= '<button type="button" data-toggle="modal" data-target="#canceltrackmodal" data-code="'. $value->uor_code .'"  data-status="Cancelled" id="cancel_'. $value->uor_id .'" class="btn btn-danger btn-block btn-xs change-status-cancel">Cancel Order</a>';
                                }
                                $action_str .= $btn_cancel;
                            }
                            if(in_array('edit', $permits) || in_array('confirm', $permits)){
                                $action_str = '<td>'.$btn_edit.$btn_cancel.'</td>';
                            }

                            // END ACTION URL
                        ?>
                            <tr>
                                <td>
                                    <?php echo '<a href="'.$detail_url.'">'.$value->uor_code.'</a>'; ?><br>
                                    <?php echo $value->st_name?>
                                    <?php echo "<br/> #".$value->uor_id; ?>
                                </td>
                                <td>
                                    <?php echo $value->user_name; ?><br>
                                    <?php echo $value->user_phone; ?><br>
                                    <?php echo $value->user_email; ?>
                                </td>
                                <td>
                                    <?php
                                        $grouping_items = array();
                                        foreach($value->product_list as $product_key => $product){
                                            $prev_qty =  (isset($grouping_items[ $product->pd_id ]) ? $grouping_items[ $product->pd_id ]['qty'] : 0);
                                            $grouping_items[ $product->pd_id ] = array('name' => $product->uorpd_name, 'qty' => $prev_qty + $product->uorpd_qty);
                                        }

                                        foreach($grouping_items as $grpvalue){
                                            echo $grpvalue['qty'].' '.$grpvalue['name'].'<br/>';
                                        }
                                    ?>
                                </td>
                                <td>
                                <?php
                                    $date = explode(', ', show_date($value->uor_date, true));
                                    echo $date[0].'<br>'.$date[1].'<br>';
            
                                    if(isset($voucher[$value->uor_id])) {
                                        echo implode(", ", $voucher[$value->uor_id]). "<br />";
                                    } else {
                                        echo '';
                                    }

                                    echo 'Payment : <strong> '.$value->pymtd_name.'</strong>';
                                ?>
                                </td>
                                <td>
                                    <?php
                                        if($value->uor_delivery_type == $cst_delivery_type['delivery']) {
                                            echo ucfirst($value->uor_delivery_type)."<br />". $value->uadd_person ."<br />". $value->uadd_phone;

                                        } else {
                                            echo ucfirst($value->uor_delivery_type);
                                        }
                                    ?>
                                </td>
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
                                <?php echo $action_str;?>

                            </tr>
                            <?php } ?>
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
                    <h5>Bulk</h5>
                    <div class="input-group" style="float:left !important">
                        <select name="list" id="list" class="form-control" required>
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="20">20</option>
                        </select>
                    </div>
                    <br/><br/>
                </div>
                <div class="form-group">
                    <h5>Store</h5>
                    <div class="input-group" style="float:left !important">

                        <?php
                        if($store_permits == 0){
                            echo '<select name="store" id="store" class="form-control" required>
                                        <option value="0" selected>All Store</option>';
                            foreach($store_data as $store) {
                                    echo "<option value='". $store->st_id ."'>". $store->st_name ."</option>";
                            }
                            echo '</select>';
                        } else  {
                            foreach($store_data as $store) {
                                if($store->st_id == $store_permits){
                                    echo '<input class="form-control" value="'. $store->st_name .'">';
                                    echo '<input type="hidden" class="form-control" name="store" id="store" value="'. $store->st_id .'">';
                                }
                            }
                        } ?>
                    </div>
                    <br/><br/>
                </div>
                <div class="form-group">
                    <h5>Order Date</h5>
                    <div class="input-group date" id="trackdate" style="float:left !important">
                        <input type="text"  class="form-control" name="uor_date" id="uor_date" value="<?php echo date('Y-m-d'); ?>"/>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal" onclick="location.reload();">Close</button>
                <button type="button" class="btn btn-primary" id="process">Process</button>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="canceltrackmodal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
    <div class="modal-content">
            <div class="modal-body">
                <div class="form-group">
                    <h5>Order ID</h5>
                    <div class="input-group date" id="trackdate" >
                        <input type="input" class="form-control" id="order_id_disabled" disabled/>
                        <input type="hidden" class="form-control" name="order_id" id="order_id"/>
                    </div>
                </div>
                <div class="form-group">
                    <h5>Order Code</h5>
                    <div class="input-group date" id="trackdate" >
                        <input type="input" class="form-control" id="order_code_disabled" disabled/>
                        <input type="hidden" class="form-control" name="order_code" id="order_code"/>
                    </div>
                </div>
                <div class="form-group">
                    <h5>Cancel Reason</h5>
                    <div class="input-group date" id="trackdate" >
                        <textarea class="form-control" name="uor_date" id="reason_cancel"></textarea>
                    </div>
                    <div class="text-left text-danger" id="text-danger" style="display:none">The Cancel Reason field is required.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal" onclick="location.reload();">Close</button>
                <button type="button" class="btn btn-primary" id="process_cancel">Process</button>
            </div>
        </div>
    </div>
</div>

<div class="modal inmodal" id="brewingnow" data-backdrop="static" data-keyboard="false" tabindex="-3" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <div class="form-group">
                    <h5>Order Code</h5>
                    <div class="input-group date" id="trackdate" >
                        <input type="hidden" id="brewing_id" value="<?php echo (isset($data_brewing->uor_id) ? $data_brewing->uor_id : '');?>" />
                        <input type="input" class="form-control" value="<?php echo (isset($data_brewing->uor_code) ? $data_brewing->uor_code : '');?>" disabled/>
                    </div>
                </div>
                <div class="form-group">
                    <h5>Customer</h5>
                    <div class="input-group date" id="trackdate" >
                        <?php
                            echo (isset($data_brewing->user_name) ? $data_brewing->user_name : '').'<br/>';
                            echo (isset($data_brewing->user_phone) ? $data_brewing->user_phone : '').'<br/>';
                            echo (isset($data_brewing->user_email) ? $data_brewing->user_email : '').'<br/>';

                        ?>
                    </div>
                </div>
                <div class="form-group">
                    <h5>Items</h5>
                    <div class="input-group date" id="trackdate" >
                    <?php
                        $grouping_items = array();
                        if(! empty($data_brewing)){
                            foreach($data_brewing->product_list as $product_key => $product){
                                $prev_qty =  (isset($grouping_items[ $product->pd_id ]) ? $grouping_items[ $product->pd_id ]['qty'] : 0);
                                $grouping_items[ $product->pd_id ] = array('name' => $product->uorpd_name, 'qty' => $prev_qty + $product->uorpd_qty);
                            }
                        }

                        foreach($grouping_items as $value){
                            echo $value['qty'].' '.$value['name'].'<br/>';
                        }
                    ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-large btn-white" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-large btn-primary" id="process_brewingnow">Process Now!</button>
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

    $('#uor_date').datetimepicker({
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
        var delivery        = $(this).data('delivery');
        var st_id           = $(this).data('st');
        var courier         = $(this).data('courier');
        var uor_id          = target[1];
        var confirm_action  = confirm('Are you sure you want to update Order No.' + uor_code + ' to "' + status + '"?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {uor_id: uor_id, status: status},
                function(res) {
                    $("#loading").fadeOut();
                    if (res == "Success") {
                        // if ( delivery == "delivery"  && courier == "sicepat") {
                        //     print_struck(uor_id)
                        // }
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
        var status          = $(this).data('status');
        var uor_code        = $(this).data('code');
        var uor_id          = target[1];

        $('#order_id').val(uor_id)
        $('#order_id_disabled').val(uor_id)
        $('#order_code').val(uor_code)
        $('#order_code_disabled').val(uor_code)
    });


    $("#process_cancel").click(function () {
        var uor_id          = $("#order_id").val();
        var uor_code        = $("#order_code").val();
        var uor_remarks     = $("textarea#reason_cancel").val();

        var url             = '<?php echo ADMIN_URL; ?>ajax/cancel_ongoing_status';
        var confirm_action  = confirm('Are you sure you want to update Order No.' + uor_code + ' to cancelled ?');

        if (confirm_action) {

            if(uor_remarks != ""){

                $("#loading").fadeIn();
                var ajax = $.post(url, {uor_id: uor_id, uor_remarks: uor_remarks},
                    function(res) {
                        $("#loading").fadeOut();
                        if (res == "Success") {
                            location.reload();
                        } else {
                            alert(res);
                        }
                    }
                );
            } else {
                $("#order_code").val(uor_remarks)
                $("#text-danger").attr('style', 'display:block')
            }
        }

        return false
    });

        // rebooking
    $(".btn_rebooking").click(function () {
        var url             = '<?php echo ADMIN_URL; ?>ajax/ongoing_rebooking';
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
        var url = '<?php echo ADMIN_URL; ?>ajax/ongoing_track_add';
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

    // save order track info
    $(".btn_completed").click(function () {
        var target          = $(this).attr('id').split("_");
        var url             = $(this).data('url');
        var status          = $(this).data('status');
        var uor_code        = $(this).data('code');
        var delivery        = $(this).data('delivery');
        var st_id           = $(this).data('st');
        var courier         = $(this).data('courier');
        var uor_id          = target[1];
        var confirm_action  = confirm('Are you sure you want to update Order No.' + uor_code + ' to "' + status + '"?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {uor_id: uor_id, status: status},
                function(res) {
                    $("#loading").fadeOut();
                    if (res == "Success") {
                        // if (delivery == "delivery" && courier == "sicepat") {
                        //     print_struck(uor_id)
                        // }
                        location.reload();
                    } else {
                        alert(res);
                    }
                }
            );
        }
    });

    // Set order to completed
    $("#process_brewingnow").click(function () {
        var url     = '<?php echo ADMIN_URL; ?>ajax/ongoing_pickup_completed';
        var uor_id  = $('#brewing_id').val();

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
                    + '<a href="<?php echo BASE_URL; ?>transaction/ongoing" id="refresh">Click here to refresh the list.</a>')
                    .fadeIn();
                } else {
                    $('#order_alert').css('display', 'none');
                }
            }
        );
    },30000);


    $("#process").click(function () {
        var bulk_process    = $("#list").val();
        var st_id           = $("#store").val();
        var date            = $("#uor_date").val();
        var url             = '<?php echo ADMIN_URL; ?>ajax/bulk_process';
        var confirm_action  = confirm('Are you sure you want to process '+ bulk_process +' orders delivery ?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {bulk_process: bulk_process, st_id: st_id, date: date},
                function(res) {
                    $("#loading").fadeOut();
                    result = JSON.parse(res);
                    msg = result['msg'];
                    list = result['list'];
                    if (msg == "success") {
                        $('#trackmodal').attr('style','display:none');
                        // if(list !== "" && list !== "W10="){
                        //     window.open('<?php echo BASE_URL ?>transaction/bulk_print_struck?id='+list, '_blank', 'location=yes,height=570,width=512mm,scrollbars=yes,status=yes')
                        // }
                        location.reload(); 
                        alert(msg)
                    }
                }
            );
        }

        return false
    });


    <?php
        if($show_brewing){
            //if show brewing == true berarti datanya cuma 1 & pickup & bukan complete / cancel
            echo "$('#brewingnow').modal('toggle'); $('#process_brewingnow').focus();";
        }
    ?>
});

    function print_struck(uor_id) {
        // window.open(window.onload = '<?php echo BASE_URL ?>transaction/print_struck/'+uor_id, '_blank', 'location=yes,height=570,width=512mm,scrollbars=yes,status=yes')
    }
</script>