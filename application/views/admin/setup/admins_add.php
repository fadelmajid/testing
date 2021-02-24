<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Admin</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
            <?php echo $msg; ?>
            <?php
                if($show_form){
            ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open(ADMIN_URL.'setup/admins_add/'.($detail ? $detail->admin_id : ''), array('id'=>'theform')); ?>
                        <input type="hidden" name="admin_id" value="<?php echo ($detail ? $detail->admin_id : '');?>" />
                        <div class="form-group">
                            <h5>Role</h5>
                            <select class="form-control" name="role_id" id="role_id">
                                <?php
                                    $sel = set_value('role_id', ($detail ? $detail->role_id : '' ));
                                    foreach($all_roles as $key => $val){
                                        echo '<option value="'. $val->role_id .'" '. ($sel == $val->role_id ? 'selected' : '') .'>'. $val->role_name .'</option>';
                                    }
                                ?>
                            </select>
                            <?php echo form_error('role_id'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Username</h5>
                            <input class="form-control" name="admin_username" value="<?php echo set_value('admin_username', ($detail ? $detail->admin_username : ''));?>">
                            <?php echo form_error('admin_username'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Fullname</h5>
                            <input class="form-control" name="admin_fullname" value="<?php echo set_value('admin_fullname', ($detail ? $detail->admin_fullname : ''));?>">
                            <?php echo form_error('admin_fullname'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Email</h5>
                            <input class="form-control" name="admin_email" type="email" value="<?php echo set_value('admin_email', ($detail ? $detail->admin_email : ''));?>">
                            <?php echo form_error('admin_email'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Password</h5>
                            <input class="form-control" name="admin_password" type="password">
                            <?php echo form_error('admin_password'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Allow Login</h5>
                            <?php
                                $sel = set_value('admin_allowlogin', ($detail ? $detail->admin_allowlogin : '' ));
                            ?>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="admin_allowlogin" id="admin_allowlogin1" value="1" <?php echo ($sel == '1' ? 'checked' : '');?>> &nbsp; Yes
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="admin_allowlogin" id="admin_allowlogin0" value="0" <?php echo ($sel == '0' ? 'checked' : '');?>> &nbsp; No
                                </label>
                            </div>
                            <?php echo form_error('admin_allowlogin'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Store Permits</h5>
                            <div class="input-group select" id="select_store">
                                <select name="st_id" class="form-control m-b" id="select_store">
                                    <option value="0">All Store</option>
                                <?php
                                    foreach($store_data as $stores) {
                                        $st_id = set_value('st_id', ($detail ? $detail->st_id : ''));
                                        echo '<option value="'. $stores->st_id .'" '. ($stores->st_id == $st_id ? "selected" : "").'>'. $stores->st_name .'</option>';
                                    }
                                ?>
                                </select>
                            </div>
                        </div>
                        <?php
                            if( ! empty($detail)){
                        ?>
                        
                            <div class="form-group">
                                <h5>Last Login</h5>
                                <p class="form-control-static"><?php echo ($detail ? $detail->admin_lastlogin : '');?></p>
                            </div>
                            <div class="form-group">
                                <h5>Created By</h5>
                                <p class="form-control-static"><?php echo ($detail ? $arr_admin[ $detail->created_by ]: '');?></p>
                            </div>
                            <div class="form-group">
                                <h5>Created Date</h5>
                                <p class="form-control-static"><?php echo ($detail ? $detail->created_date : '');?></p>
                            </div>
                            
                        <?php
                            }
                        ?>
                        <a href="#" onclick="javascript:ajax_get_role_matrix($('#role_id').val());return false;" class="btn btn-info">Copy Permission From Role</a>
                        <table class="table table-hover">
                            
                            <?php
                                foreach($list_matrix as $key=>$value){
                                    //kasih jarak.
                                    if($key>0){echo '<tr><td colspan="100%">&nbsp;</td></tr>';}
                                    
                                    //echo header menu
                                    echo '
                                    <thead>
                                        <tr>
                                            <th>'. $value['menu_name'] .'</th>
                                            <th colspan="100%">
                                                <label class="checkbox-inline"><input type="checkbox" onclick="chkbox_checkall(\'matrix_'. $key .'_\', this);" />&nbspCheck All</label>&nbsp;
                                                <label class="checkbox-inline"><input type="checkbox" onclick="chkbox_checkall(\'matrix_'. $key .'_view_\', this);" />&nbspAll View</label>&nbsp;
                                                <label class="checkbox-inline"><input type="checkbox" onclick="chkbox_checkall(\'matrix_'. $key .'_add_\', this);" />&nbspAll Add</label>&nbsp;
                                                <label class="checkbox-inline"><input type="checkbox" onclick="chkbox_checkall(\'matrix_'. $key .'_edit_\', this);" />&nbspAll Edit</label>&nbsp;
                                                <label class="checkbox-inline"><input type="checkbox" onclick="chkbox_checkall(\'matrix_'. $key .'_delete_\', this);" />&nbspAll Delete</label>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    ';
                                    
                                    //start looping checkbox
                                    foreach($value['submenu'] as $k=>$v){
                                        
                                        $roles_permit = array();
                                        if(isset($admin_menu[$v->submenu_id]))
                                            $roles_permit = explode(',', $admin_menu[$v->submenu_id]);
                                        
                                        
                                        //bikin checkbox
                                        $arr_permits = explode(',', $v->submenu_permits);
                                        $str_checkbox='';
                                        for($i=0;$i<count($arr_permits);$i++){
                                            $permit = trim($arr_permits[$i]);
                                            if($permit != ''){
                                                $sel = (in_array($permit, $roles_permit) ? 'checked' : '');
                                                $str_checkbox .= '<label class="pointer"><input type="checkbox" class="jqchange" name="matrix_permits['. $v->submenu_id .']['. $permit .']" id="matrix_'. $key .'_'. $permit .'_'. $v->submenu_id .'" value="'. $permit .'" '. $sel .' />&nbsp'. $permit .'</label>&nbsp;';
                                                
                                            }    
                                        }
                                        //bikin checkbox 
                                        
                                        echo '  <tr>
                                                    <td width="200"><b>'. $v->submenu_name .'</b></td>
                                                    <td>'. $str_checkbox .'</td>
                                                </tr>';  
                                    }
                                    echo '</tbody>';
                                }
                                
                            ?>
                        </table>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary"><?php echo $title_form;?></button>
                            <button type="reset" class="btn btn-warning">Reset</button>
                        </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
            <?php
                }
            ?>
        </div>
    </div>
</div>
<!-- /.content-wrapper -->



<script>
    /*
      script untuk detect onchange.
      kalau checkbox add, di centang, sebaiknya checkbox view juga di centang.
      tapi tidak semua permits kalau ada (add/edit/delete) harus ada view, jadi tidak boleh di tambahkan ke script php function ini.
    */
    $('.jqchange').change(function() {
        var targetid = $(this).attr('id');
        var replace_from=new Array("add", "edit", "delete");
        var found = targetid.indexOf("view");
        
        //kalau bukan checkbox view baru eksekusi ini.
        if(found < 0){
            for (i=0;i<replace_from.length;i++)
                targetid = targetid.replace(replace_from[i], "view");
            
            //check dulu apakah checkbox view ada atau tidak.
            if (typeof $('#'+targetid) !== 'undefined'
                && $(this).prop('checked') == true) {
                $('#'+targetid).prop('checked', $(this).prop('checked') );
            }
        }
    });
    
    function ajax_get_role_matrix(role_id){
        
        $.ajax({
            url: '<?php echo ADMIN_URL;?>setup/ajax_get_role_matrix/'+ role_id,
            type: 'GET',
            dataType: 'json', // added data type
            success: function(res) {
                
                
                $("input[id^=matrix_]").each(function() {
                    $(this).prop('checked', false);
                });
                
                
                $.each(res, function(index, value) {
                    
                    var permits = value.split(',');
                    $.each(permits, function(key, prmit) {
                        
                        $("input[id$=_"+ prmit +"_"+index+"]").each(function() {
                            $(this).prop('checked', true);
                        });
                        
                    });
                });
                //matrix_permits['. $v['submenu_id'] .']['. $permit .']
            }
        });
        
    }
    
    
</script>
