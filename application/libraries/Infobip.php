<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Infobip{

	function __construct() {    
        define('MULTIPART_BOUNDARY', '----'.md5(time()));
        define('EOL',"\r\n");// PHP_EOL cannot be used for emails we need the CRFL '\r\n'
    }

    public function get_body_part($form_field, $value) {
        if ($form_field === 'attachment') {
            $content = 'Content-Disposition: form-data; name="'.$form_field.'"; filename="'.basename($value).'"' . EOL;
            $content .= 'Content-Type: '.mime_content_type($value) . EOL;
            $content .= 'Content-Transfer-Encoding: binary' . EOL;
            $content .= EOL . file_get_contents($value) .EOL;
        } else {
            $content = 'Content-Disposition: form-data; name="' . $form_field . '"' . EOL;
            $content .= EOL . $value . EOL;
        }   
        return $content;
    }

    /*
     * Method to convert an associative array of parameters into the HTML body string
    */
    public function get_body($fields) {
        $content = '';
        foreach ($fields as $form_field => $value) {
            $values = is_array($value) ? $value : array($value);
            foreach ($values as $v) {
                $content .= '--' . MULTIPART_BOUNDARY . EOL . $this->get_body_part($form_field, $v);
            }
        }
        return $content . '--' . MULTIPART_BOUNDARY . '--'; // Email body should end with "--"
    }
    
    /*
     * Method to get the headers for a basic authentication with username and passowrd
    */
    public function get_header($auth){
        // Define the header
        return array('Authorization:App '.$auth, 'Content-Type: multipart/form-data ; boundary=' . MULTIPART_BOUNDARY );
    }

    public function send_email($path, $tpl_data, $verified){
        $response = "";
        //check if user email verified not 0        
        if($verified == 1) {
            // URL to the API that sends the email.
            $url = INFOBIP_URL.'/email/1/send';

            $CI =& get_instance();
            $content = $CI->load->view(EMAIL_FOLDER.$path.'_html', $tpl_data, TRUE);
            $text = $CI->load->view(EMAIL_FOLDER.$path.'_text', $tpl_data, TRUE);

            $html_data = [
                'content' => $content,
                'status' => $tpl_data['status']
            ];
            $html = $CI->load->view(EMAIL_TEMPLATE, $html_data, TRUE);
                
            // Associate Array of the post parameters to be sent to the API
            $postData = array('from' => EMAIL_FROM,
                                'to' => $tpl_data['user_email'],
                                'replyTo' => EMAIL_REPLY_TO,
                                'subject' => $tpl_data['subject'],
                                'text' => $text,
                                'html' => $html
                            );
            
            // Create the stream context.
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => $this->get_header(INFOBIP_AUTH),
                    'content' =>  $this->get_body($postData),
                )
            ));
            
            // Read the response using the Stream Context.
            $response = file_get_contents($url, false, $context);
        } else {
            $response = $tpl_data['user_email']." is not verified!";
        }
        
        return $response;
    }
}