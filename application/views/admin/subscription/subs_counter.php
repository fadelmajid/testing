<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Subscription Counter</h2>
    </div>
    <div class="col-4">
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
                        <div class="form-group col-5">
                            <div class="input-group date" id="search_date">
                                <input type="text" class="form-control" name="start" id="start_date" placeholder="Start date" value="<?php echo $start_date; ?>"/>
                                <span class="input-group-addon">to</span>
                                <input type="text" class="form-control" name="end" id="end_date" placeholder="End date" value="<?php echo $end_date; ?>" />
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
                                <th><?php echo sort_table_icon($page_url, 'id', 'Subs Order No.', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'user', 'User ID', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'subsdetail_id', 'Subs Detail ID', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'subsorder_id', 'Subs Order ID', $xtra_var);?></th>
                                <th><?php echo sort_table_icon($page_url, 'subsplan_name', 'Subs Plan Name', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'subsorder_qty', 'Qty', $xtra_var); ?></th>
                                <th>Subs Plan Promo</th>
                                <th><?php echo sort_table_icon($page_url, 'sc_status', 'Status', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'sc_total_counter', 'Subs Counter Total', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'sc_counter', 'Subs Counter', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'last_generate', 'Last Generate', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'created_date', 'Created', $xtra_var); ?></th>
                                <th><?php echo sort_table_icon($page_url, 'updated_date', 'Updated', $xtra_var); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($data)): ?>
                                <tr>
                                    <td class="error" colspan="100%">Data not found!</td>
                                </tr>
                        <?php else:
                                foreach ($data as $key => $subs_counter):
                                    $detail_url = $user_url.'_detail/'.$subs_counter->user_id;
                            ?>
                                <tr>
                                    <td>
                                        <?php echo $subs_counter->sc_id; ?><br><br>
                                    </td>
                                    <td>
                                        <?php echo '<a href="'.$detail_url.'">'.$subs_counter->user_id.'</a>'; ?><br><br>
                                    </td>
                                    <td>
                                        <?php echo $subs_counter->subsdetail_id; ?><br><br>
                                    </td>
                                    <td>
                                        <?php echo $subs_counter->subsorder_id; ?><br><br>
                                    </td>
                                    <td>
                                        <?php echo $subs_counter->subsplan_name; ?><br><br>
                                    </td>
                                    <td>
                                        <?php echo $subs_counter->subsorder_qty; ?><br><br>
                                    </td>
                                    <td>
                                        <?php
                                            $prm_rules      = json_decode($subs_counter->subsplan_promo, true);
                                            $disc_max       = $prm_rules['disc_max'] ? $prm_rules['disc_max'] : "-";
                                            $min_order      = $prm_rules['min_order'] ? $prm_rules['min_order'] : "-";
                                            $item_list      = $prm_rules['item_list'] ? implode(', ',$prm_rules['item_list']) : "-";
                                            $limit_usage    = $prm_rules['limit_usage'] ? $prm_rules['limit_usage'] : "-";
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
                                                            '<br> expired day : '.$prm_rules['expired_day'].
                                                            '<br> image : '.$prm_rules['image']
                                                        ;
                                            echo $rules;
                                        ?><br><br>
                                    </td>
                                    <td>
                                        <?php
                                            $status = $subscounter_status_name["$subs_counter->sc_status"];
                                            if ($subs_counter->sc_status === $subscounter_status['active']) {
                                                $color = 'text-info';
                                            } else {
                                                $color = 'text-danger';
                                            }

                                            echo '<span class="'.$color.'"><strong>'.$status.'</strong></span><br/>';
                                        ?><br><br>
                                    </td>
                                    <td>
                                        <?php echo $subs_counter->sc_total_counter; ?><br><br>
                                    </td>
                                    <td>
                                        <?php echo $subs_counter->sc_counter; ?><br><br>
                                    </td>
                                    <td>
                                        <?php echo show_date($subs_counter->last_generate); ?><br><br>
                                    </td>
                                    <td>
                                        <?php echo show_date($subs_counter->created_date); ?><br><br>
                                    </td>
                                    <td>
                                        <?php echo show_date($subs_counter->updated_date); ?><br><br>
                                    </td>
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
    //filter date
    $('#start_date, #end_date').datetimepicker({
        timepicker: false,
        format:'Y-m-d',
        lang:'en'
    });
});
</script>