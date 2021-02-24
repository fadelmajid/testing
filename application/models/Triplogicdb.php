<?php
defined('BASEPATH') OR EXIT('No direct script access allowed');
/**
*
*/
class Triplogicdb extends MY_Model {
    function __construct()
    {
        parent::__construct();
    }

    //=== GET PAGING triplogic LOGS
    function getpaging_logs($where = '', $data = [], $order = "", $page = 1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $orderby    = ($order == "" ? " created_date ASC " : $order);

        $sql =
            "SELECT * FROM $this->tbl_triplogic_logs
            WHERE 1=1 {$where} ORDER BY $orderby";

        $query      = $this->db->query($sql, $data);
        $total_row  = $query->num_rows();

        $data[]     = $this->row_per_page;
        $data[]     = intval($first_row > $total_row ? $total_row : $first_row);
        $sql        .= " LIMIT ? OFFSET ? ";
        $query      = $this->db->query($sql, $data);

        $result['data']         = $query->result();
        $result['total_row']    = $total_row;
        $result['perpage']      = $this->row_per_page;
        return $result;
    }
    //=== END GET PAGING triplogic LOGS

    //=== START triplogic LOGS
    function insert_log($data)
    {
        $result = $this->db->insert($this->tbl_triplogic_logs, $data);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }

    function update_log($id, $data)
    {
        $this->db->where('triplogic_id', $id);
        return $this->db->update($this->tbl_triplogic_logs, $data);
    }
    //=== END

    // BEGIN UNTUK KEPERLUAAN CRONJOB
    function delete_logs($date)
    {
        $sql = "DELETE FROM {$this->tbl_triplogic_logs} WHERE `created_date` < ?";
        return $this->db->query($sql, [$date]);
    }
    // END UNTUK KEPERLUAAN CRONJOB
}
?>
