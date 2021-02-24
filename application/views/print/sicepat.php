<!DOCTYPE html>
<html>
<head>
    <style type="text/css" media="print">
        @page {
            size: auto;   /* auto is the initial value */
            margin: 0;  /* this affects the margin in the printer settings */
            
        }
    </style>
</head>
<body>
<div>
        <img style="padding-left:20px; width: 215px;" src="<?php echo ASSETS_URL; ?>images/logo.png">
</div>
<div style="padding-left:55px;"><strong>DELIVERY ORDER</strong></div><br>
<table>
    <tr>
        <td>#<?php echo $order_data->uor_code; ?> <span style="padding-left: 20px; float: right"><?php echo date('d M Y') ?></span></td>
    </tr>
    <tr>
        <td><?php  echo $order_data->uadd_person ?><span style="padding-left: 110px; float: right"><?php echo date('H:i') ?></td>
    </tr>
    <tr>
        <td><?php echo $order_data->uadd_phone ?></td>
    </tr>
    <tr>
        <td><br><strong>Address</strong></td>
    </tr>
    <tr>
        <td><?php echo nl2br($order_data->uadd_street); ?></td>
    </tr>
    <?php 
    if(!empty($order_data->uadd_notes)) { ?>
    <tr>
        <td><strong>Delivery Notes</strong></td>
    </tr>
    <tr>
        <td><?php echo nl2br($order_data->uadd_notes); ?> </td>
    </tr>
    <?php } ?>
    <tr>
        <td><strong>Items</strong></td>
    </tr>
    <tr>
        <td><?php
                foreach($order_product as $product_key => $product){
                    echo $product->pd_qty ." ". $product->uorpd_name .'<br />';
                } ?>
        </td>
    </tr>
</table>
<div style="padding-left:110px;font-size:10px;"></div>
<br />
<div>
        <img style="padding-left:40px;" src="<?php echo BASE_URL; ?>uploads/sicepat/<?php echo $image; ?>" width="160px">
</div>
<div style="padding-left:70px;font-size:10px;"><strong>Alpha - Thank You</strong></div>
<script src="https://code.jquery.com/jquery-2.2.4.js"></script>
  <script>
  $(window).load(function () {
    window.print();
    window.close();
  });
  </script>
</body>
</html>