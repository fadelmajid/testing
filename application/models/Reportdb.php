<?php
defined('BASEPATH') OR EXIT('No direct script access allowed');

class Reportdb extends MY_Model {
    protected $_database_clone;

    function __construct()
    {
        parent::__construct();
        $this->_database_clone = $this->load->database('default', TRUE);
    }

    //=== START SALES PER ITEM
    function getall_sales_item($where="", $data=array(), $order="", $limit=0)
    {
        $orderby    = ($order == "" ? " uor_id ASC " : $order);

        // search dulu row nya.
        $sql    = "SELECT uor.uor_id, uor.uor_date, uor.user_id, 
                        uor.uor_code, uor.uor_delivery_type, st.st_name, uor.uor_status,
                        uorpd.pd_id, uorpd.uorpd_name, uorpd.uorpd_qty,
                        uorpd.uorpd_final_price, IF(uorpd.uorpd_is_free = 1, uorpd.uorpd_final_price, 0 ) AS discount,
                        uorpd.uorpd_final_price * uorpd.uorpd_qty AS total_price,
                        IF(uorpd.uorpd_is_free = 1, uorpd.uorpd_final_price * uorpd.uorpd_qty, 0 ) AS total_disc,
                        IF(uorpd.uorpd_is_free = 1, 0, uorpd.uorpd_total ) AS total, pymtd.pymtd_name
                    FROM ". $this->tbl_order_product ." uorpd
                    INNER JOIN ". $this->tbl_order ." uor ON uor.uor_id = uorpd.uor_id
                    INNER JOIN ". $this->tbl_order_address ." uoradd ON uoradd.uor_id = uor.uor_id
                    INNER JOIN ". $this->tbl_store ." st ON st.st_id = uoradd.st_id
                    INNER JOIN ". $this->tbl_user_order_payment ." uorpy ON uorpy.uor_id = uor.uor_id AND uorpy.pyhis_status = 'paid'
                    INNER JOIN ". $this->tbl_payment_method ." pymtd ON pymtd.pymtd_id = uorpy.pymtd_id
                    WHERE 1=1 ". $where ." ORDER BY ".$orderby;

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->_database_clone->query($sql, $data);

        return $query->result();
    }

    function getpaging_sales_item($where="", $data=array(), $order="", $page=1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $orderby    = ($order == "" ? " uor_id ASC " : $order);

        // search dulu row nya.
        $sql_count  = " SELECT COUNT(*) total_row ";
        $sql_select = "SELECT uor.uor_id, uor.uor_date, uor.user_id,
                        uor.uor_code, uor.uor_delivery_type, st.st_name, uor.uor_status,
                        uorpd.pd_id, uorpd.uorpd_name,
                        uorpd.uorpd_final_price, IF(uorpd.uorpd_is_free = 1, uorpd.uorpd_final_price, 0 ) AS discount,
                        uorpd.uorpd_final_price * uorpd.uorpd_qty AS total_price,
                        IF(uorpd.uorpd_is_free = 1, uorpd.uorpd_final_price * uorpd.uorpd_qty, 0 ) AS total_disc,
                        uorpd.uorpd_qty, IF(uorpd.uorpd_is_free = 1, 0, uorpd.uorpd_total ) AS total, pymtd.pymtd_name, uov.vc_code";
                        
        $sql        = " FROM ". $this->tbl_order_product ." uorpd
                    INNER JOIN ". $this->tbl_order ." uor ON uor.uor_id = uorpd.uor_id
                    INNER JOIN ". $this->tbl_order_address ." uoradd ON uoradd.uor_id = uor.uor_id
                    INNER JOIN ". $this->tbl_store ." st ON st.st_id = uoradd.st_id
                    INNER JOIN ". $this->tbl_user_order_payment ." uorpy ON uorpy.uor_id = uor.uor_id AND uorpy.pyhis_status = 'paid'
                    INNER JOIN ". $this->tbl_payment_method ." pymtd ON pymtd.pymtd_id = uorpy.pymtd_id
                    LEFT JOIN (SELECT uor_id, GROUP_CONCAT(vc_code) as vc_code FROM ". $this->tbl_user_order_voucher." GROUP BY uor_id) uov ON uov.uor_id = uor.uor_id
                    WHERE 1=1 ". $where ." ORDER BY ".$orderby;

        $query      = $this->_database_clone->query($sql_count.$sql, $data);
        $result_count  = $query->row();
        $total_row     = $result_count->total_row;

        $data[] = $this->row_per_page;
        $data[] = intval($first_row > $total_row ? $total_row : $first_row);
        $sql        .= " LIMIT ? OFFSET ? ";
        $query      = $this->_database_clone->query($sql_select.$sql, $data);

        $result['data']         = $query->result();
        $result['total_row']    = $total_row;
        $result['perpage']      = $this->row_per_page;
        return $result;
    }

    function get_total_sales_item($where="", $data=array(), $order="", $limit=0)
    {
        $orderby    = ($order == "" ? " uor_id ASC " : $order);

        // search dulu row nya.
        $sql    = "SELECT SUM(uorpd.uorpd_final_price) AS price,
			                SUM(IF(uorpd.uorpd_is_free = 1, uorpd.uorpd_final_price, 0 )) AS discount,
                            SUM(uorpd.uorpd_qty) AS total_cups,
			                SUM(IF(uorpd.uorpd_is_free = 1, uorpd.uorpd_final_price, 0 )  * uorpd.uorpd_qty) AS total_disc,
                            SUM(IF(uorpd.uorpd_is_free = 1, 0, uorpd.uorpd_total )) as total,
                            SUM(uorpd.uorpd_final_price * uorpd.uorpd_qty) AS total_price
                    FROM ". $this->tbl_order_product ." uorpd
                    INNER JOIN ". $this->tbl_order ." uor ON uor.uor_id = uorpd.uor_id
                    INNER JOIN ". $this->tbl_user ." usr ON usr.user_id = uor.user_id
                    INNER JOIN ". $this->tbl_order_address ." uoradd ON uoradd.uor_id = uor.uor_id
                    INNER JOIN ". $this->tbl_store ." st ON st.st_id = uoradd.st_id
                    WHERE 1=1 ". $where ." ORDER BY ".$orderby;

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->_database_clone->query($sql, $data);

        return $query->row();
    }

    //=== END SALES PER ITEM

    //=== START SALES PER ORDER

