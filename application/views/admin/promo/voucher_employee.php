<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Voucher Employee</h2>
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
                            <input type="text" name="search" placeholder="Search" value="<?php echo $search;?>" class="form-control col-12">
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
                                    <?php echo sort_table_icon($page_url, 'user', 'User', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'name', 'Name', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'email', 'Email', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'phone', 'Phone', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'company', 'Company', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'position', 'Position', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'created', 'Created', $xtra_var); ?>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'updated', 'Updated', $xtra_var); ?>
                                </th>
                                <?php echo (in_array('edit', $permits) ? '<th>Action</th>' : '' );?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($all_data as $key => $vce) {
                                // BEGIN ACTION URL
                                $action_str = '';
                                if(in_array('edit', $permits)){
                                    $edit_url = $current_url.'_add/'.$vce->vce_id;
                                    $action_str = '<td>';
                                    $action_str .= in_array('edit', $permits) ? '<a href="'.$edit_url.'" class="btn btn-primary btn-block btn-xs">Edit</a>' : '';
                                    $action_str     .= in_array('delete', $permits) ? '<a href="#" id="id_'.$vce->vce_id.'_'.$vce->vce_name.'" class="btn btn-xs btn-danger btn-block btn_delete">Delete</a>' : '';
                                    $action_str .= '</td>';
                                }

                                echo '
                                    <tr id="tr_'. $vce->vce_id .'">
                                        <td>'.$vce->vce_id.'</td> 
                                        <td>'.$vce->user_id.'</td> 
                                        <td>'.$vce->vce_name.'</td>
                                        <td>'.$vce->vce_email.'</td>
                                        <td>'.$vce->vce_phone.'</td>
                                        <td>'.$vce->vce_organize_name.'</td>
                                        <td>'.$vce->vce_position.'</td>
                                        <td>'.(isset($arr_admin[$vce->created_by]) ? $arr_admin[$vce->created_by] : 'system').'<br />
                                            '.show_date($vce->created_date, true).'
                                        </td>
                                        <td>'.(isset($arr_admin[$vce->updated_by]) ? $arr_admin[$vce->updated_by] : 'system').'<br />
                                            '.show_date($vce->updated_date, true).'
                                        </td>
                                        '.$action_str.'
                                    </tr>
                                ';
                            }
                            if (empty($all_data)) {
                                echo '
                                    <tr>
                                        <td class="error" colspan="100%">Data not found!</td>
                                    </tr>
                                ';
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
        var url             = '<?php echo ADMIN_URL; ?>ajax/voucher_employee_delete';
        var target          = $(this).attr('id').split("_");
        var vce_id          = target[1];
        var vce_name        = target[2];
        var status          = $(this).text().toLowerCase();
        var confirm_action  = confirm('Are you sure you want to ' + status + ' Voucher Employee with employee name"'+ vce_name +'"?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {vce_id: vce_id},
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