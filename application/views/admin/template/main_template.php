<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
    <title><?php echo CONTACT_COMPANY.($meta_title != '' ? ' :: '.$meta_title : '');?></title>
    <?php ($meta_desc != '' ? '<meta name="description" content="'. $meta_desc .'">' : '');?>
    
    <link rel="icon" type="image/ico" href='<?php echo ASSETS_URL;?>images/alpha-icon.png' />

    <!-- Admin CSS -->
    <link href="<?php echo ASSETS_URL;?>css/styles-adm.css" rel="stylesheet">
    <!-- FONT AWESOME STYLES-->
    <link rel="stylesheet" href="<?php echo ADMIN_TEMPLATE_URL;?>font-awesome/css/font-awesome.css">
    <!-- BOOTSTRAP STYLES-->
    <link rel="stylesheet" href="<?php echo ADMIN_TEMPLATE_URL;?>css/bootstrap.min.css">
    <!-- ANIMATE STYLES-->
    <link rel="stylesheet" href="<?php echo ADMIN_TEMPLATE_URL;?>css/animate.css">
    <!-- TEMPLATE STYLES-->
    <link rel="stylesheet" href="<?php echo ADMIN_TEMPLATE_URL;?>css/style.css">
    <!-- TEMPLATE UI JQUERY-->
    <link rel="stylesheet" href="<?php echo ADMIN_TEMPLATE_URL;?>css/jquery-ui.min.css">
    <!-- TEMPLATE UI CHOSEN-->
    <link rel="stylesheet" href="<?php echo ADMIN_TEMPLATE_URL;?>css/plugins/chosen/bootstrap-chosen.css">
    <!-- TEMPLATE UI SELECT-->
    <link rel="stylesheet" href="<?php echo ADMIN_TEMPLATE_URL;?>css/plugins/select2/select2.min.css">
    
    <!-- DATETIMEPICKER-->
    <link rel="stylesheet" href="<?php echo ADMIN_TEMPLATE_URL;?>vendor/jqdatetimepicker/jquery.datetimepicker.min.css">
    
    <!-- Mainly scripts -->
    <script src="<?php echo ADMIN_TEMPLATE_URL;?>js/jquery-3.1.1.min.js"></script>

    <!-- JQUERY-->
    <script src="<?php echo ASSETS_URL;?>js/sticky-table.js"></script>
    <script>
        $(document).ready(function() {
            jQuery.fn.clear = function(){
                var $form = $(this);

                $form.find('input:text, input:password, input:file, textarea').val('');
                $form.find('select option:selected').removeAttr('selected');
                $form.find('input:checkbox, input:radio').removeAttr('checked');

                return this;
            };
        });

        function clearform(form){
            $(document).ready(function() {
                $(form).clear();
            });
        }
        
        function chkbox_checkall(idprefix, source){
            $("input[id^="+ idprefix +"]").each(function() {
                $(this).prop('checked', source.checked);
                $(this).change();//fire change, supaya kalau ada script 'onchange=" bisa kedetect.
            });
        }

        function convertToRupiah(angka)
        {
            var rupiah = '';		
            var angkarev = angka.toString().split('').reverse().join('');
            for(var i = 0; i < angkarev.length; i++) if(i%3 == 0) rupiah += angkarev.substr(i,3)+'.';
            return 'Rp. '+rupiah.split('',rupiah.length-1).reverse().join('');
        }

    </script>
</head>

<body>
    <div id="loading"><img src="<?php echo ASSETS_URL ?>images/loading.gif" width="50%"/></div>

    <div id="wrapper">
        <nav class="navbar-default navbar-static-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav metismenu" id="side-menu">
                    <li class="nav-header">
                        <div class="dropdown profile-element">
                            <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                                <span class="block m-t-xs font-bold">Content Management Sys</span>
                                <span class="text-muted text-xs block font-bold"><?php echo $this->session->userdata('adm_name');?> <b class="caret"></b></span>
                            </a>
                            <ul class="dropdown-menu animated fadeInRight m-t-xs">
                                <li><a class="dropdown-item" href="<?php echo ADMIN_URL.'dashboard';?>">Dashboard</a></li>
                                <li><a class="dropdown-item" href="<?php echo ADMIN_URL.'profile';?>">Profile</a></li>
                                <li><a class="dropdown-item" href="<?php echo ADMIN_URL.'profile/password';?>">Change Password</a></li>
                                <li class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo ADMIN_URL.'login/clean';?>">Logout</a></li>
                            </ul>
                        </div>
                        <div class="logo-element">
                            CMS
                        </div>
                    </li>
                    <?php $this->view(ADMIN_MENU_FOLDER .'_menu_'.$this->session->userdata('adm_id').'.tpl'); ?>
                </ul>

            </div>
        </nav>

        <div id="page-wrapper" class="gray-bg">
            <div class="row border-bottom">
                <nav class="navbar navbar-static-top  " role="navigation" style="margin-bottom: 0">
                    <div class="navbar-header">
                        <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
                    </div>
                    <ul class="nav navbar-top-links navbar-right">
                        <li>
                            <span class="m-r-sm text-muted welcome-message"><!-- Welcome to INSPINIA+ Admin Theme. --></span>
                        </li>    
                        <li>
                            <a href="<?php echo ADMIN_URL.'login/clean';?>">
                                <i class="fa fa-sign-out"></i> Log out
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
            
            <?php echo $content;?>
            
            <div class="footer">
                <div class="float-right">
                    <?php echo  (ENVIRONMENT === 'development') ?  'CodeIgniter Version <strong>' . CI_VERSION . '</strong>. Server Time '.date("Y-m-d H:i:s e") : '' ?>
                </div>
                <div>
                    &copy; 2021 - <?php echo CONTACT_COMPANY;?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mainly scripts -->
    <script src="<?php echo ADMIN_TEMPLATE_URL;?>js/popper.min.js"></script>
    <script src="<?php echo ADMIN_TEMPLATE_URL;?>js/bootstrap.js"></script>
    <script src="<?php echo ADMIN_TEMPLATE_URL;?>js/plugins/metisMenu/jquery.metisMenu.js"></script>
    <script src="<?php echo ADMIN_TEMPLATE_URL;?>js/plugins/slimscroll/jquery.slimscroll.min.js"></script>

    <!-- Custom and plugin javascript -->
    <script src="<?php echo ADMIN_TEMPLATE_URL;?>js/inspinia.js"></script>
    <script src="<?php echo ADMIN_TEMPLATE_URL;?>js/plugins/pace/pace.min.js"></script>
    <script src="<?php echo ADMIN_TEMPLATE_URL;?>vendor/jqdatetimepicker/jquery.datetimepicker.full.min.js"></script>
    <script src="<?php echo ADMIN_TEMPLATE_URL;?>js/plugins/typehead/bootstrap3-typeahead.min.js"></script>

   <script>
        
        $(document).ready(function() {
            var idprefix = 'nav_submenuid_<?php echo $this->session->userdata('submenu_id');?>_';
            
            $("a[id^="+ idprefix +"]").each(function() {
                var target 	= $(this).attr("id").split("_");
                var menuid	= target[3];
                
                if ($(this).parent().attr("class") != 'active') {
                    $("#nav_menuid_"+ menuid).addClass("active");
                    $("#anav_menuid_"+ menuid).attr("aria-expanded", "true");
                    $("#ulnav_menuid_"+ menuid).attr("aria-expanded", "true");
                    $("#ulnav_menuid_"+ menuid).addClass("in");
                    $(this).parent().addClass("active");
                }
                
            });
            
            sticky_table();
        });

    </script>
</body>

</html>