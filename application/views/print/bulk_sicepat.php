<!DOCTYPE html>
<html>
<head>
    <style type="text/css" media="print">
        @page {
            size: auto;   /* auto is the initial value */
            margin: 0;  /* this affects the margin in the printer settings */
            
        }
        @media print {
            footer {page-break-after: always;}
        }
    </style>
</head>
<body>
    <?php foreach($order_data as $key_order => $val_order) {?>
<div>
        <img style="padding-left:20px; width: 215px;" src="<?php echo ASSETS_URL; ?>images/logo.png">
</div>
<div style="padding-left:55px;"><strong>DELIVERY ORDER</strong></div><br>
<table>
    <tr>
        <td>#<?php echo $val_order->uor_code; ?> <span style="padding-left: 20px; float: right"><?php echo date('d M Y') ?></span></td>
    </tr>
    <tr>
        <td><?php  echo $val_order->uadd_person ?><span style="padding-left: 110px; float: right"><?php echo date('H:i') ?></td>
    </tr>
    <tr>
        <td><?php echo $val_order->uadd_phone ?></td>
    </tr>
    <tr>
        <td><br><strong>Address</strong></td>
    </tr>
    <tr>
        <td><?php echo nl2br($val_order->uadd_street); ?></td>
    </tr>
    <?php 
    if(!empty($val_order->uadd_notes)) { ?>
    <tr>
        <td><strong>Delivery Notes</strong></td>
    </tr>
    <tr>
        <td><?php echo nl2br($val_order->uadd_notes); ?> </td>
    </tr>
    <?php } ?>
    <tr>
        <td><strong>Items</strong></td>
    </tr>
    <tr>
        <td><?php
                foreach($order_product[$key_order] as $product){
                    echo $product->pd_qty ." ". $product->uorpd_name .'<br />';
                } ?>
        </td>
    </tr>
</table>
<div style="padding-left:110px;font-size:10px;"></div>
<br />
<div>
        <img style="padding-left:40px;" src="<?php echo BASE_URL; ?>uploads/sicepat/<?php echo $image[$key_order]; ?>" width="160px">
</div>
<footer style="padding-left:70px;font-size:10px;"><strong>Alpha - Thank You</strong></footer>
<?php } ?>
<script src="https://code.jquery.com/jquery-2.2.4.js"></script>
  <script>
  $(window).load(function () {
    window.print();
    window.close();
  });
  </script>
</body>
</html>