    function getall_sales_order($where="", $data=array(), $order="", $limit=0)
    {
        $orderby    = ($order == "" ? " uor_id ASC " : $order);

        // search dulu row nya.
        $sql    = "SELECT uor.uor_id, uor.uor_date, uor.user_id,
                            uor.uor_code, uor.uor_delivery_type, st.st_name, uor.uor_status,
                            (uor.uor_subtotal + uorpd.total_disc) AS sub_total, uor.uor_total AS grand_total,
                            uorpd.total_cups AS total_cups_per_order, (uor.uor_discount + uorpd.total_disc) AS disc, uorpd.total_price,
                            IFNULL(uor.uor_actual_delivery_fee, 0) - uor.uor_delivery_fee AS disc_delivery_fee,
                            IF(uor.uor_discount != 0, uor.uor_subtotal - uor.uor_discount, uor.uor_subtotal) AS total, uor.uor_discount,
                            IFNULL(uor.uor_actual_delivery_fee, 0) AS uor_actual_delivery_fee, pymtd.pymtd_name, uorcr.uorcr_vendor
                    FROM ". $this->tbl_order ." uor
                    INNER JOIN ". $this->tbl_order_address ." uoradd ON uoradd.uor_id = uor.uor_id
                    INNER JOIN ". $this->tbl_user_order_payment ." uorpy ON uorpy.uor_id = uor.uor_id AND uorpy.pyhis_status = 'paid'
                    INNER JOIN ". $this->tbl_payment_method ." pymtd ON pymtd.pymtd_id = uorpy.pymtd_id
                    INNER JOIN ". $this->tbl_store ." st ON st.st_id = uoradd.st_id
                    INNER JOIN (SELECT uorpd.uor_id,
                                SUM(uorpd_qty) as total_cups,
                                SUM(IF(uorpd_is_free = 1, uorpd_final_price, 0 )) AS disc,
                                SUM(IF(uorpd_is_free = 1, uorpd_final_price  * uorpd_qty, 0 )) AS total_disc,
                                SUM(uorpd_final_price * uorpd_qty) AS total_price
                                FROM ". $this->tbl_order_product ." uorpd
                                GROUP BY uorpd.uor_id) AS uorpd ON uorpd.uor_id = uor.uor_id
                    LEFT JOIN ". $this->tbl_user_order_courier ." uorcr ON uorcr.uor_id = uor.uor_id AND uorcr.uorcr_status IN ('completed', 'delivered', 'drop')
                    WHERE 1=1 ". $where ." ORDER BY ".$orderby;

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->_database_clone->query($sql, $data);

        return $query->result();
    }

    function getpaging_sales_order($where="", $data=array(), $order="", $page=1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $orderby    = ($order == "" ? " uor_id ASC " : $order);

        // search dulu row nya.
        $sql        = "SELECT uor.uor_id, uor.uor_date, uor.user_id,
                                uor.uor_code, uor.uor_total, uor.uor_delivery_type,
                                IFNULL(uor.uor_actual_delivery_fee, 0)  AS uor_actual_delivery_fee,
                                st.st_name, uor.uor_status, (uor.uor_subtotal + uorpd.total_disc) AS sub_total,
                                uorpd.total_cups AS total_cups_per_order, (uor.uor_discount + uorpd.total_disc) AS disc, uorpd.total_price,
                                IFNULL(uor.uor_actual_delivery_fee, 0) - uor.uor_delivery_fee  AS disc_delivery_fee,
                                IF(uor.uor_discount != 0, uor.uor_subtotal - uor.uor_discount, uor.uor_subtotal) AS total, uor.uor_discount,
                                uor.uor_delivery_fee, uor.uor_total AS grand_total, pymtd.pymtd_name, uov.vc_code, uorcr.uorcr_vendor
                    FROM ". $this->tbl_order ." uor
                    INNER JOIN ". $this->tbl_order_address ." uoradd ON uoradd.uor_id = uor.uor_id
                    INNER JOIN ". $this->tbl_user_order_payment ." uorpy ON uorpy.uor_id = uor.uor_id AND uorpy.pyhis_status = 'paid'
                    INNER JOIN ". $this->tbl_payment_method ." pymtd ON pymtd.pymtd_id = uorpy.pymtd_id
                    INNER JOIN ". $this->tbl_store ." st ON st.st_id = uoradd.st_id
                    LEFT JOIN (SELECT uor_id, GROUP_CONCAT(vc_code) as vc_code FROM ". $this->tbl_user_order_voucher." GROUP BY uor_id) uov ON uov.uor_id = uor.uor_id
                    LEFT JOIN ". $this->tbl_user_order_courier ." uorcr ON uorcr.uor_id = uor.uor_id AND uorcr.uorcr_status IN ('completed', 'delivered', 'drop')
                    INNER JOIN (SELECT uorpd.uor_id,
                                SUM(uorpd_qty) as total_cups,
                                SUM(IF(uorpd_is_free = 1, uorpd_final_price, 0 )) AS disc,
                                SUM(IF(uorpd_is_free = 1, uorpd_final_price  * uorpd_qty, 0 )) AS total_disc,
                                SUM(uorpd_final_price * uorpd_qty) AS total_price
                                FROM ". $this->tbl_order_product ." uorpd
                                GROUP BY uorpd.uor_id) AS uorpd ON uorpd.uor_id = uor.uor_id
                    WHERE 1=1 ". $where ." ORDER BY ".$orderby;

        $query      = $this->_database_clone->query($sql, $data);
        $total_row  = $query->num_rows();

        $data[] = $this->row_per_page;
        $data[] = intval($first_row > $total_row ? $total_row : $first_row);
        $sql        .= " LIMIT ? OFFSET ? ";
        $query      = $this->_database_clone->query($sql, $data);

        $result['data']         = $query->result();
        $result['total_row']    = $total_row;
        $result['perpage']      = $this->row_per_page;
        return $result;
    }

