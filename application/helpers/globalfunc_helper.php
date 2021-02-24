<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if(!function_exists('get_client_ip'))
{
    function get_client_ip()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        
        return $ipaddress;
    }
}


if(!function_exists('is_pid'))
{
    function is_pid($pid)
    {
        $lines_out = array();
        exec('ps '.(int)$pid, $lines_out);
        if(count($lines_out) >= 2) {
            // Process is running
            return true;
        }
        return false;
    }
}

if(!function_exists('num_replace_range'))
{
    function num_replace_range($num, $min_num, $max_num)
    {
        if($num < $min_num){
            return $min_num;
        }else if($num > $max_num){
            return $max_num;
        }else{
            return $num;
        }
    }
}

if(!function_exists('time_diff'))
{
    function time_diff( $date )
    {
        if( empty( $date ) )
        {
            return "No date provided";
        }
        
        $now = time();
    
        $unix_date = strtotime( $date );
        
        $difference = $now - $unix_date;
        
        $sec_minute = 60;
        $sec_hour   = $sec_minute * 60;
        $sec_day    = $sec_hour * 24;
        
        $arr_ret = array();
        
        if($difference >= $sec_day){
            $count_day  = floor($difference / $sec_day);
            $difference = $difference - ( $count_day * $sec_day );
            $arr_ret[]  = $count_day.' day'.($count_day > 1 ? 's' : '').'';
        }
        if($difference >= $sec_hour){
            $count_hour = floor($difference / $sec_hour);
            $difference = $difference - ( $count_hour * $sec_hour );
            $arr_ret[]  = $count_hour.' hour'.($count_hour > 1 ? 's' : '').'';
        }
        if($difference >= $sec_minute){
            $count_minute   = floor($difference / $sec_minute);
            $difference     = $difference - ( $count_minute * $sec_minute );
            $arr_ret[]      = $count_minute.' minute'.($count_minute > 1 ? 's' : '').'';
        }
        if($difference > 0){
            $arr_ret[]      = $difference.' second'.($difference > 1 ? 's' : '').'';
        }
        
        return implode(',', $arr_ret);
    }
}

if(!function_exists('time_ago'))
{
    function time_ago( $date )
    {
        
        $today      = new DateTime(date('Y-m-d H:i:s'));
        //$thatDay = new DateTime('Sun, 10 Nov 2013 14:26:00 GMT');
        $thatDay    = new DateTime($date);
        
        $newdate = $today->diff($thatDay);
        
        if ($newdate->y > 0)
        {
            $number = $newdate->y;
            $unit = "year";
        }
        else if ($newdate->m > 0)
        {
            $number = $newdate->m;
            $unit = "month";
        }   
        else if ($newdate->d > 0)
        {
            $number = $newdate->d;
           $unit = "day";
        }
        else if ($newdate->h > 0)
        {
            $number = $newdate->h;
            $unit = "hour";
        }
        else if ($newdate->i > 0)
        {
            $number = $newdate->i;
            $unit = "minute";
        }
        else if ($newdate->s > 0)
        {
            $number = $newdate->s;
            $unit = "second";
        }
        
        $unit .= $number  > 1 ? "s" : "";
        
        $ret = $number." ".$unit." "."ago";
        return $ret;
    }
}

if(!function_exists('years_old'))
{
    function years_old( $date )
    {
        
        $today      = new DateTime(date('Y-m-d H:i:s'));
        //$thatDay = new DateTime('Sun, 10 Nov 2013 14:26:00 GMT');
        $thatDay    = new DateTime($date);
        
        $newdate = $today->diff($thatDay);
        
        if ($newdate->y > 0)
        {
            $number = $newdate->y;
            $unit = "year";
        }
        else if ($newdate->m > 0)
        {
            $number = $newdate->m;
            $unit = "month";
        }   
        else if ($newdate->d > 0)
        {
            $number = $newdate->d;
           $unit = "day";
        }
        else if ($newdate->h > 0)
        {
            $number = $newdate->h;
            $unit = "hour";
        }
        else if ($newdate->i > 0)
        {
            $number = $newdate->i;
            $unit = "minute";
        }
        else if ($newdate->s > 0)
        {
            $number = $newdate->s;
            $unit = "second";
        }
        
        $unit .= $number  > 1 ? "s" : "";
        
        $ret = $number." ".$unit." "."old";
        return $ret;
    }
}

//function untuk kebutuhan form dan table
if(!function_exists('set_form_msg'))
{
    function set_form_msg( $arr_msg )
    {
        if( ! empty($arr_msg)){
            $string = '
            <div class="alert alert-'. $arr_msg['type'] .' alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                '. $arr_msg['msg'] .'
            </div>';
        }else{
            $string = '';
        }
        
        return $string;
    }
}


