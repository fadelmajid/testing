<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Store Image</h2>
    </div>
    <div class="col-4">
        <div class="title-action">
            <?php echo (in_array('add',$permits) ? '<a href="'.ADMIN_URL.'store/store_image_add" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;Create</a>' : '' );?>
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
                                    <?php echo sort_table_icon($page_url, 'name', 'Store Name', $xtra_var); ?>
                                </th>
                                <th>Store Image</th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'order', 'Order', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'status', 'Status', $xtra_var); ?>
                                </th>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'created', 'Created', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'updated', 'Updated', $xtra_var); ?>
                                </th>
                                <?php echo (in_array('edit',$permits) ? '<th>Action</th>' : '' );?>
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
                                    if ($value->sti_status === $cst_status['active']) {
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
                                    if(in_array('edit', $permits)){
                                        $edit_url = ADMIN_URL.'store/store_image_add/'.$value->sti_id;
                                        $action_str = '<td>';
                                        $action_str .= in_array('edit', $permits) ? '<a href="#" id="id_'.$value->sti_id.'_'.$value->st_id.'" class="btn btn-xs '.$btn_color.' btn-block btn_update">'.$btn_name.'</a>' : '';
                                        $action_str .= in_array('edit', $permits) ? '<a href="'.$edit_url.'" class="btn btn-xs btn-primary btn-block">Edit</a>' : '';
                                        $action_str .= '</td>';
                                    }
                                    // END ACTION URL

                            ?>
                                    <tr>
                                        <td><?php echo $value->sti_id; ?></td>
                                        <td><?php echo $value->st_name; ?></td>
                                        <td class="text-center"><img src="<?php echo UPLOAD_URL.$value->sti_img; ?>" height=55px" alt=""></td>
                                        <td><?php echo $value->sti_order; ?></td>
                                        <td class="<?php echo $color; ?>"><strong><?php echo ucfirst($value->sti_status); ?></strong></td>
                                        <td><?php echo isset($arr_admin[$value->created_by]) ? $arr_admin[$value->created_by] : 'System'; ?><br/>
                                            <?php echo show_date($value->created_date); ?></td>
                                        <td><?php echo isset($arr_admin[$value->updated_by]) ? $arr_admin[$value->updated_by] : 'System'; ?><br/>
                                            <?php echo show_date($value->updated_date); ?></td>
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
    // update store_image status
    $(".btn_update").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/store_img_status_update';
        var target = $(this).attr('id').split("_");
        var sti_id = target[1];
        var st_id = target[2];
        var update_status = $(this).text().toLowerCase();
        var new_status = update_status == 'activate' ? '<?php echo $cst_status['active'];?>' : '<?php echo $cst_status['inactive'];?>';
        var confirm_action = confirm('Are you sure you want to ' + update_status + ' Store Image "'+ sti_id +'"?');
        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {sti_id: sti_id, sti_status: new_status},
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