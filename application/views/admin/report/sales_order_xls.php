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
            <th>Date</th>
            <th>Time</th>
            <th>Customer ID</th>
            <th>Payment Method</th>
            <th>Order Code</th>
            <th>Courier</th>
            <th>Voucher</th>
            <th>Type</th>
            <th>Store</th>
            <th>Status</th>
            <th>Total Cups</th>
            <th>Sub Total</th>
            <th>Disc</th>
            <th>Total</th>
            <th>Delivery Fee</th>
            <th>Disc Delivery Fee</th>
            <th>Grand Total</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $col_number = 17;
            foreach ($all_data as $value){
                echo '
                    <tr>
                        <td>'. date('Y-m-d', strtotime($value->uor_date)) .'</td>
                        <td>'. date('H:i', strtotime($value->uor_date)) .'</td>
                        <td>'. $value->user_id .'</td>
                        <td>'. $value->pymtd_name .'</td>
                        <td>'. $value->uor_code .'</td>
                        <td>'. (isset($value->uorcr_vendor) ? ucfirst($value->uorcr_vendor) : '') .'</td>
                        <td>'. (isset($value->vc_code) && !empty($value->vc_code) ? $value->vc_code : '') .'</td>
                        <td>'. $value->uor_delivery_type .'</td>
                        <td>'. $value->st_name .'</td>
                        <td>'. $value->uor_status.'</td>
                        <td>'. $value->total_cups_per_order .'</td>
                        <td>'. $value->sub_total .'</td>
                        <td>'. $value->disc .'</td>
                        <td>'. $value->total .'</td>
                        <td>'. $value->uor_actual_delivery_fee .'</td>
                        <td>'. $value->disc_delivery_fee .'</td>
                        <td>'. $value->grand_total .'</td>
                    </tr>
                ';
            }
            if(empty($all_data)){
                echo '
                <td colspan="'. $col_number .'">Data not found!</td>
                <tr>
                    </tr>
                ';
            }
        ?>
    </tbody>
</table>