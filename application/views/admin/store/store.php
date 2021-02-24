<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Store</h2>
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
                        <div class="form-group col-3">
                            <input type="text" name="search" placeholder="Search"value="<?php echo $search;?>" class="form-control col-12">
                        </div>
                        <div class="form-group col-4">
                            <div class="input-group date" id="search_date">
                                <input type="text" class="form-control" name="open" id="open_time" placeholder="Open time" value="<?php echo $open_time; ?>"/>
                                <span class="input-group-addon">to</span>
                                <input type="text" class="form-control" name="close" id="close_time" placeholder="Close time" value="<?php echo $close_time; ?>" />
                            </div>
                        </div>
                        <div class="form-group col-2">
                            <button type="submit" class="btn btn-default">
                                &nbsp;<i class="fa fa-search"></i>&nbsp;
                            </button>
                        </div>
                    <?php echo form_close(); ?>
                    <div class="hr-line-dashed"></div>
                    <?php 
                        if(!empty($st_without_pd)){
                            echo "<label><strong>List Store without Product</strong> :&nbsp</label>";
                            $res_arr = implode(', ',$st_without_pd);
                            echo $res_arr;
                        }

                        if(!empty($st_without_br)){
                            echo "<br><label><strong>List Store without Barista :&nbsp</strong></label>";
                            $arr = implode(', ',$st_without_br);
                            echo $arr;
                        }
                    ?>
                    <table class="table table-sticky table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo sort_table_icon($page_url, 'id', 'ID', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'name', 'Name', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'type', 'Type', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'concept', 'Concept', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'phone', 'Phone', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'latlong', 'Coordinat', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'open_time', 'Pickup Time', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'open_delivery_time', 'Delivery Time', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'is_visibility', 'Is Visibility', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'status', 'Status', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'courier', 'Courier', $xtra_var); ?></th>
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
                                foreach ($data as $store):
                                    // BEGIN ACTION URL
                                    $action_str = '';
                                    if(in_array('edit', $permits)){
                                        $edit_url = $current_url.'_add/'.$store->st_id;
                                        $action_str = '<td>';
                                        $action_str .= '<a href="'.$edit_url.'" class="btn btn-primary btn-sm">Edit</a>';
                                        $action_str .= '</td>';
                                    }
                                    // END ACTION URL
                                    
                                    if ($store->st_status === $cst_status['active']) {
                                        $color = 'text-info';
                                    } else {
                                        $color = 'text-danger';
                                    }
                        ?>
                                <tr>
                                    <td><?php echo $store->st_id; ?></td>
                                    <td><?php echo $store->st_name; ?></td>
                                    <td><?php echo ucfirst($store->st_type); ?></td>
                                    <td><?php echo ucfirst($store->st_concept); ?></td>
                                    <td><?php echo $store->st_phone; ?></td>
                                    <td><?php echo $store->st_lat.','.$store->st_long; ?></td>
                                    <td><?php echo $store->st_open.'-'.$store->st_close; ?></td>
                                    <td><?php echo $store->st_delivery_open.'-'.$store->st_delivery_close; ?></td>
                                    <td><?php echo ucfirst($store->is_visibility); ?></td>
                                    <td class="<?php echo $color; ?>"><strong><?php echo ucfirst($store->st_status); ?></strong></td>
                                    <td><?php echo nl2br($store->st_courier); ?></td>
                                    <?php echo $action_str; ?>
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
    // timepicker for search
    $('#open_time, #close_time').datetimepicker({
        datepicker:false,
        format:'H:i',
        lang:'en'
    });
});
</script>