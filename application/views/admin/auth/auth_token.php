<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Token list</h2>
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
                    <?php echo form_close(); ?>
                    <div class="hr-line-dashed"></div>
                    <table class="table table-sticky table-bordered">
                        <thead>
                            <tr>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'id', 'ID', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'name', 'Customer', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'device', 'Device', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'platform', 'Platform', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'access', 'Access Token - Refresh Token', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'status', 'Token Status', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'exp', 'Expired Date', $xtra_var); ?>
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
                                echo '
                                    <tr id="tr_'. $value->atoken_id .'">
                                        <td>'.$value->atoken_id.'</td> 
                                        <td>'.$value->user_name.'<br>
                                        '.$value->user_phone.'<br>
                                        '.$value->user_email.'</td>
                                        <td>'.$value->atoken_device.'</td>
                                        <td>'.$value->atoken_platform.'</td>
                                        <td> Access Token : '.$value->atoken_access.'<br>
                                        Refresh Token : '.$value->atoken_refresh.'</td>
                                        <td><strong>'.$value->atoken_status.'</strong></td>
                                        <td>'.show_date($value->expired_date, true).'</td>
                                        <td>'.show_date($value->created_date, true).'</td>
                                        <td>'.show_date($value->updated_date, true).'</td>
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
    $('#start_date, #end_date').datetimepicker({
        timepicker:false,
        format:'Y-m-d',
        lang:'en'
    });
});
</script>