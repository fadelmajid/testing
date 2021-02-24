<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<table>
    <thead>
        <tr>
            <th>user_id</th>
            <th>total_transaction</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $col_number = 2;
            foreach ($all_data as $value){
                echo '
                    <tr>
                        <td>'. $value->user_id .'</td>
                        <td>'. $value->total_transaction .'</td>
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