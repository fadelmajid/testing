<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Users</h2>
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
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    <?php
                    // BEGIN PERMISSION ACTION
                    if(in_array('export', $permits)){
                        echo '<div class="pull-right">
                            <a href="'. set_export_url($form_url,"export=xls") .'" class="btn btn-success btn-sm">&nbsp;<i class="fa fa-file-excel-o"></i>&nbsp;</a>
                        </div>';
                    }
                    // END PERMISSION ACTION
                    echo form_close(); ?>

                    <div class="hr-line-dashed"></div>
                    <table class="table table-sticky table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo sort_table_icon($page_url, 'id', 'ID', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'name', 'Customer', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'code', 'Ref. Code', $xtra_var);?></th>
                                <th><?php echo sort_table_icon($page_url, 'last_login', 'Last Login', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'last_activity', 'Last Activity', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'balance', 'Balance', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'status', 'Status', $xtra_var); ?></th>
                                <?php echo (in_array('edit', $permits) ? '<th>Action</th>' : '' );?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($data)){ ?>
                                <tr>
                                    <td class="error" colspan="100%">Data not found!</td>
                                </tr>
                        <?php }else{
                                foreach ($data as $user){
                                    $detail_url = $current_url.'_detail/'.$user->user_id;
                                    $register_date = explode(', ', show_date($user->created_date, true));
                                    $last_login = explode(', ', show_date($user->last_login, true));
                                    $last_activity = explode(', ', show_date($user->last_activity, true));
                                    if ($user->user_status === $cst_status['active']) {
                                        $color = 'text-info';
                                        $btn_color = 'btn-danger';
                                        $btn_name = 'Deactivate';
                                    } else {
                                        $color = 'text-danger';
                                        $btn_color = 'btn-info';
                                        $btn_name = 'Activate';
                                    }

                                    // BEGIN ACTION URL
                                    $action_str = '<td>';
                                    if(in_array('status', $permits)){
                                        $action_str .= '<a href="#" id="id_'.$user->user_id.'_'.$user->user_name.'" class="btn btn-sm '.$btn_color.' btn_update m-xs">'.$btn_name.'</a>';
                                    }
                                    $action_str .= '</td>';
                                    // END ACTION URL



                            ?>
                                <tr>
                                    <td><?php echo $user->user_id; ?></td>
                                    <td>
                                        <a href="<?php echo $detail_url; ?>"><?php echo $user->user_name; ?></a><br>
                                        <?php echo $user->user_phone; ?><br>
                                        <?php echo $user->user_email; ?><br><br>
                                        Member since: <?php echo $register_date[0]; ?>
                                    </td>
                                    <td><?php echo $user->user_code; ?></td>
                                    <td>
                                        <?php
                                            if(isset($user->last_login)) {
                                                echo $last_login[0] .'<br>'. $last_login[1];
                                            };
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if(isset($user->last_activity)) {
                                            echo $last_activity[0] .'<br>'. $last_activity[1];
                                        }
                                        ?>
                                    </td>
                                    <td class="text-right"><?php echo number_format($user->uwal_balance, 0, ',', '.'); ?></td>
                                    <td><span class="<?php echo $color; ?>"><strong><?php echo ucfirst($user->user_status); ?></strong></span></td>
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
    // update user status
    $(".btn_update").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/user_status_update';
        var target = $(this).attr('id').split("_");
        var user_id = target[1];
        var user_name = target[2];
        var update_status = $(this).text().toLowerCase();
        var new_status = update_status == 'activate' ? '<?php echo $cst_status['active'];?>' : '<?php echo $cst_status['inactive'];?>';
        var confirm_action = confirm('Are you sure you want to ' + update_status + ' User "' + user_name + '"?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {user_id: user_id, status: new_status},
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

    $('#start_date, #end_date').datetimepicker({
        timepicker: false,
        format:'Y-m-d',
        lang:'en'
    });
});
</script>