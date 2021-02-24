<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Voucher Unassaigned</h2>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
            <div class="ibox">
                <div class="ibox-content">
                    <?php echo form_open($form_url, array('method'=>'get', 'class'=>'row form-inline')); ?>
                        <div class="form-group col-3">
                            <input type="text" name="search" placeholder="Search" value="<?php echo $search;?>" class="form-control col-12">
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
                        <?php
                        // BEGIN PERMISSION ACTION
                        if(in_array('export', $permits)){
                            echo '<div class="pull-right">
                                <a href="'.set_export_url($form_url,"export=xls").'" class="btn btn-success btn-sm">&nbsp;<i class="fa fa-file-excel-o"></i>&nbsp;</a>
                            </div>';
                        }
                        // END PERMISSION ACTION
                        echo form_close(); ?>
                    <div class="hr-line-dashed"></div>
                    <table class="table table-sticky table-bordered">
                        <thead>
                            <tr>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'id', 'ID', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'prm', 'Promo Name', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'user', 'User ID', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'code', 'Voucher Unassigned Code', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'status', 'Voucher Unassigned Status', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'expired', 'Expired Date', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'created', 'Created Date', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'updated', 'Updated Date', $xtra_var); ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($all_data as $key => $value) {
                                    $detail_url = $user_url.'/'.$value->user_id;
                            ?>
                                    <tr>
                                        <td><?php echo $value->vcu_id; ?></td>
                                        <td><?php echo $value->prm_name; ?></td>
                                        <td><?php
                                            if ($value->user_id > 0){
                                                echo '<a href="'.$detail_url.'">'.$value->user_id.'</a>';
                                            }else{
                                                echo $value->user_id;
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $value->vcu_code; ?></td>
                                        <td>
                                        <?php
                                            if ($value->vcu_status === $cst_status['active']) {
                                                $color = 'text-info';
                                            }else if ($value->vcu_status === $cst_status['used']) {
                                                $color = 'text-warning';
                                            }else {
                                                $color = 'text-danger';
                                            }

                                            echo '<span class="'.$color.'"><strong>'.ucfirst($value->vcu_status).'</strong></span><br/>';
                                        ?>
                                        </td>
                                        <td><?php echo show_date($value->expired_date, true); ?></td>
                                        <td><?php echo show_date($value->created_date, true); ?></td>
                                        <td><?php echo show_date($value->updated_date, true); ?></td>
                                    </tr>
                            <?php
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
    $('#start_date, #end_date').datetimepicker({
        timepicker:false,
        format:'Y-m-d',
        lang:'en'
    });
});
</script>