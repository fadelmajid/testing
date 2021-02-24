<?php
defined('BASEPATH') OR EXIT('No direct script access allowed');
/**
*
*/
class Locktransdb extends MY_Model {
    function __construct()
    {
        parent::__construct();
    }

    //=== BEGIN LOCK TRANSACTION
    function insert($lock_remarks)
    {
        $now = date('Y-m-d H:i:s');
        $sql = "INSERT INTO {$this->tbl_lock_transaction} (lock_id, lock_remarks, updated_date) VALUES (1, ?, ?) ON DUPLICATE KEY UPDATE lock_remarks = ?, updated_date = ?";

        $this->db->query($sql, [$lock_remarks, $now, $lock_remarks, $now]);
    }
    //=== END LOCK TRANSACTION
}
?>
