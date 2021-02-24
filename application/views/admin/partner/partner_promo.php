<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Promo <?php echo $partner->ptr_name?></h2>
    </div>
    <div class="col-4">
        <div class="title-action">
            <?php echo (in_array('add', $permits) ? '<a href="'.ADMIN_URL.'partner/promo_add/'.$partner->ptr_id.'" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;Create</a>' : '' );?>
        </div>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
            <div class="ibox">
                <div class="ibox-content">
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
                                <?php echo (in_array('delete', $permits) ? '<th>Action</th>' : '' );?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($all_data as $key => $value) {
                                $btn_color_inactive = 'btn-danger';
                                $btn_name_inactive = 'Delete';

                                // BEGIN ACTION URL
                                $action_str = '';
                                if(in_array('delete', $permits)){
                                    $action_str = '<td>';
                                    $action_str .= in_array('delete', $permits) ? '<a href="#" id="deact_'.$value->prm_id.'_'.$value->prm_name.'_'.$value->ptrpm_id.'" class="btn '.$btn_color_inactive.' btn-sm btn-block btn_inactive">'.$btn_name_inactive.'</a>' : '';
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
                                        <td>'.$value->prm_status.'</td>
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
    $(".btn_inactive").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/partner_promo_delete';
        var target = $(this).attr('id').split("_");
        var promo_id = target[1];
        var promo_name = target[2];
        var ptrpm_id = target[3];
        var update_status = $(this).text().toLowerCase();
        var confirm_action = confirm('Are you sure you want to ' + update_status + ' partner promo "'+ promo_name +'"?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {ptrpm_id: ptrpm_id},
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