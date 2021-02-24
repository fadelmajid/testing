<?php
//untuk keperluan log4php
$config['log4php_config']['rootLogger']['appenders']        = array('default');
$config['log4php_config']['appenders']['default']['class']  = 'LoggerAppenderNull';

//font-color-array
$config['arr_font_color'][0] = '#24D521';
$config['arr_font_color'][1] = '#D52175';
$config['arr_font_color'][2] = '#2136D5';
$config['arr_font_color'][3] = '#D55421';
$config['arr_font_color'][4] = '#21D5CF';
$config['arr_font_color'][5] = '#D57E21';
$config['arr_font_color'][6] = '#7B2158';
$config['arr_font_color'][7] = '#21257B';
$config['arr_font_color'][8] = '#217B75';
$config['arr_font_color'][9] = '#925B23';

//yes/no array
$config['arr_yes_no'][0] = 'No';
$config['arr_yes_no'][1] = 'Yes';

// user order payment
$config['payment'] = [
    'status' => [
        "payment_pending" => "pending",
        "payment_waiting" =>  "waiting_for_payment",
        "payment_paid" => "paid",
        "payment_cancel" => "cancelled",
        "payment_error" => "error"
    ],
    'allow_refund_payment' => [
        1 // pymtd_id wallet
    ],
    'allow_refund_status' => [
        'paid' => 'paid',
        'in_process' => 'in_process',
        'ready_for_pickup' => 'ready_for_pickup',
        'on_delivery' => 'on_delivery',
    ]
];

// order array
$config['order'] = [
    'action' => [
        'update' => 'update',
        'cancel' => 'cancel'
    ],
    'delivery_type' => [
        'pickup' => 'pickup',
        'delivery' => 'delivery',
    ],
    'payment_method' => [
        "wallet" => "1",
        "ovo"    => "2",
        "credit_card"  => "3",
        "gopay"    => "4"
    ],
    'payment_name' => [
        "1" => "wallet",
        "2" => "ovo",
        "3" => "credit_card",
        "4" => "gopay"
    ],
    'status' => [
        'waiting_for_payment' => 'waiting_for_payment',
        'paid' => 'paid',
        'in_process' => 'in_process',
        'ready_for_pickup' => 'ready_for_pickup',
        'on_delivery' => 'on_delivery',
        'completed' => 'completed',
        'cancelled' => 'cancelled'
    ],
    'next_status' => [
        'waiting_for_payment' => '',
        'paid' => 'in_process',
        'in_process' => 'ready_for_pickup',
        'ready_for_pickup' => 'completed',
        'on_delivery' => 'on_delivery',
        'completed' => '',
        'cancelled' => ''
    ],
    'status_name' => [
        'waiting_for_payment' => 'Waiting For Payment',
        'paid' => 'Paid',
        'in_process' => 'Brewing Now',
        'ready_for_pickup' => 'Ready for Pickup',
        'on_delivery' => 'On Delivery',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled'
    ],
    'allow_pickup_completed' => [
        'paid' => 'paid',
        'in_process' => 'in_process',
        'ready_for_pickup' => 'ready_for_pickup'
    ]
];

// promo array
$config['promo'] = [
    'type' => [
        'generated' => 'generated',
        'limited' => 'limited',
        'unlimited' => 'unlimited'
    ],
    'status' => [
        'pending' => 'pending',
        'active' => 'active',
        'inactive' => 'inactive',
        'expired' => 'expired',
    ],
    'visible' => [
        'show' => 'show',
        'hide' => 'hide'
    ],
    'discount_type' => [
        'nominal' => 'nominal',
        'percentage' => 'percentage',
        'freecup' => 'freecup'
    ],
    'item_type' => [
        'blacklist' => 'blacklist',
        'whitelist' => 'whitelist'
    ],
    'promo_code' => [
        "emp"   => "EMP",
        "ref"   => "REF",
        "free"  => "FREE",
        "reg"   => "REG",
        "review"   => "REVW",
        "birthday" => "BDAY",
        "gift"  => "GIFT",
        "subscription" => "SUBS",
        "tgif" => "TGIF",
        "brkh" => "BRKH"
    ],
    'delivery_type' => [
        'all'           => 'all',
        'pickup_only'   => 'pickup only',
        'delivery_only' => 'delivery only'
    ]
];

