Dear <?php echo $user_email?>,

You as administrator, we send who user have been top up more than 500k in a day.
Here that data : 
<?php foreach($user as $value) { ?>
Name            = <?php echo $value->user_name; ?>   
Email           = <?php echo $value->email; ?>
No. HP          = <?php echo $value->user_phone; ?>
Type Payment    = <?php echo $value->utop_payment; ?><br />
Topup           = <?php echo $value->total_topup; ?>
Count           = <?php echo $value->count_topup; ?>
Created Date    = <?php echo $date; ?>

<?php } ?>