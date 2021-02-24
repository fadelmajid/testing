<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Static Image</h2>
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
                                    <?php echo sort_table_icon($page_url, 'stat_id', 'ID', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'stat_code', 'Static Code', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'stat_title', 'Static Title', $xtra_var); ?>
                                </th>
                                <th>
                                    Static Image
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'created', 'Created', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'updated', 'Updated', $xtra_var); ?>
                                </th>
                                <?php echo (in_array('edit', $permits) || in_array('delete', $permits) ? '<th>Action</th>' : '' );?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($all_data)): ?>
                                <tr>
                                    <td class="error" colspan="100%">Data not found!</td>
                                </tr>
                                <?php
                            else:
                                foreach ($all_data as $static_image):
                                    
                                    // BEGIN ACTION URL
                                    $action_str = '';
                                    $action_str = '<td>';
                                    if(in_array('edit', $permits)){
                                        $edit_url = $current_url.'_add/'.$static_image->stat_id;
                                        $action_str .= in_array('edit', $permits) ? '<a href="'.$edit_url.'" class="btn btn-primary btn-block btn-xs">Edit</a>' : '';
                                        $action_str .= in_array('delete', $permits) ? '<a href="#" id="id_'. $static_image->stat_id .'_'.$static_image->stat_code.'" class="btn btn-danger btn-xs btn-block btn-delete">Delete</a>' : '';
                                    }
                                    $action_str .= '</td>';
                                    // END ACTION URL

                        ?>
                                <tr>
                                    <td><?php echo $static_image->stat_id; ?></td>
                                    <td><?php echo $static_image->stat_code; ?></td>
                                    <td><?php echo $static_image->stat_title; ?></td>
                                    <td class="text-center"><img src="<?php echo UPLOAD_URL.$static_image->stat_img; ?>" height=55px" alt=""></td>
                                    <td>
                                        <?php
                                            echo isset($arr_admin[$static_image->created_by]) ? $arr_admin[$static_image->created_by] : 'System';
                                            echo "<br/>".show_date($static_image->created_date);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            echo isset($arr_admin[$static_image->updated_by]) ? $arr_admin[$static_image->updated_by] : 'System';
                                            echo "<br/>".show_date($static_image->updated_date);
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
    //untuk kebutuhan delete article
    $(".btn-delete").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/static_image_delete';
        var target = $(this).attr('id').split("_");
        var stat_id = target[1];
        var stat_code = target[2];
        var status = $(this).text().toLowerCase();
        var confirm_action = confirm('Are you sure you want to ' + status + ' static code "'+ stat_code +'"?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {stat_id: stat_id},
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