if(!function_exists('js_clearform'))
{
    function js_clearform($formid='theform')
    {
        return '<script>clearform("#'.$formid.'");</script>';
    }
}

if(!function_exists('set_var'))
{
    function set_var($input, $default)
    {
        return ($input == '' ? $default : $input);
    }
}


if(!function_exists('sort_table_order'))
{
    function sort_table_order($allow_sort, $socol, $soby, $default="")
    {
        $socol = trim(strtolower($socol));
        $soby = trim(strtolower($soby));
        $order_by = $default;
        if(isset($allow_sort[$socol])){
            $order_by = $allow_sort[$socol].' '.($soby == 'desc' ? 'DESC' : 'ASC');
        }
        
        return $order_by;
    }
}

if(!function_exists('sort_table_icon'))
{
    function sort_table_icon($menu_url, $colname, $title, $xtravar=array())
    {
        $pos = strpos($menu_url, '?');
        
        $page   = (isset($_GET['page']) ? $_GET['page'] : 1);
        $socol  = (isset($_GET['sc']) ? $_GET['sc'] : '');
        $soby   = (isset($_GET['sb']) ? $_GET['sb'] : 'ASC');
        
        $reserved_var = array('page', 'sc', 'sb');
        $qstring = '';
        if(array($xtravar)){
            foreach($xtravar as $key=>$val){
                if( ! in_array($key, $reserved_var)){
                    $qstring .= $key.'='.$val.'&';
                }
            }
        }
        
        // Note our use of ===.  Simply == would not work as expected
        // because the position of 'a' was the 0th (first) character.
        if ($pos === false) {
            $url_prefix = $menu_url.'?'.$qstring;
        }else{
            $url_prefix = $menu_url.'&'.$qstring;
        }
        
        $url_prefix .= 'page='.$page.'&sc='.$colname.'&sb=';
        
        if($socol == $colname){
            $html = '';
            if($soby == 'ASC'){
                $html .= '<i class="fa fa-sort-alpha-asc"></i>';
            }else{
                $html .= '<a href="'.$url_prefix.'ASC"><i class="fa fa-sort-alpha-asc"></i></a>';
            }
            
            $html .= ' '.$title.' ';
            
            if($soby == 'DESC'){
                $html .= '<i class="fa fa-sort-alpha-desc"></i>';
            }else{
                $html .= '<a href="'.$url_prefix.'DESC"><i class="fa fa-sort-alpha-desc"></i></a>';
            }
            
        }else{
            
            $html = '<a href="'.$url_prefix.'ASC"><i class="fa fa-sort-alpha-asc"></i></a>';
            $html .= ' '.$title.' ';
            $html .= '<a href="'.$url_prefix.'DESC"><i class="fa fa-sort-alpha-desc"></i></a>';
            
        }
        
        
        return $html;
    }
}


if(!function_exists('debug'))
{
    function debug($variable)
    {
        
        echo '<pre>';
        if (is_string($variable)) {
            echo $variable;
        }else{
            print_r($variable);
        }
    }
}

if(!function_exists('convert_to_user_date'))
{
    function convert_to_user_date($date, $format = 'Y-m-d H:i:s', $userTimeZone = 'Asia/Jakarta', $serverTimeZone = 'UTC')
    {
        try {
            $dateTime = new DateTime ($date, new DateTimeZone($serverTimeZone));
            $dateTime->setTimezone(new DateTimeZone($userTimeZone));
            return $dateTime->format($format);
        } catch (Exception $e) {
            return '';
        }
    }
}


if(!function_exists('convert_to_server_date'))
{
    function convert_to_server_date($date, $format = 'Y-m-d H:i:s', $userTimeZone = 'Asia/Jakarta', $serverTimeZone = 'UTC')
    {
        try {
            $dateTime = new DateTime ($date, new DateTimeZone($userTimeZone));
            $dateTime->setTimezone(new DateTimeZone($serverTimeZone));
            return $dateTime->format($format);
        } catch (Exception $e) {
            return '';
        }
    }
}

if(!function_exists('show_date'))
{
    function show_date($date, $show_time=false, $format = 'd M Y')
    {
        if($date == null || $date == '0000-00-00' || $date == '0000-00-00 00:00' || $date == '0000-00-00 00:00:00'){
            return '';
        }else{
                
            if($show_time){
                if($format == 'd M Y'){
                    $format = 'd M Y, H:i';
                }
            }
            
            $new_dt = strtotime($date);
            return date($format, $new_dt);
        }
        
    }
}

