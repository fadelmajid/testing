<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Products</h2>
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
                                <th><?php echo sort_table_icon($page_url, 'id', 'ID', $xtra_var); ?></th>
                                <th style="width:15%"><?php echo sort_table_icon($page_url, 'name', 'Name', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'category', 'Category', $xtra_var); ?></th>
                                <th style="width:20%"><?php echo sort_table_icon($page_url, 'desc', 'Description', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'order', 'Product Order', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'status', 'Status', $xtra_var); ?></th>
                                <th style="width:10%"><?php echo sort_table_icon($page_url, 'price', 'Price', $xtra_var); ?></th>
                                <?php echo (in_array('edit', $permits) ? '<th>Action</th>' : '' );?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($data)): ?>
                                <tr>
                                    <td class="error" colspan="100%">Data not found!</td>
                                </tr>
                        <?php
                            else:
                                foreach ($data as $product):
                                    if ($product->pd_status === $cst_status['active']) {
                                        $color = 'text-info';
                                        $btn_color = 'btn-danger';
                                        $btn_name = 'Deactivate';
                                    } else {
                                        $color = 'text-danger';
                                        $btn_color = 'btn-info';
                                        $btn_name = 'Activate';
                                    }
                                    
                                    // BEGIN ACTION URL
                                    $action_str = '';
                                    if(in_array('edit', $permits)){
                                        $edit_url = $current_url.'_add/'.$product->pd_id;
                                        $action_str = '<td>';
                                        $action_str .= in_array('edit', $permits) ? '<a href="'.$edit_url.'" class="btn btn-primary btn-block btn-xs">Edit</a>' : '';
                                        $action_str .= in_array('edit', $permits) ? '<a href="#" id="id_'.$product->pd_id.'_'.$product->pd_name.'" class="btn btn-xs '.$btn_color.' btn-block btn_update">'.$btn_name.'</a>' : '';
                                        $action_str .= '</td>';
                                    }
                                    // END ACTION URL

                                    if ($product->pd_base_price > 0) {
                                        $base_price = '<span class="text-danger"><s>'.number_format($product->pd_base_price, 0, ',', '.').'</s></span>';
                                    } else {
                                        $base_price = '';
                                    }
                        ?>
                                <tr>
                                    <td><?php echo $product->pd_id; ?></td>
                                    <td><?php echo $product->pd_name; ?></td>
                                    <td><?php echo $product->cat_name; ?></td>
                                    <td><?php echo nl2br($product->pd_desc); ?></td>
                                    <td><?php echo $product->pd_order; ?></td>
                                    <td class="<?php echo $color; ?>"><strong><?php echo ucfirst($product->pd_status); ?></strong></td>
                                    <td class="text-right">
                                        <?php echo number_format($product->pd_final_price, 0, ',', '.'); ?><br>
                                        <?php echo $base_price; ?>
                                    </td>
                                    <?php echo $action_str ?>
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
    // update user status
    $(".btn_update").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/product_status_update';
        var target = $(this).attr('id').split("_");
        var product_id = target[1];
        var product_name = target[2];
        var update_status = $(this).text().toLowerCase();
        var new_status = update_status == 'activate' ? '<?php echo $cst_status['active'];?>' : '<?php echo $cst_status['inactive'];?>';
        var confirm_action = confirm('Are you sure you want to ' + update_status + ' Product "'+ product_name +'"?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {product_id: product_id, status: new_status},
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