    function get_total_sales_order($where="", $data=array(), $order="", $limit=0)
    {
        $orderby    = ($order == "" ? " uor_id ASC " : $order);

        // search dulu row nya.
        $sql    = "SELECT SUM(uor.uor_subtotal + uorpd.total_disc) AS sub_total,
                            SUM(uorpd.total_cups) AS total_cups,
                            SUM(uorpd.total_cups_free) as total_cups_free,
                            SUM(uorpd.total_cups_paid) as total_cups_paid,
                            SUM(IF(uor.uor_delivery_type = 'pickup', uorpd.total_cups, 0)) as total_cups_pickup,
                            SUM(IF(uor.uor_delivery_type = 'delivery', uorpd.total_cups, 0)) as total_cups_delivery,
                            SUM(uorpd.total_espresso_base) as total_espresso_base,
                            SUM(uorpd.total_tea_by_twg) as total_tea_by_twg,
                            SUM(IF(uor.uor_discount != 0, uor.uor_discount, uorpd.total_disc)) AS total_disc,
                            SUM(IFNULL(uor.uor_actual_delivery_fee, 0) - uor.uor_delivery_fee) AS disc_delivery_fee,
                            SUM(IF(uor.uor_discount != 0, uor.uor_subtotal - uor.uor_discount, uor.uor_subtotal)) AS total,
                            SUM(IFNULL(uor.uor_actual_delivery_fee, 0)) AS total_delivery_fee,
                            SUM(uor.uor_total) AS grand_total,
                            COUNT(uor.uor_id) AS total_order
                    FROM ". $this->tbl_order ." uor
                    INNER JOIN ". $this->tbl_order_address ." uoradd ON uoradd.uor_id = uor.uor_id
                    INNER JOIN ". $this->tbl_store ." st ON st.st_id = uoradd.st_id
                    INNER JOIN (SELECT uopd.uor_id,
                                SUM(uopd.uorpd_qty) as total_cups,
                                SUM(IF(uopd.uorpd_is_free = 1, uopd.uorpd_qty, 0)) as total_cups_free,
                                SUM(IF(uopd.uorpd_is_free = 0, uopd.uorpd_qty, 0)) as total_cups_paid,
                                SUM(IF(cat.cat_id = 1, uopd.uorpd_qty, 0)) as total_espresso_base,
                                SUM(IF(cat.cat_id = 2, uopd.uorpd_qty, 0)) as total_tea_by_twg,
                                SUM(IF(uopd.uorpd_is_free = 1, uopd.uorpd_final_price, 0 )) AS disc,
                                SUM(IF(uopd.uorpd_is_free = 1, uopd.uorpd_final_price  * uopd.uorpd_qty, 0 )) AS total_disc,
                                SUM(uopd.uorpd_final_price * uopd.uorpd_qty) AS total_price,
                                SUM(uorpd_final_price) AS uorpd_name, SUM(uorpd_final_price) AS uorpd_final_price,
                                SUM(uorpd_qty) AS uorpd_qty
                                FROM ". $this->tbl_order_product ." uopd
                                INNER JOIN ". $this->tbl_product ." pd ON pd.pd_id = uopd.pd_id
                                INNER JOIN ". $this->tbl_category ." cat ON cat.cat_id = pd.cat_id
                                GROUP BY uor_id) AS uorpd ON uorpd.uor_id = uor.uor_id
                    WHERE 1=1 ". $where ." ORDER BY ".$orderby;

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->_database_clone->query($sql, $data);

        return $query->row();
    }


    function get_total_sales($where="", $data=array())
    {
        //select data detail
        $sql_item    = "SELECT SUM(uorpd.uorpd_total) AS sub_total,
                                SUM(uorpd.uorpd_qty) AS total_cups,
                                SUM(IF(uorpd.uorpd_is_free = 1, uorpd.uorpd_qty, 0)) as total_cups_free,
                                SUM(IF(uorpd.uorpd_is_free = 0, uorpd.uorpd_qty, 0)) as total_cups_paid,
                                SUM(IF(uor.uor_delivery_type = 'pickup', uorpd.uorpd_qty, 0)) as total_cups_pickup,
                                SUM(IF(uor.uor_delivery_type = 'delivery', uorpd.uorpd_qty, 0)) as total_cups_delivery,
                                SUM(IF(cat.cat_id = 1, uorpd.uorpd_qty, 0)) as total_espresso_base,
                                SUM(IF(cat.cat_id = 2, uorpd.uorpd_qty, 0)) as total_tea_by_twg,
                                SUM(IF(uorpd.uorpd_is_free = 1, uorpd.uorpd_final_price  * uorpd.uorpd_qty, 0 )) AS total_disc_item
                        FROM ". $this->tbl_order ." uor
                        INNER JOIN ". $this->tbl_order_address ." uoradd ON uoradd.uor_id = uor.uor_id
                        INNER JOIN ". $this->tbl_order_product ." uorpd ON uorpd.uor_id = uor.uor_id
                        INNER JOIN ". $this->tbl_product ." pd ON pd.pd_id = uorpd.pd_id
                        INNER JOIN ". $this->tbl_category ." cat ON cat.cat_id = pd.cat_id
                        WHERE 1=1 ". $where ;

        $query_item      = $this->_database_clone->query($sql_item, $data);
        $result_item     = $query_item->row();

        //select data detail
        $sql_order    = "SELECT SUM(uor.uor_discount) AS total_disc_order,
                            SUM(IFNULL(uor.uor_actual_delivery_fee, 0) - uor.uor_delivery_fee) AS disc_delivery_fee,
                            SUM(IF(uor.uor_discount != 0, uor.uor_subtotal - uor.uor_discount, uor.uor_subtotal)) AS total,
                            SUM(IFNULL(uor.uor_actual_delivery_fee, 0)) AS total_delivery_fee,
                            SUM(uor.uor_total) AS grand_total,
                            COUNT(uor.uor_id) AS total_order
                    FROM ". $this->tbl_order ." uor
                    INNER JOIN ". $this->tbl_order_address ." uoradd ON uoradd.uor_id = uor.uor_id
                    WHERE 1=1 ". $where ;

        $query_order      = $this->_database_clone->query($sql_order, $data);
        $result_order     = $query_order->row();

        $result = new stdClass();
        $result->sub_total              = $result_item->sub_total;
        $result->total_cups             = $result_item->total_cups;
        $result->total_cups_free        = $result_item->total_cups_free;
        $result->total_cups_paid        = $result_item->total_cups_paid;
        $result->total_cups_pickup      = $result_item->total_cups_pickup;
        $result->total_cups_delivery    = $result_item->total_cups_delivery;
        $result->total_espresso_base    = $result_item->total_espresso_base;
        $result->total_tea_by_twg       = $result_item->total_tea_by_twg;
        $result->total_disc             = $result_order->total_disc_order + $result_item->total_disc_item;
        $result->disc_delivery_fee      = $result_order->disc_delivery_fee;
        $result->total                  = $result_order->total;
        $result->total_delivery_fee     = $result_order->total_delivery_fee;
        $result->grand_total            = $result_order->grand_total;
        $result->total_order            = $result_order->total_order;
        return $result;
    }
    //=== END SALES PER ORDER

