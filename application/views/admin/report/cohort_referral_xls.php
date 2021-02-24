<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<table>
<thead>
        <tr>
            <th>uref_from</th>
            <th>refer_date</th>
            <th>cohort_month</th>
            <th>cohort_period</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $col_number = 4;
            foreach ($all_data as $value){
                echo '
                    <tr>
                        <td>'. $value->uref_from .'</td>
                        <td>'. $value->refer_date .'</td>
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