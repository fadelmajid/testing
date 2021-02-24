
<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>


<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2>Gosend Logs</h2>
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
                                <th><?php echo sort_table_icon($page_url, 'type', 'Type', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'date', 'Date', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'endpoint', 'Endpoint', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'header', 'Header', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'request', 'Request', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'response', 'Response', $xtravar);?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $col_number = 7;
                                foreach ($all_data as $value){
                                    echo '
                                        <tr>
                                            <td>'. $value->gosend_id .'</td>
                                            <td>'. $value->gosend_type .'</td>
                                            <td>'. show_date($value->created_date, true) .'</td>
                                            <td>'. $value->gosend_endpoint .'</td>
                                            <td>
                                                <a href="#" data-clipboard-target="#header'.$value->gosend_id.'" id="gosend">'. substr($value->gosend_header, 0, 50) .'</a>
                                                <textarea id="header'. $value->gosend_id .'" style="opacity: .01;height:0;position:absolute;z-index: -1;">'. $value->gosend_header .'</textarea>
                                            </td>
                                            <td>
                                                <a href="#" data-clipboard-target="#request'.$value->gosend_id.'" id="gosend">'. substr($value->gosend_request, 0, 50) .'</a>
                                                <textarea id="request'. $value->gosend_id .'" style="opacity: .01;height:0;position:absolute;z-index: -1;">'. $value->gosend_request .'</textarea></td>
                                            <td>
                                                <a href="#" data-clipboard-target="#response'.$value->gosend_id.'" id="gosend">'. substr($value->gosend_response, 0, 50) .'
                                                <textarea id="response'. $value->gosend_id .'" style="opacity: .01;height:0;position:absolute;z-index: -1;">'. $value->gosend_response .'</textarea>
                                            </td>
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
        $('#from, #to').datetimepicker({
            timepicker: false,
            format:'Y-m-d',
            lang:'en'
        });

        new ClipboardJS("#gosend");
    });
</script>