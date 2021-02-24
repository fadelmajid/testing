<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-6">
        <h2>Store Products</h2>
    </div>
    <div class="col-6">
        <div class="title-action">
        <?php if (in_array('bulk_process', $permits)){ ?>

            <a href="<?php echo $current_url?>_update_perproduct" class="btn btn-primary btn-sm">&nbsp;Bulk Update Perproduct</a>
            <a href="<?php echo $current_url?>_update_perstore" class="btn btn-primary btn-sm">&nbsp;Bulk Update Perstore</a>
            <a href="<?php echo $current_url?>_import" class="btn btn-primary btn-sm"><i class="fa fa-download"></i>&nbsp;Import</a>
        
        <?php }if (in_array('add', $permits)){ ?>
            <a href="<?php echo $current_url?>_add" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;Create</a>
        <?php } ?>
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
                                <th><?php echo sort_table_icon($page_url, 'store', 'Store', $xtra_var); ?></th>
                                <th style="width:15%"><?php echo sort_table_icon($page_url, 'product', 'Product', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'category', 'Category', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'status', 'Status', $xtra_var); ?></th>
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
                                foreach ($data as $store_product):
                                    // BEGIN ACTION URL
                                    $action_str = '';
                                    if(in_array('edit', $permits) || in_array('delete', $permits)){
                                        $action_str = '<td>';
                                        $action_str .= in_array('edit', $permits) ? '<a href="#" id="status_'.$store_product->stpd_id.'_'.$store_product->pd_name.'_'.$store_product->st_name.'" class="btn btn-xs btn-info btn_update" data-toggle="modal" data-target="#status_modal">Change Status</a>' : '';
                                        $action_str .= in_array('delete', $permits) ? '&nbsp;<a href="#" id="delete_'.$store_product->pd_id.'_'.$store_product->pd_name.'_'.$store_product->st_name.'" class="btn btn-danger btn-xs btn_delete">Delete</a>' : '';
                                        $action_str .= '</td>';
                                    }
                                    // END ACTION URL

                                    if ($store_product->stpd_status === $cst_status['storepd_status']['active']) {
                                        $color = 'text-info';
                                    } elseif ($store_product->stpd_status === $cst_status['storepd_status']['out_of_stock']) {
                                        $color = 'text-warning';
                                    } else {
                                        $color = 'text-danger';
                                    }


                        ?>
                                <tr id="stpd_<?php echo $store_product->stpd_id; ?>">
                                    <td><?php echo $store_product->stpd_id; ?></td>
                                    <td><?php echo $store_product->st_name; ?></td>
                                    <td><?php echo $store_product->pd_name; ?></td>
                                    <td><?php echo $store_product->cat_name; ?></td>
                                    <td class="<?php echo $color; ?>"><strong><?php echo ucfirst(str_replace('_', ' ', $store_product->stpd_status)); ?></strong></td>
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
<div class="modal inmodal" id="status_modal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
    <div class="modal-content">
            <div class="modal-body">
                <div class="form-group">
                    <h5>Store</h5>
                    <div class="input-group date" style="float:left !important">
                        <input class="form-control" id="st_name" readonly value=""/>
                        <input type="hidden" id="stpd_id" value=""/>
                    </div>
                </div>
                <br/><br/>
                <div class="form-group">
                    <h5>Product</h5>
                    <div class="input-group date" style="float:left !important">
                        <input class="form-control" id="pd_name" readonly value=""/>
                    </div>
                </div>
                <br/><br/>
                <div class="form-group">
                    <h5>Status</h5>
                    <div class="input-group date" style="float:left !important">
                        <select name="stpd_status" id="stpd_status" class="form-control">
                        <?php foreach ($cst_status['status'] as $key => $status): ?>
                            <option value="<?php echo $key; ?>"><?php echo ucfirst(str_replace('_', ' ', $status)); ?></option>
                        <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="update_status">Change Status</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    // delete or update store product
    $('.btn_delete, .btn_update').click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/store_product_delete';
        var target = $(this).attr('id').split("_");
        var action = target[0];
        var store_product_id = target[1];
        var product_name = target[2];
        var store_name = target[3];
        var delete_row = $("#stpd_" + store_product_id);
        
        if (action === 'status') {
            $('#stpd_id').val(store_product_id);
            $('#st_name').val(store_name);
            $('#pd_name').val(product_name);
        } else {
            var confirm_action = confirm('Are you sure you want to delete Product "'+ product_name +'" from Store "'+store_name+'"?');
            
            if (confirm_action) {
                $("#loading").fadeIn();
                var ajax = $.post(url, {store_product_id: store_product_id},
                    function(data) {
                        $("#loading").fadeOut();
                        if (data === "Success") {
                            delete_row.fadeTo(400, 0, function () {
                                delete_row.remove();
                            });
                        } else {
                            alert(data);
                        }
                    }
                );
            }
            return false;
        }
    });

    // update store product status
    $("#update_status").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/store_product_status_update';
        var store_product_id = $("#stpd_id").val();
        var status = $("#stpd_status").val();

        $("#loading").fadeIn();
        var ajax = $.post(url, {store_product_id: store_product_id, store_product_status: status},
            function(data) {
                $("#loading").fadeOut();
                if (data == "Success") {
                    $('#status_modal').modal('toggle');
                    $('#stpd_status').prop('selectedIndex', 0);
                    location.reload();
                } else {
                    alert(data);
                }
            }
        );
    
        return false;
    });
});
</script>