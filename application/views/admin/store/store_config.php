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
                        <div class="form-group col-3">
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
                                <th><?php echo sort_table_icon($page_url, 'id', 'ID', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'name', 'Store', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'min_cup', 'Min Cup', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'max_cup', 'Max Cup', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'min_order', 'Min Order', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'max_order', 'Max Order', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'range_data', 'Range (minute)', $xtra_var); ?></th>
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
                                foreach ($data as $store_config):
                                    // BEGIN ACTION URL
                                    $action_str = '';
                                    if(in_array('edit', $permits)){
                                        $edit_url = $current_url.'_add/'.$store_config->stcf_id;
                                        $action_str = '<td>';
                                        $action_str .= in_array('edit', $permits) ? '<a href="'.$edit_url.'" class="btn btn-xs btn-primary btn-block">Edit</a>' : '';
                                        $action_str .= in_array('delete', $permits) ? '<a href="#" id="'.$store_config->stcf_id.'_'.$store_config->st_name.'" class="btn btn-xs btn-danger btn-block btn_delete">Delete</a>' : '';
                                        $action_str .= '</td>';
                                    }
                                    // END ACTION URL
                        ?>
                            <tr>
                                <td><?php echo $store_config->stcf_id; ?></td>
                                <td><?php echo $store_config->st_name; ?></td>
                                <td><?php echo $store_config->stcf_min_cup; ?></td>
                                <td><?php echo $store_config->stcf_max_cup; ?></td>
                                <td><?php echo $store_config->stcf_min_order; ?></td>
                                <td><?php echo $store_config->stcf_max_order; ?></td>
                                <td><?php echo $store_config->stcf_range_data; ?></td>
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

<script>
$(document).ready(function(){
    //delete store config
    $(".btn_delete").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/delete_store_config';
        var target = $(this).attr('id').split("_");
        var stcf_id = target[0];
        var st_name = target[1];
        var confirm_action = confirm('Are you sure you want to delete "'+ st_name +'" Store Config ?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {stcf_id: stcf_id},
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