<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2>Log Invalid Login</h2>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
            <div class="ibox">
                <div class="ibox-content">
                    <?php echo form_open($form_url, array('method'=>'get', 'class'=>'form-inline')); ?>
                        <div class="form-group row col-8">
                            <input type="text" name="search" placeholder="Search" value="<?php echo $search;?>" class="form-control col-12">
                        </div>
                        <div class="form-group col-4">
                            <button type="submit" class="btn btn-default">&nbsp;<i class="fa fa-search"></i>&nbsp;</button>
                        </div>
                    <?php echo form_close(); ?>
                    <div class="hr-line-dashed"></div>
                    <table class="table table-sticky table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo sort_table_icon($page_url, 'id', 'ID', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'name', 'Username', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'pass', 'Password', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'ipaddress', 'IP Address', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'time', 'Date Time', $xtravar);?></th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($all_data as $key => $value){
                                    
                                    $url_delete = '#';
                                    
                                    echo '
                                        <tr id="tr_'. $value->log_id .'">
                                            <td>'. $value->log_id .'</td>
                                            <td>'. $value->log_username .'</td>
                                            <td>'. $value->log_password .'</td>
                                            <td>'. $value->log_ipaddress .'</td>
                                            <td>'. $value->log_time .'</td>
                                            <td>
                                                '. ( in_array('delete', $permits) ? '<a href="'. $url_delete .'" id="del_'. $value->log_id .'" class="btn btn-danger btn-sm btndelete">Delete</a>' : '' ) .'
                                            </td>
                                        </tr>
                                    ';
                                }
                                if(empty($all_data)){
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
    //untuk kebutuhan delete article

    $("table td a.btndelete").click(function () {

        var target 	= $(this).attr("id").split("_");
        var id		= target[1];
        var me 		= $("#tr_"+id);
        var urllink = "<?php echo ADMIN_URL;?>ajax/invalidlogin_delete";

        var konfirm = confirm('Are you sure you want to delete this id '+ id +' ?');
        if(konfirm){
            $("#loading").fadeIn(); //show when submitting
            var jqpost = $.post(urllink,  { log_id: id} ,
            function(data){
                $("#loading").fadeOut(); //hide when data's ready
                if(data == "Success"){
                    me.fadeTo(400, 0, function () {
                        me.remove();
                    });
                }else{
                    alert(data);
                }
            } );
        }

        return false;
    });

});
</script>