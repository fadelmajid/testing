<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?> 

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-8">
        <h2>Roles</h2>
    </div>
    <div class="col-4">
        <div class="title-action">
            <?php echo ( in_array('add', $permits) ? '<a href="'. ADMIN_URL .'setup/roles_add" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;Create</a>' : '' );?>
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
                            <button type="submit" class="btn btn-default">&nbsp;<i class="fa fa-search"></i>&nbsp;</button>
                        </div>
                    <?php echo form_close(); ?>
                    <div class="hr-line-dashed"></div>
                    <table class="table table-sticky table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo sort_table_icon($page_url, 'id', 'ID', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'name', 'Name', $xtravar);?></th>
                                <th><?php echo sort_table_icon($page_url, 'desc', 'Desc', $xtravar);?></th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($all_data as $key => $value){
                                    
                                    $url_edit = ADMIN_URL .'setup/roles_add/'.$value->role_id;
                                    
                                    echo '
                                        <tr>
                                            <td>'. $value->role_id .'</td>
                                            <td>'. $value->role_name .'</td>
                                            <td>'. $value->role_desc .'</td>
                                            <td>
                                                '. ( in_array('edit', $permits) ? '<a href="'. $url_edit .'" class="btn btn-primary btn-sm">Edit</a>' : '' ) .'
                                                '. ( in_array('copy_to_all', $permits) ? '<a href="#" id="id_'.$value->role_id.'_'.$value->role_name.'" class="btn btn-info btn-sm">Copy Permission For All User</a>' : '' ) .'
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

    $(".btn-info").click(function () {
        var url = '<?php echo ADMIN_URL; ?>ajax/copy_permission_to_all_user';
        var target = $(this).attr('id').split("_");
        var role_id = target[1];
        var role_name = target[2];
        var confirm_action = confirm('Are you sure you want to copy Role "'+ role_name +'" to All User?');
        if (confirm_action) {
            $("#loading").fadeIn();
            var ajax = $.post(url, {role_id: role_id},
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
</script>
