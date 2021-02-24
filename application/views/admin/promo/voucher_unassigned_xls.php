<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<table>
    <thead>
        <tr>
            <th colspan="8">Voucher Unassaigned</th>
        </tr>
        <tr></tr>
        <tr></tr>
    </thead>
    <tbody>
        <tr>
            <th>Voucher Unassaigned ID</th>
            <th>Promo Name</th>
            <th>User ID</th>
            <th>Voucher Unassigned Code</th>
            <th>Voucher Unassigned Status</th>
            <th>Expired Date</th>
            <th>Created Date</th>
            <th>Updated Date</th>
        </tr>
        <?php
            foreach ($all_data as $key => $value) {
        ?>
                <tr>
                    <td><?php echo $value->vcu_id; ?></td>
                    <td><?php echo $value->prm_name; ?></td>
                    <td><?php echo $value->user_id; ?></td>
                    <td><?php echo "'".$value->vcu_code; ?></td>
                    <td><strong><?php echo ucfirst($value->vcu_status); ?></strong></td>
                    <td><?php echo show_date($value->expired_date, true); ?></td>
                    <td><?php echo show_date($value->created_date, true); ?></td>
                    <td><?php echo show_date($value->updated_date, true); ?></td>
                </tr>
        <?php
            }
            if (empty($all_data)) {
                echo '
                    <tr>
                        <td class="error" colspan="100%">Data not found!</td>
                    </tr>
                ';
            }
        ?>
    </tbody>
</table>