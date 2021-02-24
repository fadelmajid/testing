<tr>
    <td bgcolor="#ffffff" style="padding: 0px 30px;">
        <table align="right" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td bgcolor="#ffffff" style="padding: 0px 18px; text-align: center;">
                    <img src="<?php echo UPLOAD_URL?>/email/asset_canceled.png" alt="cancel-admin" style="max-width: 85%; height: auto;">
                </td>
            </tr>
        </table>
    </td>
</tr>

<tr>
    <td bgcolor="#ffffff" style="padding: 20px 30px 30px 30px;">
        Hi <b><?php echo $user_name?></b> , <br>
        <br>
        Previously we apologize for the inconveniance. <br>
        We have reluctantly say that : <br>
        <br>
        <span style="color :black; font-weight: 400; font-size: 24px;">Your Subscription has been cancelled</span>
    </td>
</tr>

<tr>
    <td style="padding: 0 30px 0 30px; background-color: white;">
        <table align="center" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; padding: 0px">
            <tr>
                <td style="border-bottom: 2px solid #E5E5E5; width: 100%;" ></td>
            </tr>
        </table>
    </td>
</tr>

<tr>
    <td bgcolor="#ffffff" style="padding: 20px 30px 20px 30px;">
        <table align="right" cellpadding="0" cellspacing="0">
            <tr>
                <td rowspan="3" width="10%" align="left" style="padding: 0px 0px 0px 5px;">
                    <img src="<?php echo UPLOAD_URL?>/email/pole.png" alt="pole" style="max-width: 25%; height: auto; width: auto;"> <!--ini dikasih gambar-->
                </td>
                <td align="left" valign="top" style="font-size: 12px;">SUBSCRIPTION CANCELLED</td>
                <td align="right" valign="top" style="font-size: 12px;"><?php echo $created_date?></td>
            </tr>
            <tr>
                <td colspan="2" height="15px"></td>
            </tr>
            <tr>
                <td colspan="2" align="left" valign="top" style="font-size: 12px;">
                    Your Subscription <?php echo $subsorder_code?> has been cancelled by admin. Please check it now on <span style="color:#40AE4F">Alpha App</span>.
                    If you need help, contact us at <a href="mailto:<?php echo EMAIL_REPLY_TO?>" style="color : #40AE4F"><?php echo EMAIL_REPLY_TO?></a>
                    <br>
                    <br>
                    <?php 
                        if(!empty($subsorder_remarks) && isset($subsorder_remarks)){
                            echo 'Reason : '.$subsorder_remarks;
                        }
                    ?>
                </td>
            </tr>
        </table>
    </td>
</tr>

<tr>
    <td bgcolor="#ffffff" style="padding: 20px 20px 40px 20px;">
        <table cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td align="center">
                    <a href="https:///download-apps" style="
                    background-color: #40AE4F;
                    display: inline-block;
                    padding: 10px 90px;
                    text-align: center;
                    border-radius: 20px;
                    color: white;
                    font-weight: 600;
                    font-size: 16px;
                    text-decoration: none;">
                        GO TO APPS
                    </a>
                </td>
            </tr>
        </table>
    </td>
</tr>