    //=== START BALANCE PER USER
    function getall_balance_per_user($search, $from, $to, $order="", $limit=0)
    {
        $orderby    = ($order == "" ? " uor_id ASC " : $order);

        //set data untuk query
        $keyword = '%'.$search.'%';
        $where = " AND (usr.user_name LIKE ? OR usr.user_email LIKE ? OR usr.user_phone LIKE ? )";
        //masing2 data union all di enter supaya keliatan jelas
        $data = array($from, $keyword, $keyword, $keyword,
            'topup', $from, $to, $keyword, $keyword, $keyword,
            'cashback', $from, $to, $keyword, $keyword, $keyword,
            'order', $from, $to, $keyword, $keyword, $keyword,
            'refund', $from, $to, $keyword, $keyword, $keyword,
            'withdraw', $from, $to, $keyword, $keyword, $keyword,
        );
        // search dulu row nya.
        $sql        = "SELECT user_id, user_name, user_email, user_phone,
                                SUM(opening_balace) total_opening_balace ,
                                SUM(topup) total_topup,
                                SUM(cashback) total_cashback,
                                SUM(transaction) total_transaction,
                                SUM(refund) total_refund,
                                SUM(withdraw) total_withdraw,
                                SUM(opening_balace + topup + cashback + transaction + refund + withdraw) total_closing_balance
                        FROM (

                            SELECT usr.user_id, usr.user_name, usr.user_email, usr.user_phone,
                                SUM(uwhis.uwhis_nominal) as opening_balace, 0 AS topup, 0 as cashback, 0 as transaction, 0 as refund, 0 as withdraw
                            FROM ". $this->tbl_wallet_history ." uwhis
                            INNER JOIN ". $this->tbl_user ." usr ON usr.user_id = uwhis.user_id
                            WHERE uwhis.created_date < ? ". $where ."  GROUP BY usr.user_id
                            HAVING opening_balace > 0

                            UNION ALL

                            SELECT usr.user_id, usr.user_name, usr.user_email, usr.user_phone,
                                0 as opening_balace, uwhis.uwhis_nominal AS topup, 0 as cashback, 0 as transaction, 0 as refund, 0 as withdraw
                            FROM ". $this->tbl_wallet_history ." uwhis
                            INNER JOIN ". $this->tbl_user ." usr ON usr.user_id = uwhis.user_id
                            WHERE uwhis.uwhis_type = ? AND uwhis.created_date >= ? AND uwhis.created_date <= ? ". $where ."

                            UNION ALL

                            SELECT usr.user_id, usr.user_name, usr.user_email, usr.user_phone,
                                0 as opening_balace, 0 AS topup, uwhis.uwhis_nominal as cashback, 0 as transaction, 0 as refund, 0 as withdraw
                            FROM ". $this->tbl_wallet_history ." uwhis
                            INNER JOIN ". $this->tbl_user ." usr ON usr.user_id = uwhis.user_id
                            WHERE uwhis.uwhis_type = ? AND uwhis.created_date >= ? AND uwhis.created_date <= ? ". $where ."

                            UNION ALL

                            SELECT usr.user_id, usr.user_name, usr.user_email, usr.user_phone,
                                0 as opening_balace, 0 AS topup, 0 as cashback, uwhis.uwhis_nominal as transaction, 0 as refund, 0 as withdraw
                            FROM ". $this->tbl_wallet_history ." uwhis
                            INNER JOIN ". $this->tbl_user ." usr ON usr.user_id = uwhis.user_id
                            WHERE uwhis.uwhis_type = ? AND uwhis.created_date >= ? AND uwhis.created_date <= ? ". $where ."

                            UNION ALL

                            SELECT usr.user_id, usr.user_name, usr.user_email, usr.user_phone,
                                0 as opening_balace, 0 AS topup, 0 as cashback, 0 as transaction, uwhis.uwhis_nominal as refund, 0 as withdraw
                            FROM ". $this->tbl_wallet_history ." uwhis
                            INNER JOIN ". $this->tbl_user ." usr ON usr.user_id = uwhis.user_id
                            WHERE uwhis.uwhis_type = ? AND uwhis.created_date >= ? AND uwhis.created_date <= ? ". $where ."

                            UNION ALL

                            SELECT usr.user_id, usr.user_name, usr.user_email, usr.user_phone,
                                0 as opening_balace, 0 AS topup, 0 as cashback, 0 as transaction, 0 as refund, uwhis.uwhis_nominal as withdraw
                            FROM ". $this->tbl_wallet_history ." uwhis
                            INNER JOIN ". $this->tbl_user ." usr ON usr.user_id = uwhis.user_id
                            WHERE uwhis.uwhis_type = ? AND uwhis.created_date >= ? AND uwhis.created_date <= ? ". $where ."


                        )balance
                        GROUP BY user_id, user_name, user_email, user_phone ORDER BY ".$orderby;

        if($limit > 0){
            $data[] = $limit;
            $sql    .= " LIMIT ? ";
        }
        $query      = $this->_database_clone->query($sql, $data);

        return $query->result();
    }


