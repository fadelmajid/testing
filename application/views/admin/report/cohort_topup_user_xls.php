<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<table>
<thead>
        <tr>
            <th>user_id</th>
            <th>topup_date</th>
            <th>topup_amount</th>
            <th>cohort_month</th>
            <th>cohort_period</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $col_number = 5;
            foreach ($all_data as $value){
                echo '
                    <tr>
                        <td>'. $value->user_id .'</td>
                        <td>'. $value->topup_date .'</td>
                        <td>'. $value->topup_amount .'</td>
                        <td>'. $value->cohort_month .'</td>
                        <td>'. $value->cohort_period .'</td>
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