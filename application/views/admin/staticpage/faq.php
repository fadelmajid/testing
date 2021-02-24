<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>FAQ</h2>
    </div>
    <div class="col-4">
        <div class="title-action">
            <?php echo (in_array('add', $permits) ? '<a href="'.$current_url.'_add" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;Create</a>' : '' );?>
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
                                    <?php echo sort_table_icon($page_url, 'question', 'Question', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'answer', 'Answer', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'order', 'Order', $xtra_var); ?>
                                </th>
                                <?php echo (in_array('edit', $permits) || in_array('delete', $permits) ? '<th>Action</th>' : '' );?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($all_data as $key => $value) {
                                // BEGIN ACTION URL
                                $action_str = '';
                                if(in_array('edit', $permits) || in_array('delete', $permits)){
                                    $edit_url = $current_url.'_add/'.$value->faq_id;
                                    $action_str = '<td>';
                                    $action_str .= in_array('edit', $permits) ? '<a href="'.$edit_url.'" class="btn btn-primary btn-sm">Edit</a>' : '';
                                    $action_str .= in_array('delete', $permits) ? '&nbsp;<a href="#" id="del_'. $value->faq_id .'" class="btn btn-danger btn-sm btn-delete">Delete</a>' : '';
                                    $action_str .= '</td>';
                                }
                                // END ACTION URL
                                echo '
                                    <tr id="tr_'. $value->faq_id .'">
                                        <td>'.$value->faq_id.'</td>
                                        <td>'.$value->faq_question.'</td>
                                        <td>'.$value->faq_answer.'</td>
                                        <td>'.$value->faq_order.'</td>
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
    //untuk kebutuhan delete article

    $("table td a.btndelete").click(function () {

        var target 	= $(this).attr("id").split("_");
        var id		= target[1];
        var me 		= $("#tr_"+id);
        var urllink = "<?php echo ADMIN_URL;?>ajax/faq_delete";

        var konfirm = confirm('Are you sure you want to delete this id '+ id +' ?');
        if(konfirm){
            $("#loading").fadeIn(); //show when submitting
            var jqpost = $.post(urllink,  { faq_id: id} ,
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