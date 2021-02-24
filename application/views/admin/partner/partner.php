<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Partner List</h2>
    </div>
    <div class="col-4">
        <div class="title-action">
            <?php echo (in_array('add', $permits) ? '<a href="'.ADMIN_URL.'partner/partner_add" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;Create</a>' : '' );?>
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
                            <input type="text" name="search" placeholder="Search" value="<?php echo $search;?>" class="form-control col-12">
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
                                    <?php echo sort_table_icon($page_url, 'id', 'Partner ID', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'name', 'Partner Name', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'code', 'Partner Code', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'token', 'Token', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'desc', 'Deskripsi', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'created_by', 'Created By', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'created_date', 'Created Date', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'updated_by', 'Updated By', $xtra_var); ?>
                                </th>
                                <th>
                                    <?php echo sort_table_icon($page_url, 'updated_date', 'Updated Date', $xtra_var); ?>
                                </th>
                                <?php echo (in_array('edit', $permits) || in_array('delete', $permits) ? '<th>Action</th>' : '' );?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($all_data as $key => $value) {
                                // BEGIN ACTION URL
                                $action_str = '';
                                if(in_array('edit', $permits)){
                                    $edit_url = ADMIN_URL.'partner/partner_add/'.$value->ptr_id;
                                    $detail_url = ADMIN_URL.'partner/partner_promo/'.$value->ptr_id;
                                    $action_str = '<td>';
                                    $action_str .= '<a href="'.$edit_url.'" class="btn btn-primary btn-block btn-sm">Edit</a>';
                                    $action_str .= '<a href="'.$detail_url.'" id="trk_'. $value->ptr_id.'" class="btn btn-success btn-block btn-sm btn_track">Partner Promo</a>';
                                    $action_str .= '</td>';
                                }
                                // END ACTION URL
                                echo '
                                    <tr id="tr_'. $value->ptr_id .'">
                                        <td>'.$value->ptr_id.'</td> 
                                        <td>'.$value->ptr_name.'</td>
                                        <td>'.$value->ptr_code.'</td>
                                        <td>'.$value->ptr_token.'</td>
                                        <td>'.$value->ptr_desc.'</td>
                                        <td>'.$value->created_by.'</td>
                                        <td>'.show_date($value->created_date, true).'</td>
                                        <td>'.$value->updated_by.'</td>
                                        <td>'.show_date($value->updated_date, true).'</td>
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