<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Products COGS</h2>
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
                                    <?php echo sort_table_icon($page_url, 'pdcogs_id', 'ID', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'pd_name', 'Product Name', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'pdcogs_price', 'COGS', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'cogs_date', 'Date', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'created', 'Created', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'updated', 'Updated', $xtra_var); ?>
                                </th>
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
                                foreach ($data as $product_cogs):
                                    // BEGIN ACTION URL
                                    $action_str = '';
                                    if(in_array('edit', $permits)){
                                        $edit_url       = $current_url.'_add/'.$product_cogs->pdcogs_id;
                                        $action_str     = '<td>';
                                        $action_str     .= in_array('edit', $permits) ? '<a href="'.$edit_url.'" class="btn btn-primary btn-block btn-xs">Edit</a>' : '';
                                        $action_str     .= in_array('delete', $permits) ? '<a href="#" id="id_'.$product_cogs->pdcogs_id.'_'.$product_cogs->pd_name.'" class="btn btn-xs btn-danger btn-block btn_delete">Delete</a>' : '';
                                        $action_str     .= '</td>';
                                    }
                                    // END ACTION URL

                        ?>
                                <tr>
                                    <td>
                                        <?php echo $product_cogs->pdcogs_id; ?>
                                    </td>
                                    <td>
                                        <?php echo $product_cogs->pd_name; ?>
                                    </td>
                                    <td class="text-right">
                                        <?php echo number_format($product_cogs->pdcogs_price, 0, ',', '.'); ?>
                                    </td>
                                    <td>
                                        <?php echo show_date($product_cogs->cogs_date); ?>
                                    </td>
                                    <td>
                                        <?php
                                            echo isset($arr_admin[$product_cogs->created_by]) ? $arr_admin[$product_cogs->created_by] : 'System';
                                            echo "<br/>".show_date($product_cogs->created_date);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            echo isset($arr_admin[$product_cogs->updated_by]) ? $arr_admin[$product_cogs->updated_by] : 'System';
                                            echo "<br/>".show_date($product_cogs->updated_date);
                                        ?>
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
    //delete data
    $(".btn_delete").click(function () {
        var url             = '<?php echo ADMIN_URL; ?>ajax/product_cogs_delete';
        var target          = $(this).attr('id').split("_");
        var pdcogs_id       = target[1];
        var pd_name         = target[2];
        var status          = $(this).text().toLowerCase();
        var confirm_action  = confirm('Are you sure you want to ' + status + ' Product COGS with product name"'+ pd_name +'"?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {pdcogs_id: pdcogs_id},
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