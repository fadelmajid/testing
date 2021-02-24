<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Voucher Default</h2>
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
                                    <?php echo sort_table_icon($page_url, 'id', 'Voucher Default ID', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'code', 'Voucher Defauld Code', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'type', 'Voucher Defauld Type', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'list', 'Voucher Default List', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'date', 'Created', $xtra_var); ?>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'updated', 'Updated Date', $xtra_var); ?>
                                </th>
                                <?php echo (in_array('edit', $permits) ? '<th>Action</th>' : '' );?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($all_data as $key => $value) {
                                // BEGIN ACTION URL
                                $action_str = '';
                                if(in_array('edit', $permits)){
                                    $edit_url = $current_url.'_add/'.$value->vcdef_id;
                                    $action_str = '<td>';
                                    $action_str .= in_array('edit', $permits) ? '<a href="'.$edit_url.'" class="btn btn-primary btn-block btn-xs">Edit</a>' : '';
                                    $action_str .= in_array('delete', $permits) ? '<a href="#" id="id_'.$value->vcdef_id.'_'.$value->vcdef_code.'" class="btn btn-xs btn-danger btn-block btn_delete">Delete</a>' : '';
                                    $action_str .= '</td>';
                                }

                                echo '
                                    <tr id="tr_'. $value->vcdef_id .'">
                                        <td>'.$value->vcdef_id.'</td> 
                                        <td>'.$value->vcdef_code.'</td> 
                                        <td>'.$value->vcdef_type.'</td>
                                        <td>'.$value->vcdef_list.'</td>
                                        <td>'.(isset($arr_admin[$value->created_by]) ? $arr_admin[$value->created_by] : 'system').'<br />
                                            '.show_date($value->created_date, true).'
                                        </td>
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
        var url = '<?php echo ADMIN_URL; ?>ajax/delete_voucher_default';
        var target = $(this).attr('id').split("_");
        var vsdef_id = target[1];
        var vcdef_code = target[2];
        var update_status = $(this).text().toLowerCase();
        var confirm_action = confirm('Are you sure you want to ' + update_status + ' voucher default "'+ vcdef_code +'"?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {vsdef_id: vsdef_id},
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