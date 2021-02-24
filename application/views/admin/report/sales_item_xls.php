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
            <th>Voucher Code</th>
            <th>Type</th>
            <th>Store</th>
            <th>PD Name</th>
            <th>COGS</th>
            <th>Price</th>
            <th>Disc Per Item</th>
            <th>Qty</th>
            <th>Total Price</th>
            <th>Total Disc Per Item</th>
            <th>Grand Total</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $col_number = 17;
            foreach ($all_data as $key => $value){
                $cogs_price = ( isset($cogs[$value->pd_id][date("Y-m-d", strtotime($value->uor_date))]) && !empty($cogs[$value->pd_id][date("Y-m-d", strtotime($value->uor_date))]) ?  $cogs[$value->pd_id][date("Y-m-d", strtotime($value->uor_date))] : 0 );
                echo '
                    <tr>
                        <td>'. date('Y-m-d', strtotime($value->uor_date)) .'</td>
                        <td>'. date('H:i', strtotime($value->uor_date)) .'</td>
                        <td>'. $value->user_id .'</td>
                        <td>'. $value->pymtd_name .'</td>
                        <td>'. $value->uor_code .'</td>
                        <td>'. (isset($value->vc_code) && !empty($value->vc_code) ? $value->vc_code : '') .'</td>
                        <td>'. $value->uor_delivery_type .'</td>
                        <td>'. $value->st_name .'</td>
                        <td>'. $value->uorpd_name .'</td>
                        <td>'. $cogs_price .'</td>
                        <td>'. $value->uorpd_final_price .'</td>
                        <td>'. $value->discount .'</td>
                        <td>'. $value->uorpd_qty .'</td>
                        <td>'. $value->uorpd_final_price * $value->uorpd_qty .'</td>
                        <td>'. $value->discount * $value->uorpd_qty.'</td>
                        <td>'. $value->total .'</td>
                    </tr>
                ';
            }
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