<!DOCTYPE html>
<html lang="en">
 
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo CONTACT_COMPANY.' :: Login';?></title>
    <link rel="icon" type="image/ico" href='<?php echo ASSETS_URL;?>images/alpha-icon.png' />
    <!-- FONT AWESOME STYLES-->
    <link rel="stylesheet" href="<?php echo ADMIN_TEMPLATE_URL;?>font-awesome/css/font-awesome.css">
    <!-- BOOTSTRAP STYLES-->
    <link rel="stylesheet" href="<?php echo ADMIN_TEMPLATE_URL;?>css/bootstrap.min.css">
    <!-- ANIMATE STYLES-->
    <link rel="stylesheet" href="<?php echo ADMIN_TEMPLATE_URL;?>css/animate.css">
    <!-- TEMPLATE STYLES-->
    <link rel="stylesheet" href="<?php echo ADMIN_TEMPLATE_URL;?>css/style.css">
</head>

<body class="gray-bg">

    <div class="middle-box text-center loginscreen animated fadeInDown">
        <div>
            <div>
                <h1 class="logo-name"></h1>
            </div>
            <h3>Welcome to <?php echo CONTACT_COMPANY;?></h3>
            <p>Login in. To see it in action.</p>
            <?php echo form_open(ADMIN_URL.'login', array("role"=>"form", "class"=>"m-t" )); ?>
                <div class="form-group">
                    <input name="username" type="text" placeholder="Enter username" autocomplete="off" required class="form-control">
                    <?php echo form_error('username'); ?>
                </div>
                <div class="form-group">
                    <input name="password" type="password" placeholder="Password" required class="form-control">
                    <?php echo form_error('password'); ?>
                </div>
                <button type="submit" class="btn btn-primary block full-width m-b">Login</button>
            <?php echo form_close(); ?>
            <p class="m-t">
                <span>&copy;</span>
                <span>2021</span>
                <span>-</span>
                <span><?php echo CONTACT_COMPANY;?></span>
            </p>
        </div>
    </div>

    <!-- JQUERY-->
    <script src="<?php echo ADMIN_TEMPLATE_URL;?>js/jquery-3.1.1.min.js"></script>
    <!-- POPPER-->
    <script src="<?php echo ADMIN_TEMPLATE_URL;?>js/popper.min.js"></script>
    <!-- BOOTSTRAP-->
    <script src="<?php echo ADMIN_TEMPLATE_URL;?>js/bootstrap.js"></script>
</body>

</html>