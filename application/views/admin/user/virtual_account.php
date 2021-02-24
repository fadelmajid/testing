
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>


<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2>Virtual Account</h2>
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
                                <th><?php echo sort_table_icon($page_url, 'user', 'User', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'bank', 'Bank Code', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'provider', 'Provider', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'acount_name', 'Account Name', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'account_num', 'Account Number', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'response', 'Response', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'created', 'Created', $xtravar);?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $col_number = 8;
                                foreach ($all_data as $value){
                                    echo '
                                        <tr>
                                            <td>'. $value->uva_id .'</td>
                                            <td>'. $value->user_name.'<br />'. $value->user_phone .'<br />'. $value->user_email .'</td>
                                            <td>'. $value->bank_code .'</td>
                                            <td>'. $value->uva_provider .'</td>
                                            <td>'. $value->uva_account_name .'</td>
                                            <td>'. $value->uva_account_number .'</td>
                                            <td>
                                                <a href="#" data-clipboard-target="#header'.$value->uva_id.'" id="va">'. substr($value->uva_response, 0, 50) .'</a>
                                                <textarea id="header'. $value->uva_id .'" style="opacity: .01;height:0;position:absolute;z-index: -1;">'. $value->uva_response .'</textarea>
                                            </td>
                                            <td>'. show_date($value->created_date, true) .'</td>
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

<!-- CLIPBOARD PLUGIN -->
<script src="<?php echo ADMIN_TEMPLATE_URL;?>js/dist/clipboard.min.js"></script>

<script type="text/javascript">
    $(function () {
        var id = "";
        $('#from, #to').datetimepicker({
            timepicker: false,
            format:'Y-m-d',
            lang:'en'
        });

        new ClipboardJS("#va");
    });
</script>