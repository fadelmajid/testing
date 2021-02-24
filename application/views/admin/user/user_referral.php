<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Users Referral</h2>
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
                                <th><?php echo sort_table_icon($page_url, 'from', 'From', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'to', 'To', $xtra_var);?></th>
                                <th><?php echo sort_table_icon($page_url, 'purch', 'Is Purchased', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'created', 'Created Date', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'updated', 'Updated Date', $xtra_var); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($data)): ?>
                                <tr>
                                    <td class="error" colspan="100%">Data not found!</td>
                                </tr>
                        <?php else:
                                foreach ($data as $key => $value):
                            ?>
                                <tr>
                                    <td><?php echo $value->uref_id; ?></td>
                                    <td>
                                        <?php echo $value->from_name; ?><br>
                                        <?php echo $value->from_phone; ?><br>
                                        <?php echo $value->from_email; ?><br>
                                    </td>
                                    <td>
                                        <?php echo $value->to_name; ?><br>
                                        <?php echo $value->to_phone; ?><br>
                                        <?php echo $value->to_email; ?><br>
                                    </td>
                                    <td><?php echo ($value->uref_to_purchased == 1 ? 'yes' : ($value->uref_to_purchased == 2 ? 'disabled' : 'no'));?></td>
                                    <td>
                                        <?php
                                            $date = explode(', ', show_date($value->created_date, true));
                                            echo $date[0].'<br>'.$date[1];
                                        ?>
                                    </td>
                                    <td><?php echo show_date($value->updated_date, true); ?></td>
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