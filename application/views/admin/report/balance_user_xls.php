<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Opening</th>
            <th>Topup</th>
            <th>Cashback</th>
            <th>Transaction</th>
            <th>Refund</th>
            <th>Withdraw</th>
            <th>Closing</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $col_number = 10;
            foreach ($all_data as $value){
                echo '
                    <tr>
                        <td>'. $value->user_id .'</td>
                        <td>'. $value->user_name .'</td>
                        <td>'. $value->user_email .'</td>
                        <td>\''. $value->user_phone .'</td>
                        <td>'. $value->total_opening_balace .'</td>
                        <td>'. $value->total_topup .'</td>
                        <td>'. $value->total_cashback .'</td>
                        <td>'. $value->total_transaction .'</td>
                        <td>'. $value->total_refund .'</td>
                        <td>'. $value->total_withdraw .'</td>
                        <td>'. $value->total_closing_balance .'</td>
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