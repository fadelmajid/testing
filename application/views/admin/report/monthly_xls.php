<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<table>
    <thead>
        <tr>
            <th>Period</th>
            <th><?php echo show_date($from); ?> to <?php echo show_date($to); ?></th>
        </tr>
        <tr></tr>
        <tr>
            <th>Description</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>APPS</strong></td>
        </tr>
        <tr>
            <td>Download Android</td>
            <td><?php echo $total_android; ?></td>
        </tr>
        <tr>
            <td>Download IOS</td>
            <td><?php echo $total_ios; ?></td>
        </tr>
        <tr>
            <td>Total Download</td>
            <td><?php echo $total_download; ?></td>
        </tr>
        <tr>
            <td>Total Member</td>
            <td><?php echo $user->total; ?></td>
        </tr>
        <tr><td></td></tr>
        <tr><td></td></tr>
        <tr><td></td></tr>
        <tr>
            <td><strong>Delivery</strong></td>
            <td><?php echo $total_delivery; ?></td>
        </tr>
        <tr>
            <td><strong>Pick Up</strong></td>
            <td><?php echo $total_pickup; ?></td>
        </tr>
        <tr><td></td></tr>
        <tr>
            <td><strong><u>New Users</u></strong></td>
        </tr>
        <tr>
            <td>Download without activity</td>
        </tr>
        <tr>
            <td>Yg claim free</td>
            <td><?php echo $claim_free->total; ?></td>
        </tr>
        <tr>
            <td>Yg claim free + ada repeat order</td>
            <td><?php echo $claim_free_repeat->total; ?></td>
        </tr>
        <tr>
            <td>Yg claim free tp gak lg order</td>
            <td><?php echo $claim_free_not_order->total; ?></td>
        </tr>
        <tr>
            <td>Yg lgs beli tanpa free</td>
            <td><?php echo $not_claim_free->total; ?></td>
        </tr>
        <tr><td></td></tr>
        <tr>
            <td><strong><u>Referal</u></strong></td>
        </tr>
        <tr>
            <td>Yg tereferensi dari temen</td>
            <td><?php echo $user_reff->total; ?></td>
        </tr>
        <tr>
            <td>Yg tereferensi dari temen gak claim</td>
            <td><?php echo $reff_not_claim->total; ?></td>
        </tr>
        <tr>
            <td>Yg tereferensi dari temen ada claim</td>
            <td><?php echo $reff_claim->total; ?></td>
        </tr>
        <tr>
            <td>Yg tereferensi dari temen ada claim tp gak repeat order</td>
            <td><?php echo $reff_claim_not_repeat->total; ?></td>
        </tr>
        <tr>
            <td>Yg tereferensi dari temen ada claim + ada repeat order</td>
            <td><?php echo $reff_claim_repeat->total; ?></td>
        </tr>
        <tr>
            <td>Yg tereferensi dari temen lgs beli</td>
            <td><?php echo $reff_not_free->total; ?></td>
        </tr>
        <tr><td></td></tr>
        <tr>
            <td><strong><u>TOP UP</u></strong></td>
        </tr>
        <tr>
            <td>Top up saldo plus lgs pakai (bulan yg sama)</td>
            <td><?php echo $user_topup_in_same_month->total; ?></td>
        </tr>
        <tr>
            <td>Top up saldo tp gak dipakai (Bulan yg sama)</td>
            <td><?php echo $user_topup_not_in_same_month->total; ?></td>
        </tr>
        <tr>
            <td>Top up saldo tp ada sisa balance</td>
            <td><?php echo $user_have_balance->total; ?></td>
        </tr>
        
    </tbody>
</table>