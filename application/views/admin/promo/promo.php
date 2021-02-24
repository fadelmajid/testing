<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Promo</h2>
    </div>
    <div class="col-4">
        <div class="title-action">
            <?php echo (in_array('add', $permits) ? '<a href="'.ADMIN_URL.'promo/promo_add" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;Create</a>' : '' );?>
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
                                    <?php echo sort_table_icon($page_url, 'name', 'Promo Name', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'code', 'Promo Code', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'start', 'Promo Date', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'type', 'Promo Type', $xtra_var); ?>
                                </th>
                                <th>Promo Image</th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'status', 'Promo Status', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'by', 'Created By', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'date', 'Created Date', $xtra_var); ?>
                                </th>
                                <?php echo (in_array('edit', $permits) ? '<th>Action</th>' : '' );?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($all_data as $key => $value) {
                                $btn_color_active = 'btn-warning';
                                $btn_name_active = 'Activate';
                                $btn_color_inactive = 'btn-danger';
                                $btn_name_inactive = 'Deactivate';

                                // BEGIN ACTION URL
                                $action_str = '';
                                if(in_array('edit', $permits) || in_array('delete', $permits)){
                                    $edit_url = ADMIN_URL.'promo/promo_add/'.$value->prm_id;
                                    $action_str = '<td>';
                                    $action_str .= in_array('edit', $permits) ? '<a href="'.$edit_url.'" class="btn btn-primary btn-sm btn-block">Edit</a>' : '';
                                    if($value->prm_status == $cst_status['expired']){
                                        $action_str .= '';
                                    }else{
                                        if ($value->prm_status === $cst_status['pending']) {
                                            $action_str .= in_array('delete', $permits) ? '<a href="#" id="act_'.$value->prm_id.'_'.$value->prm_name.'" class="btn '.$btn_color_active.' btn-sm btn-block btn_active">'.$btn_name_active.'</a>' : '';
                                            $action_str .= in_array('delete', $permits) ? '<a href="#" id="deact_'.$value->prm_id.'_'.$value->prm_name.'" class="btn '.$btn_color_inactive.' btn-sm btn-block btn_inactive">'.$btn_name_inactive.'</a>' : '';
                                        } else if ($value->prm_status === $cst_status['active']) {
                                            $action_str .= in_array('delete', $permits) ? '<a href="#" id="deact_'.$value->prm_id.'_'.$value->prm_name.'" class="btn '.$btn_color_inactive.' btn-sm btn-block btn_inactive">'.$btn_name_inactive.'</a>' : '';
                                        } else {
                                            $action_str .= in_array('delete', $permits) ? '<a href="#" id="deact_'.$value->prm_id.'_'.$value->prm_name.'" class="btn '.$btn_color_active.' btn-sm btn-block btn_active">'.$btn_name_active.'</a>' : '';
                                        }
                                    }
                                    $action_str .= '</td>';
                                }
                                // END ACTION URL
                                
                                $start = show_date($value->prm_start, true);
                                $end = show_date($value->prm_end, true);
                                $create = show_date($value->created_date, true);
                                echo '
                                    <tr id="tr_'. $value->prm_id .'">
                                        <td>'.$value->prm_id.'</td>
                                        <td>'.$value->prm_name.'</td>
                                        <td>'.$value->prm_custom_code.'</td>
                                        <td>'.$start.' - '.$end.'</td>
                                        <td>'.$value->prm_type.'</td>
                                        <td><img src="'.UPLOAD_URL.$value->prm_img.'" alt="promo-img" style="height: 50px; width: auto;"></td>
                                        <td>'.ucfirst($value->prm_status).'</br>Visible : '.ucfirst($value->prm_visible).'</td>
                                        <td>'.(isset($arr_admin[$value->created_by]) ? $arr_admin[$value->created_by] : 'system').'</td>
                                        <td>'.$create.'</td>
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
    // update user status
    $(".btn_active").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/promo_status_active';
        var target = $(this).attr('id').split("_");
        var promo_id = target[1];
        var promo_name = target[2];
        var update_status = $(this).text().toLowerCase();
        var confirm_action = confirm('Are you sure you want to ' + update_status + ' promo "'+ promo_name +'"?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {promo_id: promo_id},
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

    $(".btn_inactive").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/promo_status_inactive';
        var target = $(this).attr('id').split("_");
        var promo_id = target[1];
        var promo_name = target[2];
        var update_status = $(this).text().toLowerCase();
        var confirm_action = confirm('Are you sure you want to ' + update_status + ' promo "'+ promo_name +'"?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {promo_id: promo_id},
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