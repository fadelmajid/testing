<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Courier</h2>
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
                                <th>
                                    <?php echo sort_table_icon($page_url, 'id', 'ID', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'courier_code', 'Code', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'courier_vendor', 'Courier Vendor', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'courier_desc', 'Description', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'courier_status', 'Status', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'is_default', 'Is Default', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'created', 'Created', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'updated', 'Updated', $xtra_var); ?>
                                </th>
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
                                foreach ($data as $courier):
                                    if ($courier->courier_status === $cst_status['active']) {
                                        $color      = 'text-info';
                                        $btn_color  = 'btn-warning';
                                        $btn_name   = 'Deactivate';
                                    } else {
                                        $color      = 'text-danger';
                                        $btn_color  = 'btn-info';
                                        $btn_name   = 'Activate';
                                    }
                                    
                                    // BEGIN ACTION URL
                                    $action_str = '';
                                    if(in_array('edit', $permits)){
                                        $edit_url       = $current_url.'_add/'.$courier->courier_id;
                                        $action_str     = '<td>';
                                        $action_str     .= in_array('edit', $permits) ? '<a href="#" id="id_'.$courier->courier_id.'_'.$courier->courier_code.'" class="btn btn-xs '.$btn_color.' btn-block btn_update">'.$btn_name.'</a>' : '';
                                        $action_str     .= in_array('edit', $permits) ? '<a href="'.$edit_url.'" class="btn btn-primary btn-block btn-xs">Edit</a>' : '';
                                        $action_str     .= in_array('delete', $permits) ? '<a href="#" id="id_'.$courier->courier_id.'_'.$courier->courier_code.'" class="btn btn-xs btn-danger btn-block btn_delete">Delete</a>' : '';
                                        $action_str     .= '</td>';
                                    }
                                    // END ACTION URL

                        ?>
                                <tr>
                                    <td>
                                        <?php echo $courier->courier_id; ?>
                                    </td>
                                    <td>
                                        <?php echo $courier->courier_code; ?>
                                    </td>
                                    <td>
                                        <?php echo $courier->courier_vendor; ?>
                                    </td>
                                    <td>
                                        <?php echo nl2br($courier->courier_desc); ?>
                                    </td>
                                    <td class="<?php echo $color; ?>">
                                        <strong>
                                            <?php echo ucfirst($courier->courier_status); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php
                                            if($courier->is_default==1){
                                                echo "True";
                                            }else{
                                                echo "False";
                                            }?>
                                    </td>
                                    <td>
                                        <?php
                                            echo isset($arr_admin[$courier->created_by]) ? $arr_admin[$courier->created_by] : 'System';
                                            echo "<br/>".show_date($courier->created_date);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            echo isset($arr_admin[$courier->updated_by]) ? $arr_admin[$courier->updated_by] : 'System';
                                            echo "<br/>".show_date($courier->updated_date);
                                        ?>
                                    </td>
                                    <?php echo $action_str ?>
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
<script>
$(document).ready(function(){
    // update status
    $(".btn_update").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/courier_status_update';
        var target = $(this).attr('id').split("_");
        var courier_id = target[1];
        var courier_code = target[2];
        var update_status = $(this).text().toLowerCase();
        var new_status = update_status == 'activate' ? '<?php echo $cst_status['active'];?>' : '<?php echo $cst_status['inactive'];?>';
        var confirm_action = confirm('Are you sure you want to ' + update_status + ' Courier "'+ courier_code +'"?');
        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {courier_id: courier_id, status: new_status},
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

    //delete data
    $(".btn_delete").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/courier_delete';
        var target = $(this).attr('id').split("_");
        var courier_id = target[1];
        var courier_code = target[2];
        var status = $(this).text().toLowerCase();
        var confirm_action = confirm('Are you sure you want to ' + status + ' Courier Code "'+ courier_code +'"?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {courier_id: courier_id},
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