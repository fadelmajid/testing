<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>App Version</h2>
    </div>
    <div class="col-4">
        <div class="title-action">
            <?php echo (in_array('add', $permits) ? '<a href="'.ADMIN_URL.'appversion/appversion_add" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;Create</a>' : '' );?>
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
                                    <?php echo sort_table_icon($page_url, 'code', 'Version Code', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'platform', 'Platform', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'status', 'Status', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'by', 'Created By', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'created', 'Created Date', $xtra_var); ?>
                                </th>
                                <th>
                                    Updated By
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'updated', 'Updated Date', $xtra_var); ?>
                                </th>
                                <?php echo (in_array('edit', $permits) ? '<th>Action</th>' : '' );?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($all_data as $key => $value) {
                                $btn_color_inactive = 'btn-danger';
                                $btn_name_inactive = 'Delete';
                                // BEGIN ACTION URL
                                $action_str = '';
                                if(in_array('edit', $permits)){
                                    $edit_url = ADMIN_URL.'appversion/appversion_add/'.$value->ver_id;
                                    $action_str = '<td>';
                                    $action_str .= '<a href="'.$edit_url.'" class="btn btn-primary btn-sm btn-block">Edit</a>';
                                    $action_str .= in_array('delete', $permits) ? '<a href="#" data-id="'.$value->ver_id.'" class="btn '.$btn_color_inactive.' btn-sm btn-block btn_delete">'.$btn_name_inactive.'</a>' : '';
                                    $action_str .= '</td>';
                                } 
                                // END ACTION URL
                                echo '
                                    <tr id="tr_'. $value->ver_id .'">
                                        <td>'.$value->ver_id.'</td>
                                        <td>'.$value->ver_code.'</td>
                                        <td>'.$value->ver_platform.'</td> 
                                        <td>'.$value->ver_status.'</td>
                                        <td>'.(isset($arr_admin[$value->created_by]) ? $arr_admin[$value->created_by] : 'system').'</td>
                                        <td>'.show_date($value->created_date, true).'</td> 
                                        <td>'.(isset($arr_admin[$value->updated_by]) ? $arr_admin[$value->updated_by] : 'system').'</td>
                                        <td>'.show_date($value->updated_date, true).'</td>
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
    $(".btn_delete").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/appversion_delete';
        var id = $(this).data('id');
        var confirm_action = confirm('Are you sure you want to delete this id = ' + id +'?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {ver_id: id},
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