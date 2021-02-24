<tr>
    <td bgcolor="#ffffff" style="padding: 0px 30px;">
        <table align="right" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td bgcolor="#ffffff" style="padding: 0px 18px; text-align: center;">
                    <img src="<?php echo UPLOAD_URL?>/email/asset_referral.png" alt="cancel-admin" style="max-width: 85%; height: auto;">
                </td>
            </tr>
        </table>
    </td>
</tr>

<tr>
    <td bgcolor="#ffffff" style="padding: 20px 30px 30px 30px;">
            Hi <b><?php echo $user_name?></b> ,<br><br>
            A friend has used your referral code so you get<br><br>
            <span style="color : #40AE4F; font-weight: 400; font-size: 32px;">1 Free Cup</span><br><br>
            <i>Don't forget to say thanks <b><?php echo $from?><b></i>
    </td>
</tr>

<tr>
    <td style="padding: 0 30px 0 30px; background-color: white;">
        <table align="center" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; padding: 0px">
            <tr>
                <td style="border-bottom: 2px dashed #E5E5E5; width: 100%;" >
                </td>
            </tr>
        </table>
    </td>
</tr>

<tr>
    <td bgcolor="#ffffff" style="padding: 20px 30px 20px 30px;">
        <table align="right" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td rowspan="4" width="10%" align="left" style="padding: 0px 0px 0px 5px;">
                    <img src="<?php echo UPLOAD_URL?>/email/pole.png" alt="pole" style="max-width: 25%; height: auto; width: auto;">
                </td>
                <td align="left" valign="top" style="font-size: 12px;">GET VOUCHER</td>
                <td align="right" valign="top" style="font-size: 12px;"><?php echo $created_date?></td>
            </tr>
            <tr>
                <td colspan="2" height="15px"></td>   
            </tr>
            <tr>
                <td colspan="2" align="left" valign="top">
                    You have received 1 Free Cup from <b><?php echo $from?></b>. Redeem it now on <a href="https:///download-apps"><span style="color:#40AE4F">Alpha App</span></a>.
                </td>
            </tr>
            <tr>
                <td colspan="2" align="left" valign="top" style="font-size: 12px;">
                    <i>Your voucher will valid until: <b><?php echo $exp_date?></b></i>
                </td>
            </tr>
        </table>
    </td>
</tr>

<tr>
    <td bgcolor="#ffffff" style="padding: 20px 20px 30px 20px;">
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
                        REDEEM NOW
                    </a>
                </td>
            </tr>
        </table>
    </td>
</tr>