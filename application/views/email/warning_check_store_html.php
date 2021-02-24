<tr>
    <td bgcolor="#ffffff" style="padding: 20px 30px 30px 30px;">
        Dear <b><?php echo $user_email?></b> , <br>
        <br>
        You as administrator, we send who store have been updated operational hours in a day. <br>
        Here that data : <br>
        <?php echo 'Total Store : '. $total; ?>  <br><br>
        <?php 
        $no = 1;
        foreach($data as $value) { ?>
            <?php echo $no; ?>. Store Name = <?php echo $value->st_name; ?><br />
        <?php $no++; } ?>
    </td>
</tr>