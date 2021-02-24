<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Role</h2>
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
                    <?php echo form_open(ADMIN_URL.'setup/roles_add/'.($detail ? $detail->role_id : ''), array('id'=>'theform')); ?>
                        <div class="form-group">
                            <h5>Role Name</h5>
                            <input class="form-control" name="role_name" value="<?php echo set_value('role_name', ($detail ? $detail->role_name : ''));?>">
                            <?php echo form_error('role_name'); ?>
                        </div>
                        <div class="form-group">
                            <h5>Description</h5>
                            <textarea class="form-control" name="role_desc" ><?php echo set_value('role_desc', ($detail ? $detail->role_desc : ''));?></textarea>
                            <?php echo form_error('role_desc'); ?>
                        </div>
                        
                        
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
                                                <label class="checkbox-inline"><input type="checkbox" onclick="chkbox_checkall(\'matrix_'. $key .'_\', this);" />&nbsp;Check All</label>&nbsp;
                                                <label class="checkbox-inline"><input type="checkbox" onclick="chkbox_checkall(\'matrix_'. $key .'_view_\', this);" />&nbsp;All View</label>&nbsp;
                                                <label class="checkbox-inline"><input type="checkbox" onclick="chkbox_checkall(\'matrix_'. $key .'_add_\', this);" />&nbsp;All Add</label>&nbsp;
                                                <label class="checkbox-inline"><input type="checkbox" onclick="chkbox_checkall(\'matrix_'. $key .'_edit_\', this);" />&nbsp;All Edit</label>&nbsp;
                                                <label class="checkbox-inline"><input type="checkbox" onclick="chkbox_checkall(\'matrix_'. $key .'_delete_\', this);" />&nbsp;All Delete</label>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    ';
                                    
                                    //start looping checkbox
                                    foreach($value['submenu'] as $k=>$v){
                                        
                                        $roles_permit = array();
                                        if(isset($roles_menu[$v->submenu_id]))
                                            $roles_permit = explode(',', $roles_menu[$v->submenu_id]);
                                        
                                        
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
</script>