
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>


<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2>E-Money</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <!-- /.row -->
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
                                <input type="text" class="form-control" name="from" id="from" placeholder="Start date" value="<?php echo $from; ?>"/>
                                <span class="input-group-addon">to</span>
                                <input type="text" class="form-control" name="to" id="to" placeholder="End date" value="<?php echo $to; ?>" />
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
                                <th><?php echo sort_table_icon($page_url, 'id', 'ID', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'user', 'User Name', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'method', 'Payment Method', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'number', 'No Handphone', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'created', 'Created Date', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'updated', 'Updated Date', $xtravar);?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $col_number = 6;
                                foreach ($all_data as $value){
                                    echo '
                                        <tr>
                                            <td>'. $value->emy_id .'</td>
                                            <td>'. $value->user_name.'</td>
                                            <td>'. $value->pymtd_name.'</td>
                                            <td>'. $value->emy_number .'</td>
                                            <td>'. show_date($value->created_date, true) .'</td>
                                            <td>'. show_date($value->updated_date, true) .'</td>
                                        </tr>
                                    ';
                                }
                                if(empty($all_data)){
                                    echo '
                                        <tr>
                                            <td class="error" colspan="'. $col_number .'">Data not found!</td>
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

<script type="text/javascript">
    $(function () {
        $('#from, #to').datetimepicker({
            timepicker: false,
            format:'Y-m-d',
            lang:'en'
        });
    });
</script>
