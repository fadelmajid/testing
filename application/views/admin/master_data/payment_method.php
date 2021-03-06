<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Payment Method</h2>
    </div>
    <div class="col-4">
        <div class="title-action">
            <?php echo (in_array('add',$permits) ? '<a href="'.ADMIN_URL.'master_data/payment_method_add" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;Create</a>' : '' );?>
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
                                    <?php echo sort_table_icon($page_url, 'name', 'Name', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'code', 'Code', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'label', 'Label', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'desc', 'Description', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'order', 'Order', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'created', 'Created', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'updated', 'Updated', $xtra_var); ?>                               
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'status', 'Status', $xtra_var); ?>
                                </th>
                                <?php echo (in_array('edit', $permits) ? '<th>Action</th>' : '' );?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($all_data)) {
                                echo '
                                    <tr>
                                        <td class="error" colspan="100%">Data not found!</td>
                                    </tr>
                                ';
                            }else{
                                foreach ($all_data as $key => $value) {
                                    if ($value->pymtd_status === $cst_status['active']) {
                                        $color = 'text-info';
                                        $btn_color = 'btn-warning';
                                        $btn_name = 'Deactivate';
                                    } else {
                                        $color = 'text-danger';
                                        $btn_color = 'btn-info';
                                        $btn_name = 'Activate';
                                    }

                                    // BEGIN ACTION URL
                                    $action_str = '';
                                    if(in_array('edit',$permits)){
                                        $edit_url    = ADMIN_URL.'master_data/payment_method_add/'.$value->pymtd_id;
                                        $action_str  = '<td>';
                                        $action_str .= in_array('edit', $permits) ? '<a href="'.$edit_url.'" class="btn btn-xs btn-primary btn-block">Edit</a>' : '';
                                        $action_str .= in_array('edit', $permits) ? '<a href="#" id="id_'.$value->pymtd_id.'_'.$value->pymtd_name.'" class="btn btn-xs '.$btn_color.' btn-block btn_update">'.$btn_name.'</a>' : '';
                                        $action_str .= in_array('delete', $permits) ? '<a href="#" id="id_'.$value->pymtd_id.'_'.$value->pymtd_name.'" class="btn btn-xs btn-danger btn-block btn_delete">Delete</a>' : '';
                                        $action_str .= '</td>';
                                    }
                                    // END ACTION URL

                            ?>
                                    <tr><td><?php echo $value->pymtd_id; ?></td>
                                        <td><?php echo $value->pymtd_name; ?></td>
                                        <td><?php echo $value->pymtd_code; ?></td>
                                        <td><?php echo $value->pymtd_label; ?></td>
                                        <td><?php echo $value->pymtd_desc; ?></td>
                                        <td><?php echo $value->pymtd_order; ?></td>
                                        <td><?php echo isset($arr_admin[$value->created_by]) ? $arr_admin[$value->created_by] : 'System'; ?><br/>
                                            <?php echo show_date($value->created_date); ?></td>
                                        <td><?php echo isset($arr_admin[$value->updated_by]) ? $arr_admin[$value->updated_by] : 'System'; ?><br/>
                                            <?php echo show_date($value->updated_date); ?></td>
                                        <td class="<?php echo $color; ?>"><strong><?php echo ucfirst($value->pymtd_status); ?></strong></td>
                                        <?php echo $action_str ?>
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

<script>
$(document).ready(function(){
    //delete data
    $(".btn_delete").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/delete_payment_method';
        var target = $(this).attr('id').split("_");
        var pymtd_id = target[1];
        var pymtd_name = target[2];
        var status = $(this).text().toLowerCase();
        var confirm_action = confirm('Are you sure you want to ' + status + ' Payment Method "'+ pymtd_name +'"?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {pymtd_id: pymtd_id},
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

    // update payment method status
    $(".btn_update").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/payment_method_status_update';
        var target = $(this).attr('id').split("_");
        var pymtd_id = target[1];
        var pymtd_name = target[2];
        var update_status = $(this).text().toLowerCase();
        var new_status = update_status == 'activate' ? '<?php echo $cst_status['active'];?>' : '<?php echo $cst_status['inactive'];?>';
        var confirm_action = confirm('Are you sure you want to ' + update_status + ' Payment Method "'+ pymtd_name +'"?');
        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {pymtd_id: pymtd_id, status: new_status},
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