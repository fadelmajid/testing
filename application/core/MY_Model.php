<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Model extends CI_Model {
    function __construct()
    {
        parent::__construct();

        //=== BEGIN TABLE NAMES

        $this->tbl_app_version              = 'app_version';
        $this->tbl_setup_admin              = 'setup_admin';
        $this->tbl_setup_admin_invalidlogin = 'setup_admin_invalidlogin';
        $this->tbl_setup_admin_menu         = 'setup_admin_menu';
        $this->tbl_setup_menu               = 'setup_menu';
        $this->tbl_setup_menu_sub           = 'setup_menu_sub';
        $this->tbl_setup_role               = 'setup_role';
        $this->tbl_setup_role_menu          = 'setup_role_menu';
        $this->tbl_product                  = 'product';
        $this->tbl_partner                  = 'partner';
        $this->tbl_partner_promo            = 'partner_promo';
        $this->tbl_category                 = 'category';
        $this->tbl_store                    = 'store';
        $this->tbl_store_product            = 'store_product';
        $this->tbl_order                    = 'user_order';
        $this->tbl_order_address            = 'user_order_address';
        $this->tbl_order_product            = 'user_order_product';
        $this->tbl_order_track              = 'user_order_track';
        $this->tbl_user                     = 'user';
        $this->tbl_user_address             = 'user_address';
        $this->tbl_user_referral            = 'user_referral';
        $this->tbl_user_withdraw            = 'user_withdraw';
        $this->tbl_wallet                   = 'user_wallet';
        $this->tbl_wallet_history           = 'user_wallet_history';
        $this->tbl_user_topup               = 'user_topup';
        $this->tbl_store                    = 'store';
        $this->tbl_promo                    = 'promo';
        $this->tbl_voucher                  = 'voucher';
        $this->tbl_voucher_history          = 'voucher_history';
        $this->tbl_lock_transaction         = 'lock_transaction';
        $this->tbl_province                 = 'province';
        $this->tbl_city                     = 'city';
        $this->tbl_static_faq               = 'static_faq';
        $this->tbl_gosend_logs              = 'gosend_logs';
        $this->tbl_sicepat_logs             = 'sicepat_logs';
        $this->tbl_user_order_courier       = 'user_order_courier';
        $this->tbl_bank                     = 'bank';
        $this->tbl_auth_code                = 'auth_code';
        $this->tbl_auth_sms                 = 'auth_code_sms';
        $this->tbl_auth_token               = 'auth_token';
        $this->tbl_user_pushtoken           = 'user_pushtoken';
        $this->tbl_user_order_payment       = 'user_order_payment';
        $this->tbl_user_email_token         = 'user_email_token';
        $this->tbl_payment_method           = 'payment_method';
        $this->tbl_payment_logs             = 'payment_logs';
        $this->tbl_user_va                  = 'user_va';
        $this->tbl_voucher_default          = 'voucher_default';
        $this->tbl_static_image             = 'static_image';
        $this->tbl_banner                   = 'banner';
        $this->tbl_user_emoney              = "user_emoney";
        $this->tbl_history_pushtoken        = "history_pushtoken";
        $this->tbl_history_device           = "history_device";
        $this->tbl_courier                  = "courier";
        $this->tbl_voucher_unassigned       = "voucher_unassigned";
        $this->tbl_voucher_birthday         = "voucher_birthday";
        $this->tbl_product_cogs             = 'product_cogs';
        $this->tbl_store_operational        = "store_operational";
        $this->tbl_subs_plan                = "subs_plan";
        $this->tbl_subs_counter             = "subs_counter";
        $this->tbl_subs_order               = "subs_order";
        $this->tbl_subs_order_detail        = "subs_order_detail";
        $this->tbl_grab_logs                = 'grab_logs';
        $this->tbl_user_dana                = 'user_dana';
        $this->tbl_user_download            = 'user_download';
        $this->tbl_setup_barista            = 'setup_barista';
        $this->tbl_subs_order               = "subs_order";
        $this->tbl_subs_order_detail        = "subs_order_detail";
        $this->tbl_subs_order_payment       = "subs_order_payment";
        $this->tbl_campaign_token           = 'campaign_token';
        $this->tbl_subs_payment_logs        = 'subs_payment_logs';
        $this->tbl_user_order_voucher       = 'user_order_voucher';
        $this->tbl_triplogic_logs           = 'triplogic_logs';
        $this->tbl_store_image              = "store_image";
        $this->tbl_voucher_employee         = 'voucher_employee';
        $this->tbl_dana_logs                = 'dana_logs';
        $this->tbl_store_constant           = "store_constant";
        $this->tbl_store_config             = "store_config";
        $this->tbl_banner_catalogue         = 'banner_catalogue';
        //=== END TABLE NAMES


        //=== BEGIN VARIABLES
        $this->row_per_page = PAGINATION_PER_PAGE;
        //=== END VARIABLES
    }

    function set_row_per_page($row)
    {
        $this->row_per_page = $row;
    }
}
