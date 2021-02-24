Dear <?php echo $user_email?>,

You as administrator, we send which store have been updated operational hours in a day. 
Here that data : <br />
<?php echo 'Total Store : '. $total; ?>  <br><br>
<?php 
$no = 1;
foreach($data as $value) { ?>
    <?php echo $no; ?>. Store Name = <?php echo $value->st_name; ?><br />
<?php $no++; } ?>