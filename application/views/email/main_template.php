<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <style>
        *{
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            color: black;
        }
    </style>
</head>
<body style="background-color: #E9E9EB;">
    
    <table align="center" cellpadding="0" cellspacing="0" height=auto style="padding-top: 20px; max-width: 600px; min-width: 320px; "> 
        <tr>
            <td>
                <table align="center" cellpadding="0" cellspacing="0" height=auto style="border-collapse: collapse; border-radius: 20px; overflow: hidden; max-width: 600px; min-width: 320px; background-color: white;">
                    <tr>
                        <td>
                            <table align="center" cellpadding="0" cellspacing="0" bgcolor="white">
                                    <tr>
                                        <td bgcolor="#ffffff" style="padding: 20px 0px 10px 30px;">
                                            <table   cellpadding="0" cellspacing="0" width="100%">
                                                <tr>

                                                    <td width="50%">
                                                        <img src="<?php echo UPLOAD_URL.'email/logo-horizontal.png'?>" alt="alpha" style="max-width: 50%; height: auto; width: auto;">
                                                    </td>
                                                    <td width="50%" align="right" valign="right" style="">
                                                        <?php
                                                            if($status == 'cancelled'){
                                                                echo '<img src="'.UPLOAD_URL.'email/label_canceled.png" alt="canceled" style="max-width: 65%; height: auto; width: auto;">';
                                                            }elseif($status == 'completed'){
                                                                echo '<img src="'.UPLOAD_URL.'email/label_success.png" alt="completed" style="max-width: 65%; height: auto; width: auto;">';
                                                            }elseif($status == 'referral'){
                                                                echo '<img src="'.UPLOAD_URL.'email/label_referral.png" alt="completed" style="max-width: 65%; height: auto; width: auto;">';
                                                            }elseif($status == 'birthday'){
                                                                echo '<img src="'.UPLOAD_URL.'email/label_birthday.png" alt="completed" style="max-width: 65%; height: auto; width: auto;">';
                                                            }else{
                                                                echo '';
                                                            }
                                                        ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                            </table>
                            <table align="center" cellpadding="0" cellspacing="0" width="100%" bgcolor="white"> 
                                <?php echo $content;?>   
                            </table>
                        </td>
                    </tr> 
                </table>
                <?php
                    if($status == 'completed'){
                        echo '<table align="center" cellpadding="0" cellspacing="0" height=auto style="margin-top:20px; border-collapse: collapse; border-radius: 20px; overflow: hidden; max-width: 600px; min-width: 320px; background-color: white;">
                                    <tr>
                                        <td>
                                            <a href="https:///download-apps"><img src="'.UPLOAD_URL.'email/reffer-190410.jpg" alt="pin-img" style="max-width: 100%; height: auto; width: auto;"></a>
                                        </td>
                                    </tr>
                                </table>';
                    }
                ?>

                <table align="right" cellpadding="0" cellspacing="0" height=auto style="border-collapse: collapse; overflow: hidden; max-width: 600px;">
                    <tr>
                        <td style="padding: 10px;">
                            <table cellpadding="0" cellspacing="0" min-width="100%">
                                <tr>
                                    <td>
                                        Need help? Contact at : <a href="mailto:<?php echo EMAIL_REPLY_TO?>" style="color:green"><?php echo EMAIL_REPLY_TO?></a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>