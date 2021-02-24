<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Subscription Order</h2>
    </div>
    <div class="col-4">
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
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
                    <table class="table table-sticky table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo sort_table_icon($page_url, 'id', 'Subs Order No.', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'user', 'Customer', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'subsorder_date', 'Date', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'subsorder_subtotal', 'Subtotal', $xtra_var);?></th>
                                <th><?php echo sort_table_icon($page_url, 'subsorder_discount', 'Discount', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'subsorder_total', 'Total', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'subsorder_status', 'Status', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'subsorder_remarks', 'Remarks', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'created', 'Created', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'updated', 'Updated', $xtra_var); ?></th>
                                <?php echo (in_array('cancel', $permits) ? '<th>Action</th>' : '' );?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($data)){ ?>
                                <tr>
                                    <td class="error" colspan="100%">Data not found!</td>
                                </tr>
                        <?php }else {
                                foreach ($data as $key => $subs_order){
                                    $detail_url = $current_url.'_detail/'.$subs_order->subsorder_id;
                                    $action_str = "";
                                    $btn_cancel = "";

                                    if(in_array('cancel', $permits)){
        
                                        //check status cancelled and completed
                                        if ($subs_order->subsorder_status === $subsorder_status['waiting_for_payment']) { 
                                            $btn_cancel .= '<button type="button" data-toggle="modal" data-target="#canceltrackmodal" data-code="'. $subs_order->subsorder_code .'"  data-status="Cancelled" id="cancel_'. $subs_order->subsorder_id .'" class="btn btn-danger btn-block btn-xs change-status-cancel">Cancel Order</a>';
                                        }
                                        $action_str .= '<td>'.$btn_cancel.'</td>';
                                    }
                            ?>
                                <tr>
                                    <td>
                                        <?php
                                            echo '<a href="'.$detail_url.'">'.$subs_order->subsorder_code.'</a><br>
                                                '.$subs_order->subsplan_name.'<br>
                                                #'.$subs_order->subsorder_id
                                            ; 
                                        ?><br><br>
                                    </td>
                                    <td>
                                        <?php
                                            echo
                                                $subs_order->user_name.'<br>'.
                                                $subs_order->user_phone.'<br>'.
                                                $subs_order->user_email
                                            ;
                                        ?><br><br>
                                    </td>
                                    <td>
                                        <?php
                                            $date = explode(', ', show_date($subs_order->subsorder_date, true));
                                            echo
                                                $date[0].'<br>'.
                                                $date[1].'<br>
                                                Payment : <strong>'.$subs_order->pymtd_name.'</strong>'
                                            ;
                                        ?><br><br>
                                    </td>
                                    <td align="right">
                                        <?php echo number_format($subs_order->subsorder_subtotal); ?><br><br>
                                    </td>
                                    <td align="right">
                                        <?php echo number_format($subs_order->subsorder_discount); ?><br><br>
                                    </td>
                                    <td align="right">
                                        <?php echo number_format($subs_order->subsorder_total); ?><br><br>
                                    </td>
                                    <td>
                                        <?php
                                            $status = $subsorder_status_name[$subs_order->subsorder_status];
                                            if ($subs_order->subsorder_status === $subsorder_status['waiting_for_payment']) {
                                                $color = 'text-warning';
                                            } elseif ($subs_order->subsorder_status === $subsorder_status['paid']) {
                                                $color = 'text-info';
                                            } else {
                                                $color = 'text-danger';
                                            }

                                            echo '<span class="'.$color.'"><strong>'.$status.'</strong></span><br/>';
                                        ?><br><br>
                                    </td>
                                    <td>
                                        <?php echo $subs_order->subsorder_remarks; ?><br><br>
                                    </td>
                                    <td>
                                        <?php echo show_date($subs_order->created_date); ?><br><br>
                                    </td>
                                    <td>
                                        <?php
                                            echo isset($arr_admin[$subs_order->updated_by]) ? $arr_admin[$subs_order->updated_by] : 'System';
                                            echo "<br/>".show_date($subs_order->updated_date);
                                        ?><br><br>
                                    </td>
                                    <?php echo $action_str;?>
                                </tr>
                        <?php
                                    }
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

<div class="modal inmodal" id="canceltrackmodal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
    <div class="modal-content">
            <div class="modal-body">
                <div class="form-group">
                    <h5>Subs Order ID</h5>
                    <div class="input-group date" id="trackdate" >
                        <input type="input" class="form-control" id="subsorder_id_disabled" disabled/>
                        <input type="hidden" class="form-control" name="subsorder_id" id="subsorder_id"/>
                    </div>
                </div>
                <div class="form-group">
                    <h5>Subs Order Code</h5>
                    <div class="input-group date" id="trackdate" >
                        <input type="input" class="form-control" id="subsorder_code_disabled" disabled/>
                        <input type="hidden" class="form-control" name="subsorder_code" id="subsorder_code"/>
                    </div>
                </div>
                <div class="form-group">
                    <h5>Cancel Reason</h5>
                    <div class="input-group date" id="trackdate" >
                        <textarea class="form-control" name="subsorder_date" id="reason_cancel"></textarea>
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

<script>
$(document).ready(function(){
    //filter date
    $('#start_date, #end_date').datetimepicker({
        timepicker: false,
        format:'Y-m-d',
        lang:'en'
    });

    $(".change-status-cancel").click(function () {
        var target          = $(this).attr('id').split("_");
        var status          = $(this).data('status');
        var subsorder_code  = $(this).data('code');
        var subsorder_id    = target[1];

        $('#subsorder_id').val(subsorder_id)
        $('#subsorder_id_disabled').val(subsorder_id)
        $('#subsorder_code').val(subsorder_code)
        $('#subsorder_code_disabled').val(subsorder_code)
    });

    $("#process_cancel").click(function () {
        var subsorder_id          = $("#subsorder_id").val();
        var subsorder_code        = $("#subsorder_code").val();
        var subsorder_remarks     = $("textarea#reason_cancel").val();
        var url             = '<?php echo ADMIN_URL; ?>ajax/cancel_subsorder_status';
        var confirm_action  = confirm('Are you sure you want to update Subs Order No.' + subsorder_code + ' to cancelled ?');

        if (confirm_action) {

            if(subsorder_remarks != ""){

                $("#loading").fadeIn();
                var ajax = $.post(url, {subsorder_id: subsorder_id, subsorder_remarks: subsorder_remarks},
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
                $("#order_code").val(subsorder_remarks)
                $("#text-danger").attr('style', 'display:block')
            }
        }

        return false
    });
});
</script>