if(!function_exists('convert_bytes'))
{
    function convert_bytes($size, $decimal=2) {
        //$size = memory_get_usage(true); SIZE MUST IN BYTE
        $string = '';
        if ($size < 1024)
            $string = $size." bytes";
        elseif ($size < 1048576)
            $string = round($size/1024,$decimal)." KiB";
        elseif ($size < 1073741824)
            $string = round($size/1048576,$decimal)." MiB";
        else
            $string = round($size/1073741824,$decimal)." GiB";
           
        return $string;
    } 
}

if(!function_exists('filter_email'))
{
    function filter_email($mail) {
        if($mail !== "") {
            $pattern = "/^([A-Za-z0-9\.|\-|_]{1,60})([@])";
            $pattern .="([A-Za-z0-9\.|\-|_]{1,60})(\.)([A-Za-z]{2,3})$/";
            if(!preg_match($pattern, $mail)) {
                return FALSE;
            }else {
                return TRUE;
            }
        }
    }
}


//=== BEGIN CURRENCY RELATED
if(!function_exists('idr_format'))
{
    function idr_format($nominal, $prefix) {
        return ($prefix ? 'IDR ' : '').number_format($nominal, 0, ",", ".");
    }
}

if(!function_exists('usd_format'))
{
    function usd_format($nominal, $prefix) {
        return ($prefix ? 'USD ' : '').number_format($nominal, 1, ".", ",");
    }
}

if(!function_exists('sgd_format'))
{
    function sgd_format($nominal, $prefix) {
        return ($prefix ? 'SGD ' : '').number_format($nominal, 1, ".", ",");
    }
}

if(!function_exists('aud_format'))
{
    function aud_format($nominal, $prefix) {
        return ($prefix ? 'AUD ' : '').number_format($nominal, 1, ".", ",");
    }
}

if(!function_exists('currency_format'))
{
    function currency_format($nominal, $curr='IDR', $prefix=FALSE) {
        if(strtolower($curr) == 'usd'){
            return usd_format($nominal, $prefix);
        
        }else if(strtolower($curr) == 'sgd'){
            return sgd_format($nominal, $prefix);
        
        }else if(strtolower($curr) == 'aud'){
            return aud_format($nominal, $prefix);
        
        }else{
            return idr_format($nominal, $prefix);
        }
    }
}

if(!function_exists('convert_currency_format'))
{
    function convert_currency_format($idr_nominal, $curr='IDR', $curr_value=1, $prefix=FALSE) {
        
        $new_nominal = convert_currency($idr_nominal, $curr_value);
        
        return currency_format($new_nominal, $curr, $prefix);
        
    }
}

if(!function_exists('convert_currency'))
{
    function convert_currency($idr_nominal, $curr_value) {
        
        if($curr_value > 1){
            $new_nominal = round(($idr_nominal / $curr_value), 1, PHP_ROUND_HALF_UP);
        }else{
            $new_nominal = $idr_nominal;
        }
        
        return $new_nominal;
        
    }
}

//=== END CURRENCY RELATED


//=== BEGIN RANDOM THINGS
if(!function_exists('set_export_url'))
{
    function set_export_url($base_url='', $newparam='export=xls') {
        $findme   = '?';
        $pos = strpos($base_url, $findme);
        
        // Note our use of ===.  Simply == would not work as expected
        // because the position of 'a' was the 0th (first) character.
        if ($pos === false) {
            return $base_url.'?'.$newparam;
        }else{
            return $base_url.'&'.$newparam;
        }
        
    }
}
//=== END RANDOM THINGS

//=== BEGIN ROUTING
if(!function_exists('clean_url_string'))
{
    function clean_url_string($string="")
    {
        $string     = strtolower($string);
        $string     = preg_replace('/[^a-zA-Z0-9]+/', '-', $string);
        $string     = trim($string);
       
        return $string;
    }
}
//=== END ROUTING

if(!function_exists('clean_phone'))
{
    function clean_phone($num) 
    {
        //make value to lowercase
        $clean_phone = strtolower($num);
        $clean_phone = trim($clean_phone);
        //check if value have alphabet
        $clean_phone = preg_replace('/[^0-9]+/', "" ,$clean_phone);
        if($clean_phone != "") {
            //check value if start from 0 and it will replace with +62
            $clean_phone = 0 == substr($clean_phone, 0, 1) ? '+62'.substr($clean_phone, 1, strlen($clean_phone)) : '+'.$clean_phone ;
        }
        
        return $clean_phone;
    }
}