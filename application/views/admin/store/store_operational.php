<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Store Operational</h2>
    </div>
    <div class="col-4">
        <div class="title-action">
            <?php echo (in_array('add', $permits) ? '<a href="'.$current_url.'_add" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;Create</a>' : '' );?>
        </div>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
            <div class="ibox">
                <div class="ibox-content">
                    <?php echo form_open($form_url, array('method'=>'get', 'class'=>'row form-inline')); ?>
                        <div class="form-group col-8">
                            <input type="text" name="search" placeholder="Search"value="<?php echo $search;?>" class="form-control col-12">
                        </div>
                        <div class="form-group col-2">
                            <button type="submit" class="btn btn-default">
                                &nbsp;<i class="fa fa-search"></i>&nbsp;
                            </button>
                        </div>
                    <?php echo form_close(); ?>
                    <div class="hr-line-dashed"></div>
                    <table class="table table-sticky table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo sort_table_icon($page_url, 'st_name', 'Store', $xtra_var); ?></th>
                                <th width="9%"><?php echo sort_table_icon($page_url, 'monday', 'Monday', $xtra_var); ?></th>
                                <th width="9%"><?php echo sort_table_icon($page_url, 'tuesday', 'Tuesday', $xtra_var); ?></th>
                                <th width="9%"><?php echo sort_table_icon($page_url, 'wednesday', 'Wednesday', $xtra_var); ?></th>
                                <th width="9%"><?php echo sort_table_icon($page_url, 'thursday', 'Thursday', $xtra_var); ?></th>
                                <th width="9%"><?php echo sort_table_icon($page_url, 'friday', 'Friday', $xtra_var); ?></th>
                                <th width="9%"><?php echo sort_table_icon($page_url, 'saturday', 'Saturday', $xtra_var); ?></th>
                                <th width="9%"><?php echo sort_table_icon($page_url, 'sunday', 'Sunday', $xtra_var); ?></th>
                                <th ><?php echo sort_table_icon($page_url, 'start_date', 'Date', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'created', 'Created', $xtra_var); ?></th>
                                <?php echo (in_array('edit', $permits) ? '<th>Action</th>' : '' );?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($data)): ?>
                                <tr>
                                    <td class="error" colspan="100%">Data not found!</td>
                                </tr>
                        <?php
                            else:
                                foreach ($data as $store_opt):
                                    if ($store_opt->sto_status === $cst_status['active']) {
                                        $color = 'text-info';
                                        $btn_color = 'btn-warning';
                                        $btn_name = 'Deactivate';
                                    } else {
                                        $color = 'text-danger';
                                        $btn_color = 'btn-info';
                                        $btn_name = 'Activate';
                                    }

                                    $monday         = json_decode($store_opt->monday, true);
                                    $tuesday        = json_decode($store_opt->tuesday, true);
                                    $wednesday      = json_decode($store_opt->wednesday, true);
                                    $thursday       = json_decode($store_opt->thursday, true);
                                    $friday         = json_decode($store_opt->friday, true);
                                    $saturday       = json_decode($store_opt->saturday, true);
                                    $sunday         = json_decode($store_opt->sunday, true);

                                    $data_monday    = 'pickup : <br>'.$monday['pickup']['open'].' - '.$monday['pickup']['close'].'<br> delivery : <br>'.$monday['delivery']['open'].' - '.$monday['delivery']['close'];
                                    $data_tuesday   = 'pickup : <br>'.$tuesday['pickup']['open'].' - '.$tuesday['pickup']['close'].'<br> delivery : <br>'.$tuesday['delivery']['open'].' - '.$tuesday['delivery']['close'];
                                    $data_wednesday = 'pickup : <br>'.$wednesday['pickup']['open'].' - '.$wednesday['pickup']['close'].'<br> delivery : <br>'.$wednesday['delivery']['open'].' - '.$wednesday['delivery']['close'];
                                    $data_thursday  = 'pickup : <br>'.$thursday['pickup']['open'].' - '.$thursday['pickup']['close'].'<br> delivery : <br>'.$thursday['delivery']['open'].' - '.$thursday['delivery']['close'];
                                    $data_friday    = 'pickup : <br>'.$friday['pickup']['open'].' - '.$friday['pickup']['close'].'<br> delivery : <br>'.$friday['delivery']['open'].' - '.$friday['delivery']['close'];
                                    $data_saturday  = 'pickup : <br>'.$saturday['pickup']['open'].' - '.$saturday['pickup']['close'].'<br> delivery : <br>'.$saturday['delivery']['open'].' - '.$saturday['delivery']['close'];
                                    $data_sunday    = 'pickup : <br>'.$sunday['pickup']['open'].' - '.$sunday['pickup']['close'].'<br> delivery : <br>'.$sunday['delivery']['open'].' - '.$sunday['delivery']['close'];

                                    
                                    // BEGIN ACTION URL
                                    $action_str = '';
                                    if(in_array('edit', $permits)){
                                        $edit_url = $current_url.'_add/'.$store_opt->sto_id;
                                        $action_str = '<td>';
                                        $action_str .= in_array('edit', $permits) ? '<a href="'.$edit_url.'" class="btn btn-xs btn-primary btn-block">Edit</a>' : '';
                                        $action_str .= in_array('edit', $permits) ? '<a href="#" id="id_'.$store_opt->sto_id.'_'.$store_opt->st_name.'" class="btn btn-xs '.$btn_color.' btn-block btn_update">'.$btn_name.'</a>' : '';
                                        $action_str .= in_array('delete', $permits) ? '<a href="#" id="id_'.$store_opt->sto_id.'_'.$store_opt->st_name.'" class="btn btn-xs btn-danger btn-block btn_delete">Delete</a>' : '';
                                        $action_str .= '</td>';
                                    }
                                    // END ACTION URL
                        ?>
                                <tr>
                                    <td><?php echo $store_opt->st_name.'<br>#'.$store_opt->sto_id; ?></td>
                                    <td><?php echo $data_monday; ?></td>
                                    <td><?php echo $data_tuesday; ?></td>
                                    <td><?php echo $data_wednesday; ?></td>
                                    <td><?php echo $data_thursday; ?></td>
                                    <td><?php echo $data_friday; ?></td>
                                    <td><?php echo $data_saturday; ?></td>
                                    <td><?php echo $data_sunday; ?></td>
                                    <td>Start : <?php echo show_date($store_opt->start_date); ?><br>
                                        End &nbsp;: <?php echo show_date($store_opt->end_date); ?></td>
                                    <td><?php echo isset($arr_admin[$store_opt->created_by]) ? $arr_admin[$store_opt->created_by] : 'System'; ?><br/>
                                        <?php echo show_date($store_opt->created_date); ?><br>
                                        <strong class="<?php echo $color; ?>"><?php echo ucfirst($store_opt->sto_status); ?></strong></td>
                                    <?php echo $action_str; ?>
                                </tr>
                        <?php 
                                endforeach;
                            endif;
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
        var id = "";
        $('#from, #to').datetimepicker({
            timepicker: false,
            format:'Y-m-d',
            lang:'en'
        });

    });
</script>

<script>
$(document).ready(function(){
    //delete store operational
    $(".btn_delete").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/delete_store_operational';
        var target = $(this).attr('id').split("_");
        var sto_id = target[1];
        var st_id = target[2];
        var status = $(this).text().toLowerCase();
        var confirm_action = confirm('Are you sure you want to ' + status + ' Store Operational "'+ st_id +'"?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {sto_id: sto_id},
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

    // update store operational status
    $(".btn_update").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/store_opt_status_update';
        var target = $(this).attr('id').split("_");
        var sto_id = target[1];
        var st_name = target[2];
        var update_status = $(this).text().toLowerCase();
        var new_status = update_status == 'activate' ? '<?php echo $cst_status['active'];?>' : '<?php echo $cst_status['inactive'];?>';
        var confirm_action = confirm('Are you sure you want to ' + update_status + ' Store Operational "'+ st_name +'"?');
        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {sto_id: sto_id, status: new_status},
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
});
</script>