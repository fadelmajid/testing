<tr>
    <td colspan="2" bgcolor="#ffffff" style="padding: 0px 30px;">
        <table align="right" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td bgcolor="#ffffff" style="padding: 0px 18px; text-align: center;">
                    <img src="<?php echo UPLOAD_URL?>/email/asset_success.png" alt="completed" style="max-width: 85%; height: auto;">
                </td>
            </tr>
        </table>
    </td>
</tr>

<tr>
    <td colspan="2" style="padding: 10px 30px 10px 30px;">
        <table cellpadding="0" cellspacing="0" width="100%"">
            <tr>
                <td  style="color: black;">
                    Hi <b style="color: black;"><?php echo $user_name?>,</b>
                    <br>&nbsp;
                </td>
            </tr>
            <tr>
                <td  style="color: black;">
                    Thank you for your order. Enjoy your drink and have a nice day!
                    <br>&nbsp;
                </td>
            </tr>
            <tr>
                <td style="color: #989898; font-size: 10px;">
                    Amount Paid
                    <br>&nbsp;
                </td>
            </tr>
            <tr>
                <td  style="color: black; font-size: 24px;">
                    <?php echo 'Rp '.number_format($uor_total, 0, ',', '.')?>
                    <br>&nbsp;
                </td>
            </tr>
            <tr>
                <td>
                    <table align="center" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; padding: 0px">
                        <tr>
                            <td style="border-bottom: 1px dashed #cdcdda; height: 1px;" >
                                
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr>
    <td style="padding-left: 30px; padding-top: 8px; font-size: 12px;"><span style="border: 1px solid #40AE4F; border-radius: 25px; padding: 5px 8px;">Order Details</span></td>
    <td align="right" style="padding-right: 30px; padding-top: 8px; color: black; font-size: 16px;">ID Order: <?php echo $uor_code?></td>
</tr>
<tr>
    <td colspan="2" style="padding: 20px 30px 10px 30px;">
        <table  cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td colspan="2"><b  style="color: #989898;"><?php echo $pin_title?></b></td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 0px 0px 10px 0px;">
                    <table   align="center" cellpadding="0" cellspacing="0" width="100%" style="padding-bottom: 5px;">
                        <tr>
                            <td style="border-bottom: 1px dashed #F0F0F0; height: 5px;" >
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td rowspan="2" align="center" valign="top" width="10%">
                    <?php echo '<img src="'.$pin_img.'" alt="pin-img" style="max-width: 100%; height: auto; width: auto;">';?>
                </td>
                <td style="padding-left: 10px;"><b style="font-size: 18px; color: #989898;">
                    <?php echo $pin_name;?>
                </td>
            </tr>
            <tr>
                <td style="padding-left: 10px; color: #989898;"><?php echo $pin_desc; ?></td>
            </tr>
        </table>
    </td>
</tr>
<tr>
    <td colspan="2" style="padding: 10px 30px 10px 30px;">
        <table   cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td>
                    <b style="color: #989898;">Items to order<b>
                </td>
            </tr>
            <tr>
                <td>
                    <table   align="center" cellpadding="0" cellspacing="0" width="100%" style="padding-bottom: 5px;">
                        <tr>
                            <td style="border-bottom: 1px dashed #F0F0F0; height: 5px; " >
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="color: #989898;">
                    <?php
                        $product = '';
                        for($i=0;$i<count($uor_product);$i++){
                            if($uor_product[$i]->uorpd_is_free == 0){
                                $product .= $uor_product[$i]->uorpd_name.' - '.$uor_product[$i]->uorpd_qty.' | ';
                            }
                        }
                        if($product == ''){
                            echo '-';
                        }else{
                            echo substr($product, 0, -2);
                        }
                    ?>
                    <br>&nbsp;
                </td>
            </tr>
            <tr>
                <td>
                    <b style="color: #989898;">Free Coffee</b>
                </td>
            </tr>
            <tr>
                <td>
                    <table   align="center" cellpadding="0" cellspacing="0" width="100%" style="padding-bottom: 5px;">
                        <tr>
                            <td style="border-bottom: 1px dashed #F0F0F0; height: 5px; " >
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="color: #989898;">                    
                    <?php
                        $free_product = '';
                        for($i=0;$i<count($uor_product);$i++){
                            if($uor_product[$i]->uorpd_is_free == 1){
                                $free_product .= $uor_product[$i]->uorpd_name.' - '.$uor_product[$i]->uorpd_qty.' | ';
                            }
                        }
                        if($free_product == ''){
                            echo '-';
                        }else{
                            echo substr($free_product, 0, -2);
                        }
                    ?>
                    <br>&nbsp;
                </td>
            </tr>
        </table>
    </td>
</tr>
<tr>
    <td colspan="2" style="padding: 10px 30px 10px 30px;">
        <table cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td colspan="2" style="color: #989898;">Payment Details</td>
            </tr>
            <tr>
                <td colspan="2">
                    <table   align="center" cellpadding="0" cellspacing="0" width="100%" style="padding-bottom: 5px;">
                        <tr>
                            <td style="border-bottom: 1px dashed #F0F0F0; height: 5px; " >
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="color: #989898;">Price</td>
                <td align="right" style="color: #989898;"><?php echo 'Rp '.number_format($uor_subtotal, 0,',', '.')?></td>
            </tr>
            <tr>
                <td style="color: #989898;">Delivery Fee</td>
                <td align="right" style="color: #989898;"><?php echo 'Rp '.number_format($uor_delivery_fee, 0, ',', '.')?></td>
            </tr>
            <tr>
                <td style="color: #989898;">Discount</td>
                <td align="right" style="color: #989898;"><?php echo 'Rp '.number_format($uor_discount, 0, ',', '.')?></td>
            </tr>
            <tr>
                <td colspan="2">
                    &nbsp;
                </td>
            </tr>
            <tr>
                <td><b style="color: #40AE4F; font-size: 18px;">TOTAL</b></td>
                <td align="right"><b style="color: #40AE4F; font-size: 18px;"><?php echo 'Rp '.number_format($uor_total, 0,',', '.')?></b></td>
            </tr>
        </table>
    </td>
</tr>
<tr>
    <td colspan="2" align="center" style="padding: 20px 20px 30px 20px;">
        <a href="https:///download-apps" style="
        background-color: #40AE4F;
        display: inline-block;
        padding: 10px 50px;
        text-align: center;
        border-radius: 35px;
        color: white;
        font-weight: 600;
        font-size: 16px;
        text-decoration: none;">
            GO TO APPS
        </a>
    </td>
</tr>