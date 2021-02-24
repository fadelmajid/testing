<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>User Download</h2>
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
                        <div class="form-group col-5">
                            <div class="input-group date" id="search_date">
                                <input type="text" class="form-control" name="start" id="start_date" placeholder="Start date" value="<?php echo $start_date; ?>"/>
                                <span class="input-group-addon">to</span>
                                <input type="text" class="form-control" name="end" id="end_date" placeholder="End date" value="<?php echo $end_date; ?>" />
                            </div>
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
                                    <?php echo sort_table_icon($page_url, 'usrd_type', 'Type', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'usrd_date', 'Date', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'usrd_total', 'Total', $xtra_var); ?>
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
                                foreach ($data as $user_download):
                                    
                                    // BEGIN ACTION URL
                                    $action_str = '';
                                    if(in_array('edit', $permits)){
                                        $edit_url       = $current_url.'_add/'.$user_download->usrd_id;
                                        $action_str     = '<td>';
                                        $action_str     .= in_array('edit', $permits) ? '<a href="'.$edit_url.'" class="btn btn-primary btn-block btn-xs">Edit</a>' : '';
                                        $action_str     .= in_array('delete', $permits) ? '<a href="#" id="id_'.$user_download->usrd_id.'_" class="btn btn-xs btn-danger btn-block btn-delete">Delete</a>' : '';
                                        $action_str     .= '</td>';
                                    }
                                    // END ACTION URL

                        ?>
                                <tr>
                                    <td>
                                        <?php echo $user_download->usrd_id; ?>
                                    </td>
                                    <td>
                                        <?php echo $user_download->usrd_type; ?>
                                    </td>
                                    <td>
                                        <?php echo $user_download->usrd_date; ?>
                                    </td>
                                    <td>
                                        <?php echo $user_download->usrd_total; ?>
                                    </td>
                                    <td>
                                        <?php
                                            echo isset($arr_admin[$user_download->created_by]) ? $arr_admin[$user_download->created_by] : 'System';
                                            echo "<br/>".show_date($user_download->created_date);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            echo isset($arr_admin[$user_download->updated_by]) ? $arr_admin[$user_download->updated_by] : 'System';
                                            echo "<br/>".show_date($user_download->updated_date);
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
    // set cursor on search column
    $('#search').focus()

    // timepicker for search
    $('#start_date, #end_date').datetimepicker({
        timepicker:false,
        format:'Y-m-d',
        lang:'en'
    });

    //delete data
    $(".btn-delete").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/user_download_delete';
        var target = $(this).attr('id').split("_");
        var usrd_id = target[1];
        var status = $(this).text().toLowerCase();
        var confirm_action = confirm('Are you sure you want to ' + status + "?");

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {usrd_id: usrd_id},
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