// voucher array
$config['voucher'] = [
    'status' => [
        'inactive' => 'inactive',
        'active' => 'active',
        'cancelled' => 'cancelled',
        'expired' => 'expired',
        'used' => 'used',
    ]
];

// wallet array
$config['wallet'] = [
    'history_type' => [
        'topup' => 'topup',
        'order' => 'order',
        'refund' => 'refund',
        'withdraw' => 'withdraw'
    ]
];

// auth array
$config['auth_token'] = [
    'status' => [
        'active' => 'active',
        'inactive' => 'inactive',
    ]
];

// user array
$config['user'] = [
    'status' => [
        'active' => 'active',
        'inactive' => 'inactive',
    ]
];

// product array
$config['product'] = [
    'status' => [
        'active' => 'active',
        'inactive' => 'inactive',
    ]
];

// store array
$config['store'] = [
    'status' => [
        'active' => 'active',
        'inactive' => 'inactive',
    ],
    'type' => [
        'all' => 'all',
        'pickup_only' => 'pickup_only',
        'delivery_only' => 'delivery_only'
    ],
    'is_visible' => [
        'show' => 'show',
        'hide' => 'hide'
    ],
    'st_concept' => [
        'store'         => 'Store',
        'leisure'       => 'Leisure',
        'alpha_go'       => 'Alpha Go',
        'grab_and_go'   => 'Grab & Go'
    ]
];

$config['store_product'] = [
    'status' => [
        'active' => 'Active',
        'out_of_stock' => 'Out of stock',
        'inactive' => 'Inactive',
    ],
    'storepd_status' => [
        'active' => 'active',
        'out_of_stock' => 'out_of_stock',
        'inactive' => 'inactive',
    ]
];

// order track message array
// Jangan dirubah urutan message ini
// karena ada hubungannya dengan webhook gosend
$config['order_track'] = [
    'message' => [
        'We are doing a courier search',
        'We got a courier for you',
        'Courier on the way to pick up',
        'Courier on the way to you',
        'Courier has arrived',
        'Driver has cancelled the order, we will find another driver soon',
        'Driver is not available right now, we will find another one'
    ]
];

$config['uor_remarks'] = [
    'note' => [
        'no_driver' => 'Driver is not available right now'
    ]
];

// gosend array
$config['gosend'] = [
    'status' => [
        'confirmed' => 'confirmed',
        'out_for_pickup' => 'out_for_pickup',
        'out_for_delivery' => 'out_for_delivery',
        'no_driver' => 'no_driver',
        'delivered' => 'delivered',
        'cancelled' => 'cancelled',
        'rebooking' => 'rebooking'
    ],
    'type' => [
        "confirmed" => "confirmed",
        "booking" => "booking",
        "pickup" => "pickup",
        "drop" => "drop",
        "completed" => "completed",
        "cancel" => "cancel",
        "price" => "price",
        'no_driver' => 'no_driver'
    ]
];

// Appversion array
$config['appversion'] = [
    'status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'unreleased' => 'Unreleased'
    ],
    'platform' => [
        'android' => 'Android',
        'ios' => 'IOS'
    ]
];

// User topup array
$config['user_topup'] = [
    'status' => [
        'accepted' => 'accepted',
    ],
    'payment' => [
        'cash' => 'cash',
    ],
    'limit' => [
        'max'       => 1000000,
        'average'   => 500000
    ],
    'nominal' => [
        '50000'  => '50000',
        '100000' => '100000',
        '150000' => '150000',
        '200000' => '200000',
        '250000' => '250000'
    ],
    'email_admin' => [
        'purnomo@',
        'sindarigo@',
        'rindu@'
    ]
];

