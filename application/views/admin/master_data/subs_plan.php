<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Subscription Plan</h2>
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
                                    <?php echo sort_table_icon($page_url, 'name', 'Plan Name', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'code', 'Plan Code', $xtra_var); ?>
                                </th>
                                <th>
                                    Subs Plan Image
                                </th>
                                <th>
                                    Subs Plan Image Detail
                                </th>
                                <th>
                                    Promo Rules
                                </th>
                                <th>
                                    Promo Image
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'subsplan_show', 'Show Hide Plan', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'price', 'Price', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'duration', 'Duration', $xtra_var); ?>
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
                        <?php if (empty($all_data)): ?>
                                <tr>
                                    <td class="error" colspan="100%">Data not found!</td>
                                </tr>
                        <?php
                            else:
                                foreach ($all_data as $subs_plan):
                                    // BEGIN ACTION URL
                                    $action_str = '';
                                    $action_str = '<td>';
                                    if(in_array('edit', $permits)){
                                        $edit_url = $current_url.'_add/'.$subs_plan->subsplan_id;
                                        $action_str .= in_array('edit', $permits) ? '<a href="'.$edit_url.'" class="btn btn-primary btn-block btn-xs">Edit</a>' : '';
                                        $action_str .= in_array('delete', $permits) ? '<a href="#" id="id_'. $subs_plan->subsplan_id .'_'.$subs_plan->subsplan_name.'" class="btn btn-danger btn-xs btn-block btn-delete">Delete</a>' : '';
                                    }
                                    $action_str .= '</td>';
                                    // END ACTION URL

                                    if ($subs_plan->subsplan_baseprice > 0) {
                                        $base_price = '<span class="text-danger"><s>'.number_format($subs_plan->subsplan_baseprice, 0, ',', '.').'</s></span>';
                                    } else {
                                        $base_price = '';
                                    }
                        ?>
                                <tr>
                                    <td>
                                        <?php echo $subs_plan->subsplan_id; ?>
                                    </td>
                                    <td>
                                        <?php echo $subs_plan->subsplan_name; ?>
                                    </td>
                                    <td>
                                        <?php echo $subs_plan->subsplan_code; echo'
                                    </td>
                                    <td>
                                        <img src="'.UPLOAD_URL.$subs_plan->subsplan_img.'" alt="" style="height: 50px; width: auto;">
                                    </td>
                                    <td>
                                        <img src="'.UPLOAD_URL.$subs_plan->subsplan_img_detail.'" alt="" style="height: 50px; width: auto;">';?>
                                    </td>
                                    <td>
                                        <?php
                                            $prm_rules      = json_decode($subs_plan->subsplan_promo, true);
                                            $disc_max       = isset($prm_rules['disc_max']) ? $prm_rules['disc_max'] : "-";
                                            $min_order      = isset($prm_rules['min_order']) ? $prm_rules['min_order'] : "-";
                                            $item_list      = isset($prm_rules['item_list']) ? implode(', ',$prm_rules['item_list']) : "-";
                                            $limit_usage    = isset($prm_rules['limit_usage']) ? $prm_rules['limit_usage'] : "-";
                                            $rules      =
                                                            'limit usage : '.$limit_usage.
                                                            '<br> custom function : '.$prm_rules['custom_function'].
                                                            '<br> disc type : '.$prm_rules['disc_type'].
                                                            '<br> disc nominal : '.$prm_rules['disc_nominal'].
                                                            '<br> disc max : '.$disc_max.
                                                            '<br> min order : '.$min_order.
                                                            '<br> delivery included : '.$prm_rules['delivery_included'].
                                                            '<br> free delivery : '.$prm_rules['free_delivery'].
                                                            '<br> item type : '.$prm_rules['item_type'].
                                                            '<br> item list : '.$item_list.
                                                            '<br> expired day : '.$prm_rules['expired_day']
                                                        ;
                                            echo $rules;
                                            echo'
                                    </td>
                                    <td>';
                                        if(isset($prm_rules['image'])){
                                            echo'<img src="'.UPLOAD_URL.$prm_rules['image'].'" alt="" style="height: 50px; width: auto;">';
                                        }else{
                                            echo "-";
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo ucfirst($subs_plan->subsplan_show); ?>
                                    </td>
                                    <td class="text-right">
                                        <?php echo number_format($subs_plan->subsplan_finalprice, 0, ',', '.'); ?><br>
                                        <?php echo $base_price; ?>
                                    </td>
                                    <td>
                                        <?php echo $subs_plan->subsplan_duration; ?>
                                    <td>
                                        <?php
                                            echo isset($arr_admin[$subs_plan->created_by]) ? $arr_admin[$subs_plan->created_by] : 'System';
                                            echo "<br/>".show_date($subs_plan->created_date);
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                            echo isset($arr_admin[$subs_plan->updated_by]) ? $arr_admin[$subs_plan->updated_by] : 'System';
                                            echo "<br/>".show_date($subs_plan->updated_date);
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
    //untuk kebutuhan delete article
    $(".btn-delete").click(function () {
        var url             = '<?php echo ADMIN_URL; ?>ajax/subs_plan_delete';
        var target          = $(this).attr('id').split("_");
        var subsplan_id     = target[1];
        var subsplan_name   = target[2];
        var status          = $(this).text().toLowerCase();
        var confirm_action  = confirm('Are you sure you want to ' + status + ' subs plan name "'+ subsplan_name +'"?');

        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax        = $.post(url, {subsplan_id: subsplan_id},
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