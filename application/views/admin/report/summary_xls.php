<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<table>
    <thead>
        <tr>
            <td><strong>Total Order : </strong></td>
            <td><?php echo number_format($total_report->total_order); ?></td>
            <td><strong>Total Cups Free: </strong></td>
            <td><?php echo number_format($total_report->total_cups_free); ?></td>
            <td><strong>Total Cups Pickup: </strong></td>
            <td><?php echo number_format($total_report->total_cups_pickup); ?></td>
            <td><strong>Total Espresso Base: </strong></td>
            <td><?php echo number_format($total_report->total_espresso_base); ?></td>
        </tr>
        <tr>
            <td><strong>Total Cups : </strong></td>
            <td><?php echo number_format($total_report->total_cups); ?></td>
            <td><strong>Total Cups Paid: </strong></td>
            <td><?php echo number_format($total_report->total_cups_paid); ?></td>
            <td><strong>Total Cups Delivery: </strong></td>
            <td><?php echo number_format($total_report->total_cups_delivery); ?></td>
            <td><strong>Total Tea by TWG : </strong></td>
            <td><?php echo number_format($total_report->total_tea_by_twg); ?></td>
        </tr>
        <tr></tr>
        <tr>
            <td><strong>Sub Total : </strong></td>
            <td><?php echo number_format($total_report->sub_total); ?></td>
            <td><strong>Discount : </strong></td>
            <td><?php echo number_format($total_report->total_disc); ?></td>
            <td><strong>Total : </strong></td>
            <td><?php echo number_format($total_report->total); ?></td>
        </tr>
        <tr>
            <td><strong>Delivery Fee : </strong></td>
            <td><?php echo number_format($total_report->total_delivery_fee); ?></td>
            <td><strong>Disc Delivery Fee : </strong></td>
            <td><?php echo number_format($total_report->disc_delivery_fee); ?></td>
            <td><strong>Grand Total : </strong></td>
            <td><?php echo number_format($total_report->grand_total); ?></td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr>
            <th>Subsplan Name</th>
            <th>Total</th>
            <th>Total Price</th>
            <th>Total Subscriber</th>
            <th>Voucher Used</th>
            <th>Voucher Expired</th>
            <th>Voucher Unused</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $total_cash_in = $total_report->grand_total;
            $col_number = 4;
            foreach ($all_data as $value){
                echo '
                    <tr>
                        <td>'. $value->subsplan_name .'</td>
                        <td>'. $value->total_order .'</td>
                        <td>'. $value->total_price .'</td>
                        <td>'. $value->total_subscriber .'</td>
                        <td>'. $value->total_voucher_used .'</td>
                        <td>'. $value->total_voucher_expired .'</td>
                        <td>'. $value->total_voucher_unused .'</td>
                    </tr>
                ';
                $total_cash_in = $total_cash_in + $value->total_price;    
            }
            echo '
                    <tr></tr>
                    <tr>
                        <td><strong>Total Cash-In : </strong>'. $total_cash_in .'</td>
                    </tr>
                ';
            if(empty($all_data)){
                echo '
                    <tr>
                        <td colspan="'.$col_number.'">Data not found!</td>
                    </tr>
                ';
            }
        ?>
    </tbody>
</table>