//banner array
$config['banner'] = [
    'status' => [
        'active' => 'active',
        'inactive' => 'inactive',
    ],
    'name' => [
        'tgif' => 17
    ]
];

//payment method array
$config['payment_method'] = [
    'status' => [
        'active' => 'active',
        'inactive' => 'inactive',
    ]
];

//courier array
$config['courier'] = [ 
    'status' => [
        'active'    => 'active',
        'inactive'  => 'inactive',
    ],
    'type' => [
        "confirmed" => "confirmed",
        "booking" => "booking",
        "pickup" => "pickup",
        "drop" => "drop",
        "completed" => "completed",
        "cancel" => "cancel",
        "price" => "price",
        'no_driver' => 'no_driver'
    ],
    'courier_code' => [
        'gosend' => 'gosend',
        'grab' => 'grab',
        'sicepat' => 'sicepat'
    ]
];

//store_operational array
$config['store_operational'] = [
    'stopt_status' => [
        'active'    => 'active',
        'inactive'  => 'inactive'
    ],
    'store_opt_status' => [
        'open'      => 'open',
        'close'     => 'close'
    ],
    'store_type' => [
        'all'           => 'all',
        'pickup_only'   => 'pickup_only',
        'delivery_only' => 'delivery_only'
    ],
    'day_name' => [
        'monday'    => 'monday',
        'tuesday'   => 'tuesday',
        'wednesday' => 'wednesday',
        'thursday'  => 'thursday',
        'friday'    => 'friday',
        'saturday'  => 'saturday',
        'sunday'    => 'sunday'
    ]

]; 

$config['cohort'] = [
    'cups_status' => [
        'free' => 1,
        'paid' => 0
    ]
];

//subs_plan
$config['subs_plan'] = [
    'subsplan_show'     => [
        'show'          => 'show',
        'hide'          => 'hide'
    ],
    'subsplan_promo'    => [
        'limit_usage'   => 0
    ]
];

// subscription
$config['subscription'] = [
    'status' => [
        "active" => "active",
        "inactive" => "inactive"
    ],
    'expired' => 3,
    'counter' => 0
];

// report user download
$config['user_download'] = [
    'usrd_type' => [
        'android' => 'android',
        'ios'     => 'ios'
    ]
];

// setup
$config['setup_admin'] = [
    'role' => [
        'barista' => 3
    ]
];

//voucher employee
$config['voucher_employee'] = [
    'organize'  => [
        'otten' => 'otten',
        'alpha'  => 'alpha'
    ],
    'position'      => [
        'employee'  => 'employee',
        'barista'   => 'barista',
        'trainer'   => 'trainer'
    ]
];

// subs_order
$config['subs_order'] = [
    'status_name' => [
        "waiting_for_payment" => "Waiting for payment",
        "paid"                => "Paid",
        "cancelled"           => "Cancelled"
    ],
    'status' => [
        "waiting_for_payment" => "waiting_for_payment",
        "paid"                => "paid",
        "cancelled"           => "cancelled"
    ],
    'payment_method' => [
        "wallet" => "1",
        "ovo"    => "2",
        "credit_card"  => "3",
        "gopay"    => "4"
    ],
    'payment_name' => [
        "1" => "wallet",
        "2" => "ovo",
        "3" => "credit_card",
        "4" => "gopay"
    ],
];

// subs counter
$config['subs_counter'] = [
    'status_name' => [
        "active" => "Active",
        "inactive" => "Inactive"
    ],
    'status' => [
        "active" => "active",
        "inactive" => "inactive"
    ]
];

//store image
$config['store_image'] = [
    'status' => [
        'active'    => 'active',
        'inactive'  => 'inactive'
    ]
];