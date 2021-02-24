<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once("./vendor/autoload.php");

use Google\Cloud\Storage\StorageClient;

class Google_cloud_bucket{
    private $bucket_name    = BUCKET_NAME;
    private $project_id     = BUCKET_PROJECT_ID;
    private $ci;
    private $credential     = [];
 
	function __construct() {    
        $this->ci =& get_instance();
        $this->ci->load->config(BUCKET_CREDENTIAL);
        $this->credential = $this->ci->config->item('bucket_credential');
    }

/**
 * Upload a file.
 *
 * @param Array $data are fill from the name of the object and the path to the file to upload.
 *
 * @return Psr\Http\Message\StreamInterface
 */
    public function upload_image($data){
        $storage = new StorageClient([
            "projectId" => $this->project_id,
            "keyFile"   => $this->credential
        ]);

        $file = fopen($data['source'], 'r');

        $bucket = $storage->bucket($this->bucket_name); // Put your bucket name here.

        $object = $bucket->upload($file, [
            'name' => $data['name']
        ]);
        
        return $object;
    }
    
}