    function getpaging_balance_per_user($search, $from, $to, $order="", $page=1)
    {
        $page       = ($page <= 0 ? 1 : intval($page));
        $last_row   = $this->row_per_page * $page;
        $first_row  = $last_row - $this->row_per_page;

        $orderby    = ($order == "" ? " uor_id ASC " : $order);

        //set data untuk query
        $keyword = '%'.$search.'%';
        $where = " AND (usr.user_name LIKE ? OR usr.user_email LIKE ? OR usr.user_phone LIKE ? )";
        //masing2 data union all di enter supaya keliatan jelas
        $data = array($from, $keyword, $keyword, $keyword,
            'topup', $from, $to, $keyword, $keyword, $keyword,
            'cashback', $from, $to, $keyword, $keyword, $keyword,
            'order', $from, $to, $keyword, $keyword, $keyword,
            'refund', $from, $to, $keyword, $keyword, $keyword,
            'withdraw', $from, $to, $keyword, $keyword, $keyword,
        );

        // search dulu row nya.
        $sql        = "SELECT user_id, user_name, user_email, user_phone,
                                SUM(opening_balace) total_opening_balace ,
                                SUM(topup) total_topup,
                                SUM(cashback) total_cashback,
                                SUM(transaction) total_transaction,
                                SUM(refund) total_refund,
                                SUM(withdraw) total_withdraw,
                                SUM(opening_balace + topup + cashback + transaction + refund + withdraw) total_closing_balance
                        FROM (

                            SELECT usr.user_id, usr.user_name, usr.user_email, usr.user_phone,
                                SUM(uwhis.uwhis_nominal) as opening_balace, 0 AS topup, 0 as cashback, 0 as transaction, 0 as refund, 0 as withdraw
                            FROM ". $this->tbl_wallet_history ." uwhis
                            INNER JOIN ". $this->tbl_user ." usr ON usr.user_id = uwhis.user_id
                            WHERE uwhis.created_date < ? ". $where ."  GROUP BY usr.user_id
                            HAVING opening_balace > 0

                            UNION ALL

                            SELECT usr.user_id, usr.user_name, usr.user_email, usr.user_phone,
                                0 as opening_balace, uwhis.uwhis_nominal AS topup, 0 as cashback, 0 as transaction, 0 as refund, 0 as withdraw
                            FROM ". $this->tbl_wallet_history ." uwhis
                            INNER JOIN ". $this->tbl_user ." usr ON usr.user_id = uwhis.user_id
                            WHERE uwhis.uwhis_type = ? AND uwhis.created_date >= ? AND uwhis.created_date <= ? ". $where ."

                            UNION ALL

                            SELECT usr.user_id, usr.user_name, usr.user_email, usr.user_phone,
                                0 as opening_balace, 0 AS topup, uwhis.uwhis_nominal as cashback, 0 as transaction, 0 as refund, 0 as withdraw
                            FROM ". $this->tbl_wallet_history ." uwhis
                            INNER JOIN ". $this->tbl_user ." usr ON usr.user_id = uwhis.user_id
                            WHERE uwhis.uwhis_type = ? AND uwhis.created_date >= ? AND uwhis.created_date <= ? ". $where ."

                            UNION ALL

                            SELECT usr.user_id, usr.user_name, usr.user_email, usr.user_phone,
                                0 as opening_balace, 0 AS topup, 0 as cashback, uwhis.uwhis_nominal as transaction, 0 as refund, 0 as withdraw
                            FROM ". $this->tbl_wallet_history ." uwhis
                            INNER JOIN ". $this->tbl_user ." usr ON usr.user_id = uwhis.user_id
                            WHERE uwhis.uwhis_type = ? AND uwhis.created_date >= ? AND uwhis.created_date <= ? ". $where ."

                            UNION ALL

                            SELECT usr.user_id, usr.user_name, usr.user_email, usr.user_phone,
                                0 as opening_balace, 0 AS topup, 0 as cashback, 0 as transaction, uwhis.uwhis_nominal as refund, 0 as withdraw
                            FROM ". $this->tbl_wallet_history ." uwhis
                            INNER JOIN ". $this->tbl_user ." usr ON usr.user_id = uwhis.user_id
                            WHERE uwhis.uwhis_type = ? AND uwhis.created_date >= ? AND uwhis.created_date <= ? ". $where ."

                            UNION ALL

                            SELECT usr.user_id, usr.user_name, usr.user_email, usr.user_phone,
                                0 as opening_balace, 0 AS topup, 0 as cashback, 0 as transaction, 0 as refund, uwhis.uwhis_nominal as withdraw
                            FROM ". $this->tbl_wallet_history ." uwhis
                            INNER JOIN ". $this->tbl_user ." usr ON usr.user_id = uwhis.user_id
                            WHERE uwhis.uwhis_type = ? AND uwhis.created_date >= ? AND uwhis.created_date <= ? ". $where ."

                        )balance
                        GROUP BY user_id, user_name, user_email, user_phone ORDER BY ".$orderby;

        $query      = $this->_database_clone->query($sql, $data);

        $total_row  = $query->num_rows();

        $data[] = $this->row_per_page;
        $data[] = intval($first_row > $total_row ? $total_row : $first_row);
        $sql        .= " LIMIT ? OFFSET ? ";
        $query      = $this->_database_clone->query($sql, $data);

        $result['data']         = $query->result();
        $result['total_row']    = $total_row;
        $result['perpage']      = $this->row_per_page;
        return $result;
    }
    //=== END BALANCE PER USER

    //=== START REPORT MONTHLY

