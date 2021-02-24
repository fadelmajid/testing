<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<table>
<thead>
        <tr>
            <th>date</th>
            <th>total_issued</th>
            <th>total_used</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $col_number = 3;
            foreach ($all_data as $value){
                echo '
                    <tr>
                        <td>'. $value->date .'</td>
                        <td>'. $value->total_issued .'</td>
                        <td>'. $value->total_used .'</td>
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