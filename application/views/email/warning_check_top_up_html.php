<tr>
    <td bgcolor="#ffffff" style="padding: 20px 30px 30px 30px;">
        Dear <b><?php echo $user_email?></b> , <br>
        <br>
        You as administrator, we send who user have been top up more than 500k in a day. <br>
        Here that data : <br>
        <?php foreach($user as $value) { ?>
        Name            = <?php echo $value->user_name; ?><br />
        Email           = <?php echo $value->email; ?><br />
        No. HP          = <?php echo $value->user_phone; ?><br />
        Type Payment    = <?php echo $value->utop_payment; ?><br />
        Topup           = <?php echo $value->total_topup; ?><br />
        Count           = <?php echo $value->count_topup; ?><br />
        Created Date    = <?php echo $date; ?><br /><br /><br /><br />
        <?php } ?>
    </td>
</tr>