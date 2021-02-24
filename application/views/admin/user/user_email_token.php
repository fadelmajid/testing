<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Users Email Token</h2>
    </div>
    <div class="col-4">
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
                    <?php echo form_close(); ?>
                    <div class="hr-line-dashed"></div>
                    <table class="table table-sticky table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo sort_table_icon($page_url, 'id', 'ID', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'user', 'User', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'user_email', 'User Email', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'uet_token', 'Uet Token', $xtra_var);?></th>
                                <th><?php echo sort_table_icon($page_url, 'uet_status', 'Uet Status', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'expired_date', 'Experied Date', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'created_date', 'Created Date', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'updated_date', 'Updated Date', $xtra_var); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($data)): ?>
                                <tr>
                                    <td class="error" colspan="100%">Data not found!</td>
                                </tr>
                        <?php else:
                                foreach ($data as $key => $row):
                            ?>
                                <tr>
                                    <td><?php echo $row->uet_id; ?></td>
                                    <td>
                                        <?php
                                            echo $row->user_id.'<br/>';
                                            echo isset($arr_admin[$row->user_id]) ? $arr_admin[$row->user_id] : 'System';
                                        ?>
                                    </td>
                                    <td><?php echo $row->user_email; ?><br><br></td>
                                    <td><?php echo $row->uet_token; ?><br><br></td>
                                    <td><?php echo $row->uet_status; ?><br><br></td>
                                    <td><?php echo $row->expired_date; ?><br><br></td>
                                    <td><?php echo $row->created_date; ?><br><br></td>
                                    <td><?php echo $row->updated_date; ?><br><br></td>
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

    $('#start_date, #end_date').datetimepicker({
        timepicker: false,
        format:'Y-m-d',
        lang:'en'
    });
});
</script>