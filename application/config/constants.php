<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') OR define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
defined('FILE_READ_MODE')  OR define('FILE_READ_MODE', 0644);
defined('FILE_WRITE_MODE') OR define('FILE_WRITE_MODE', 0666);
defined('DIR_READ_MODE')   OR define('DIR_READ_MODE', 0755);
defined('DIR_WRITE_MODE')  OR define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ')                           OR define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE')                     OR define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE')       OR define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE')  OR define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE')                   OR define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE')              OR define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT')            OR define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT')       OR define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        OR define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          OR define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         OR define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   OR define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  OR define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') OR define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     OR define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       OR define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      OR define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      OR define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code


/*
|--------------------------------------------------------------------------
| Application Constants
|--------------------------------------------------------------------------
|
| constanta yang berhubungan dengan applikasi
| constanta yang berhubungan dengan server harus dimasukan kedalam {production|testing|development}/sv_variables.php
|
*/
//FOLDER
define('PIDFOLDER', FCPATH.'pidfolder/'); // folder untuk nampung pid cronjob
define('LOG_FOLDER', FCPATH.'application/logs/');
define('ADMIN_MENU_FOLDER', 'admin/menu/');

//DEFAULT SMTP UNTUK KIRIM EMAIL
define('DSMTP_FROM_NAME', 'lorem');
define('DSMTP_HOST', 'lipsum.com');
define('DSMTP_USER', 'nothing@lipsum.com');
define('DSMTP_PASS', 'this-is-password');
define('DSMTP_PORT', '25');
define('DSMTP_FLAG', 'none');


//SLEEP, VARIABLE INI DIPAKAI DI MY_Service.php
define('SLEEP_DIVIDE', 1000000);//dipakai untuk total_sleep / SLEEP_DIVIDE , kalau total_sleep dalam microseconds SLEEP_DIVIDE = 1000,000, kalau total_sleep dalam second SLEEP_DIVIDE = 1
define('SLEEP_MICROSECOND', 1000000);//1 detik = 1000,000 micro seconds
define('COUNT_SLEEP_START', 1);
define('COUNT_SLEEP_WARN', 7200);//log ke file bila $sleep_no == $sleep_warn

//FORM VALIDATION
define('PREFIX_ERROR_DELIMITER', '<div class="text-left text-danger">');
define('SUFFIX_ERROR_DELIMITER', '</div>');

//EMAIL
define('EMAIL_FROM', 'no-reply@gmail.com'); 
define('EMAIL_REPLY_TO', 'hello@gmail.com');
define('EMAIL_FOLDER', 'email/');
define('EMAIL_TEMPLATE', 'email/main_template');


//OTHERS
define('WEBMASTER_EMAIL', 'fadelmajid@gmail.com');
define('SEPARATOR_LOG', ' ; ');//untuk kebutuhan log4php
define('SEPARATOR_LOG_ERROR', ' ; [ERROR] ');//untuk kebutuhan log4php
define('SEPARATOR_LOG_LINE', '==========');//untuk kebutuhan log4php
define('PAGINATION_PER_PAGE', 30);

define('REDMARK', '<span style="color:#F00;">*</span>');
define('RESET_PASS_LIMIT', 20);
define('INVALID_LOGIN_LIMIT', 15);
define('INVALID_LOGIN_TIME', 12);//satuan jam, kalau dalam x jam sudah melakukan invalid login sebanyak INVALID_LOGIN_LIMIT, tidak bisa login lagi.

define('PROMO_FREECUP_PERIOD_MONTHS', 1);

define('VERSION', '1.0');

define('CONTACT_COMPANY','Alpha IT Development');
define('APPLOG', 'application.log');
define('FILESIZE',2048);

define('ADMIN_SALT','K3jDnPl94Se1');
