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
            <th>Member Since</th>
            <th>Referal Code</th>
            <th>Last Login</th>
            <th>Last Activity</th>
            <th>Balance</th>
            <th>Status</th>
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
                        <td>'. show_date($value->created_date, true) .'</td>
                        <td>\''. $value->user_code .'</td>
                        <td>'. show_date($value->last_login, true) .'</td>
                        <td>'. show_date($value->last_activity, true) .'</td>
                        <td>'. $value->uwal_balance .'</td>
                        <td>'. $value->user_status.'</td>
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