    function get_total_complete($data)
    {
        $from           = $data['from_date'];
        $to             = $data['to_date'];
        $status         = $data['status'];
        $sql_data       = [$from, $to, $status];
        $where          = '';

        if(isset($data['delivery_type'])){
            $where      = " AND uor_delivery_type = ? ";
            $sql_data[] = $data['delivery_type'];
        }

        $sql    = "SELECT COUNT(*) as total
                    FROM {$this->tbl_order}
                    WHERE created_date >= ? AND created_date <= ? AND uor_status = ? {$where}";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }

    //=== User
    function get_total_user($data)
    {
        $to         = $data['to_date'];
        $sql_data   = [$to];

        $sql    = "SELECT COUNT(*) as total
                    FROM {$this->tbl_user}
                    WHERE created_date <= ? ";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }

    function get_total_user_not_order($data)
    {
        $to         = $data['to_date'];
        $status     = $this->config->item('order')['status']['completed'];
        $sql_data   = [$status, $to, $to];

        $sql    = "SELECT COUNT(*) as total
                    FROM {$this->tbl_user}
                    WHERE user_id NOT IN (
                        SELECT user_id
                        FROM {$this->tbl_order}
                        WHERE uor_status =  ? AND uor_date <= ? )
                    AND created_date <= ? ";

        $query = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }

    function get_total_user_topup_month($data)
    {
        $negation   = ($data['negation'] ? '' : 'NOT');
        $from       = $data['from_date'];
        $to         = $data['to_date'];
        $status     = $this->config->item('order')['status']['completed'];
        $sql_data   = [$status, 0, $from, $to, $from, $to];

        $sql    = "SELECT COUNT(DISTINCT user_id ) total
                    FROM {$this->tbl_user_topup}
                    WHERE user_id {$negation} IN (
                        SELECT user_id
                        FROM {$this->tbl_order}
                        WHERE uor_status = ?
                        AND uor_total > ?
                        AND created_date >= ? AND created_date <= ?
                    ) AND utop_date >= ? AND utop_date <= ? ";

        $query = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }

    function get_total_user_have_balance_in_end_month($data)
    {
        $from       = $data['from_date'];
        $to         = $data['to_date'];
        $sql_data   = [$to, 0, $from, $to];

        $sql    = "SELECT COUNT(*) as total
                    FROM {$this->tbl_user_topup}
                    WHERE user_id IN (
                        SELECT user_id
                        FROM {$this->tbl_wallet_history}
                        WHERE created_date <= ?
                        GROUP BY user_id
                        HAVING SUM(uwhis_nominal) > ?
                    ) AND utop_date >= ? AND utop_date <= ? ";

        $query = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }

    //=== Referral
    function get_total_user_referral($data)
    {
        $from       = $data['from_date'];
        $to         = $data['to_date'];
        $sql_data   = [$from, $to];

        $sql    = "SELECT COUNT(*) as total
                    FROM {$this->tbl_user_referral}
                    WHERE created_date >= ? AND created_date <= ? ";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }

    function get_total_user_referral_claim($data)
    {
        $negation     = ($data['negation'] ? '' : 'NOT');
        $status       = $this->config->item('order')['status']['completed'];
        $voucher_code = $this->config->item('promo')['promo_code']['reg'];
        $to           = $data['to_date'];
        $sql_data     = [$to, $status, $voucher_code.'%', $to];

        $sql    = "SELECT COUNT(*) as total
                    FROM {$this->tbl_user_referral}
                    WHERE created_date <= ?
                    AND uref_to {$negation} IN (
                        SELECT user_id
                        FROM {$this->tbl_order}
                        WHERE uor_status = ?
                        AND uor_vc_code LIKE ? AND uor_date <= ? )";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }

    function get_total_user_referral_claim_repeat($data)
    {
        $negation     = ($data['negation'] ? '' : 'NOT');
        $status       = $this->config->item('order')['status']['completed'];
        $voucher_code = $this->config->item('promo')['promo_code']['reg'];
        $to           = $data['to_date'];
        $sql_data     = [$to, $status, $voucher_code.'%', $status, $voucher_code.'%', $to];

        $sql    = "SELECT COUNT(*) total
                    FROM {$this->tbl_order}
                    WHERE uor_date <= ?
                    AND uor_status = ?
                    AND uor_vc_code LIKE ?
                    AND user_id {$negation} IN (
                        SELECT user_id
                        FROM {$this->tbl_order}
                        WHERE uor_status = ?
                        AND uor_vc_code NOT LIKE ? AND uor_date <= ?
                    )
                    AND user_id IN (SELECT uref_to FROM {$this->tbl_user_referral})";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }

    function get_total_user_referral_not_free($data)
    {
        $status       = $this->config->item('order')['status']['completed'];
        $voucher_code = $this->config->item('promo')['promo_code']['reg'];
        $to           = $data['to_date'];
        $sql_data     = [$to, $status, $voucher_code.'%', $status, $voucher_code.'%', $to];

        $sql    = "SELECT COUNT(*) total
                    FROM {$this->tbl_order}
                    WHERE uor_date <= ?
                    AND uor_status = ?
                    AND uor_vc_code NOT LIKE ?
                    AND user_id NOT IN (
                        SELECT user_id
                        FROM {$this->tbl_order}
                        WHERE uor_status = ?
                        AND uor_vc_code LIKE ?
                        AND uor_date <= ?
                    )
                    AND user_id IN (SELECT uref_to FROM {$this->tbl_user_referral})";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }

    //=== Claim
    function get_total_claim_free($data)
    {
        $status       = $this->config->item('order')['status']['completed'];
        $voucher_code = $this->config->item('promo')['promo_code']['reg'];
        $to           = $data['to_date'];
        $sql_data     = [$to, $status, $voucher_code.'%'];

        $sql    = "SELECT COUNT(*) as total
                    FROM {$this->tbl_order}
                    WHERE uor_date <= ?
                    AND uor_status = ?
                    AND uor_vc_code LIKE ?";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }

    function get_total_claim_free_repeat($data)
    {
        $negation     = ($data['negation'] ? '' : 'NOT');
        $status       = $this->config->item('order')['status']['completed'];
        $voucher_code = $this->config->item('promo')['promo_code']['reg'];
        $to           = $data['to_date'];
        $sql_data     = [$to, $status, $voucher_code.'%', $status, $voucher_code.'%', $to];

        $sql    = "SELECT COUNT(*) as total
                    FROM {$this->tbl_order}
                    WHERE uor_date <= ?
                    AND uor_status = ?
                    AND uor_vc_code LIKE ?
                    AND user_id {$negation} IN (
                        SELECT user_id
                        FROM {$this->tbl_order}
                        WHERE uor_status = ?
                        AND uor_vc_code NOT LIKE ?
                        AND uor_date <= ? )";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }

    function get_not_claim_free($data, $order = " ")
    {
        $status       = $this->config->item('order')['status']['completed'];
        $voucher_code = $this->config->item('promo')['promo_code']['reg'];
        $from         = $data['from_date'];
        $to           = $data['to_date'];
        $sql_data     = [$from, $to, $status, $from, $to, $voucher_code.'%'];

        $sql    = "SELECT COUNT(*) as total
                    FROM {$this->tbl_order}
                    WHERE uor_id IN (
                        SELECT MIN(uor.uor_id) AS uor_id
                        FROM {$this->tbl_user} usr
                        INNER JOIN {$this->tbl_order} uor
                        ON uor.user_id = usr.user_id
                        WHERE usr.created_date >= ? AND usr.created_date <= ?
                        AND uor_status = ?
                        AND uor_date >= ? AND uor_date <= ?
                        GROUP BY uor.user_id)
                    AND uor_vc_code NOT LIKE ? ";

        $query = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }

    //=== Topup
    function get_total_user_topup($data)
    {
        $from         = $data['from_date'];
        $to           = $data['to_date'];
        $sql_data     = [$from, $to];

        $sql    = "SELECT SUM(utop_nominal) as total
                    FROM {$this->tbl_user_topup}
                    WHERE created_date >= ? AND created_date <= ? ";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }

    //===  DOWNLOAD
    function get_total_download_apps($data){
        $from         = $data['from_date'];
        $to           = $data['to_date'];
        $sql_data     = [$from, $to];
        $where        = '';

        if(isset($data['usrd_type'])){
            $where      = ' AND usrd_type = ? ';
            $sql_data[] = $data['usrd_type'];
        }

        $sql    = "SELECT IFNULL(SUM(usrd_total), 0) as total
                    FROM {$this->tbl_user_download}
                    WHERE usrd_date >= ? AND usrd_date <= ? {$where}";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }

    //=== END REPORT MONTHLY

    //=== START DASHBOARD
    function get_total_order($data)
    {
        $uor_date = $data['date'];
        $status   = $data['status'];
        $where    = '';
        $sql_data = [$uor_date, $status];

        if(isset($data['delivery_type'])){
            $where .= " AND uor_delivery_type = ? ";
            $sql_data[] = $data['delivery_type'];
        }
        if(isset($data['st_id'])){
            $where      .= "AND st.st_id = ? ";
            $sql_data[] = $data['st_id'];
        }

        $sql    = "SELECT COUNT(uor.uor_id) as total
                    FROM {$this->tbl_order} uor
                    INNER JOIN {$this->tbl_user} usr ON usr.user_id = uor.user_id
                    INNER JOIN {$this->tbl_order_address} uoradd ON uoradd.uor_id = uor.uor_id
                    INNER JOIN {$this->tbl_store} st ON st.st_id = uoradd.st_id
                    WHERE 1=1 AND uor.uor_date LIKE ? AND uor.uor_status = ? {$where}
                    ORDER BY uor.uor_date ASC ";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }
    //=== END DASHBOARD

    //=== START COHORT
    function get_total_transaction($data)
    {
        $status     = $this->config->item('order')['status']['completed'];
        $to         = $data['to_date'];
        $sql_data   = [$to, $status];

        $sql    = "SELECT user_id, COUNT(*) AS total_transaction
                    FROM {$this->tbl_order}
                    WHERE uor_date <= ?
                    AND uor_status = ? GROUP BY user_id";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->result();
    }

    function get_cohort_data($data)
    {
        $status     = $this->config->item('order')['status']['completed'];
        $to         = $data['to_date'];
        $sql_data   = [$to, $status, $to, $status];

        $sql    = "SELECT
                    user_id,
                    MIN(transaction_date) as trx_date,
                    SUM(transaction_amount) as trx_amount,
                    cohort_month,
                    cohort_period
                FROM (

                    SELECT
                        ord.user_id,
                        DATE(ord.uor_date) transaction_date,
                        SUM(ordpd.uorpd_total) transaction_amount,
                        DATE_FORMAT(ordmin.first_purchase, '%Y-%m') AS cohort_month,
                        (12 * (YEAR(ord.uor_date) - YEAR(ordmin.first_purchase)) + (MONTH(ord.uor_date) - MONTH(ordmin.first_purchase)) ) + 1 AS cohort_period

                    FROM {$this->tbl_order} ord
                    INNER JOIN (
                        SELECT DATE(MIN(uor_date)) first_purchase, user_id
                        FROM {$this->tbl_order}
                        WHERE uor_date <= ?
                        AND uor_status = ?
                        GROUP BY user_id
                    )ordmin ON ordmin.user_id = ord.user_id
                    INNER JOIN {$this->tbl_order_product} ordpd ON ordpd.uor_id = ord.uor_id
                    WHERE uor_date <= ?
                    AND ord.uor_status = ?
                    GROUP BY ord.uor_id
                )cohort_data

                GROUP BY cohort_period, user_id
                ORDER BY trx_date, user_id ";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->result();
    }

    function get_cohort_topup_user($data)
    {
        $his_type   = $this->config->item('wallet')['history_type']['topup'];
        $to         = $data['to_date'];
        $sql_data   = [$his_type, $to, $his_type, $to];

        $sql = "SELECT
                    user_topup.user_id,
                    DATE(user_topup.utop_date) topup_date,
                    user_topup.utop_nominal topup_amount,
                    DATE_FORMAT(tumin.first_purchase, '%Y-%m') AS cohort_month,
                    (12 * (YEAR(user_topup.utop_date) - YEAR(tumin.first_purchase)) + (MONTH(user_topup.utop_date) - MONTH(tumin.first_purchase)) ) + 1 AS cohort_period

                FROM {$this->tbl_user_topup}
                INNER JOIN (
                    SELECT DATE(MIN(utop_date)) first_purchase, user_topup.user_id
                    FROM {$this->tbl_user_topup}
                    INNER JOIN {$this->tbl_wallet_history} walhis ON walhis.uwhis_primary = user_topup.utop_id AND walhis.uwhis_type = ?
                    WHERE utop_date <= ?
                    GROUP BY user_topup.user_id
                )tumin ON tumin.user_id = user_topup.user_id
                INNER JOIN {$this->tbl_wallet_history} walhis ON walhis.uwhis_primary = user_topup.utop_id AND walhis.uwhis_type = ?
                WHERE user_topup.utop_date <= ?
                ORDER BY cohort_month ASC, cohort_period ASC ";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->result();
    }

    function get_cohort_referral($data)
    {
        $to         = $data['to_date'];
        $sql_data   = [$to, $to];

        $sql    = "SELECT
                    ord.uref_from,
                    DATE(ord.created_date) refer_date,
                    DATE_FORMAT(ordmin.first_purchase, '%Y-%m') AS cohort_month,
                    (12 * (YEAR(ord.created_date) - YEAR(ordmin.first_purchase)) + (MONTH(ord.created_date) - MONTH(ordmin.first_purchase)) ) + 1 AS cohort_period

                    FROM {$this->tbl_user_referral} ord
                    INNER JOIN (
                        SELECT DATE(MIN(created_date)) first_purchase, uref_from
                        FROM {$this->tbl_user_referral}
                        WHERE created_date <= ?
                        GROUP BY uref_from
                    )ordmin ON ordmin.uref_from = ord.uref_from
                    WHERE created_date <= ?
                    ORDER BY cohort_month ASC, cohort_period ASC ";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->result();
    }

    function get_cohort_cups($data)
    {
        $status         = $this->config->item('order')['status']['completed'];
        $to             = $data['to_date'];
        $uorpd_is_free  = $data['uorpd_is_free'];
        $sql_data       = [$to, $status, $uorpd_is_free, $to, $status, $uorpd_is_free];

        $sql = "SELECT
                    user_id,
                    MIN(transaction_date) as trx_date,
                    SUM(transaction_amount) as trx_amount,
                    cohort_month,
                    cohort_period
                FROM (
                    SELECT
                        ord.user_id,
                        DATE(ord.uor_date) transaction_date,
                        SUM(ordpd.uorpd_total) transaction_amount,
                        DATE_FORMAT(ordmin.first_purchase, '%Y-%m') AS cohort_month,
                        (12 * (YEAR(ord.uor_date) - YEAR(ordmin.first_purchase)) + (MONTH(ord.uor_date) - MONTH(ordmin.first_purchase)) ) + 1 AS cohort_period

                    FROM {$this->tbl_order} ord
                    INNER JOIN (
                        SELECT DATE(MIN(uor_date)) first_purchase, user_id
                        FROM {$this->tbl_order} ord
                        INNER JOIN {$this->tbl_order_product} ordpd ON ordpd.uor_id = ord.uor_id
                        WHERE ord.uor_date <= ?
                        AND ord.uor_status = ?
                        AND ordpd.uorpd_is_free = ?
                        GROUP BY ord.user_id
                    )ordmin ON ordmin.user_id = ord.user_id
                    INNER JOIN {$this->tbl_order_product} ordpd ON ordpd.uor_id = ord.uor_id
                    WHERE ord.uor_date <= ?
                    AND ord.uor_status = ?
                    AND ordpd.uorpd_is_free = ?
                    GROUP BY ord.uor_id
                )cohort_data
                GROUP BY cohort_period, user_id
                ORDER BY trx_date, user_id ";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->result();
    }

    function get_cohort_voucher_complimentary($data)
    {
        $promo_code         = $this->config->item('promo')['promo_code']['free'];
        $voucher_status     = $this->config->item('voucher')['status']['used'];
        $to                 = $data['to_date'];
        $sql_data           = [$to, $promo_code.'%', $to, $promo_code.'%', $voucher_status];

        $sql    = "SELECT date, SUM(total_issued) AS total_issued, SUM(total_used) AS total_used
                    FROM (
                        SELECT DATE_FORMAT(vc.created_date, '%Y-%m') date, COUNT(*) total_issued, 0 total_used
                        FROM {$this->tbl_promo} prm
                        INNER JOIN {$this->tbl_voucher} vc ON vc.prm_id = prm.prm_id
                        WHERE vc.created_date <= ?
                        AND prm.prm_custom_code LIKE ?
                        GROUP BY DATE_FORMAT(vc.created_date, '%Y-%m')

                        UNION ALL

                        SELECT DATE_FORMAT(vc.updated_date, '%Y-%m') date, 0 total_issued, COUNT(*) total_used
                        FROM {$this->tbl_promo} prm
                        INNER JOIN {$this->tbl_voucher} vc ON vc.prm_id = prm.prm_id
                        WHERE vc.updated_date <= ?
                        AND prm.prm_custom_code LIKE ?
                        AND vc.vc_status = ?
                        GROUP BY DATE_FORMAT(vc.updated_date, '%Y-%m')
                    ) voucher
                    GROUP BY date";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->result();
    }

    function get_all_product_cogs_by_date($where = "", $data = []) {
        $newdata = array();
        
        $sql        = "SELECT cogs.*
                        FROM {$this->tbl_product_cogs} cogs
                        WHERE 1=1 {$where}";

        $query      = $this->_database_clone->query($sql, $data);
        $result = $query->result();

        foreach($result as $key => $data) {
            $date = date("Y-m-d", strtotime($data->cogs_date));
            $newdata[$data->pd_id][$date] = $data->pdcogs_price;
        }

        return $newdata;
    }
    //=== END COHORT

    function get_total_report_summary($where = '', $data = array()){  
        
        $subsplan_id = $data['subsplan_id'];
        $status      = $data['subs_status'];
        $from        = $data['from'];
        $to          = $data['to'];
        $sql_data    = [$from, $to, $subsplan_id, $status];

        $sql    = "SELECT subsplan.subsplan_name,
                        COUNT(suo.subsorder_id) as total_order, 
                        SUM(IFNULL(suo.subsorder_total, 0)) as total_price
                    FROM {$this->tbl_subs_order} suo
                    INNER JOIN {$this->tbl_subs_counter} sc ON sc.subsorder_id = suo.subsorder_id
                    INNER JOIN {$this->tbl_subs_plan} subsplan ON subsplan.subsplan_id = sc.subsplan_id
                    WHERE 1=1 {$where} AND subsplan.subsplan_id = ? AND suo.subsorder_status LIKE ? ";
        
        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }

    function get_total_subscriber($where = '', $data = array()){
        
        $subsplan_id = $data['subsplan_id'];
        $from        = $data['from'];
        $to          = $data['to'];
        $sql_data    = [$from, $to, $subsplan_id];

        $sql    = "SELECT COUNT(subs.user_id) as total_subs FROM (
                    SELECT suo.user_id FROM {$this->tbl_subs_counter} sc 
                    INNER JOIN {$this->tbl_subs_order} suo ON suo.subsorder_id = sc.subsorder_id 
                    WHERE 1=1 {$where} AND subsplan_id = ? GROUP BY user_id) subs";
        
        $query = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }

    function get_total_voucher($where = '', $data = array()){

        $from          = $data['from'];
        $to            = $data['to'];
        $today         = $data['today'];
        $status        = $data['status'];
        $vc_status     = $data['vc_status'];
        $prm_code      = $data['prm_code'];

        if($status == 'used'){
            $sql_data      = [$vc_status, $prm_code, $from, $to];
        }else if($status == 'expired' || $status == 'active'){
            $prm_status    = $data['prm_status'];
            $sql_data      = [$vc_status, $prm_code, $from, $to, $prm_status];
        }
        
        $sql    = "SELECT COUNT(vc_id) as total_voucher FROM {$this->tbl_voucher} vc 
                    INNER JOIN {$this->tbl_promo} prm ON prm.prm_id = vc.prm_id 
                    WHERE 1=1 
                    AND vc.vc_status = ? 
                    AND prm.prm_custom_code LIKE ? {$where}"; 
        
        $query = $this->_database_clone->query($sql, $sql_data);
        return $query->row();
    }

    function get_store_out_of_stock($data)
    {
        $status   = $data['stpd_status'];
        $where    = '';
        $sql_data = [$status];

        if(isset($data['st_id'])){
            $where      .= "AND st.st_id = ? ";
            $sql_data[] = $data['st_id'];
        }

        $sql    = "SELECT st.st_id, st.st_name 
                    FROM {$this->tbl_store_product} stpd 
                    INNER JOIN {$this->tbl_store} st ON stpd.st_id = st.st_id 
                    WHERE 1=1 AND stpd_status = ? {$where} 
                    GROUP BY st.st_id";

        $query  = $this->_database_clone->query($sql, $sql_data);
        return $query->result();
    }
}
?>
