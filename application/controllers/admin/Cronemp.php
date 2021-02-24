<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Cronemp extends MY_Service {

    function __construct()
	{
		parent::__construct();

        $this->load->model('promodb');
        $this->load->model('userdb');
        $this->log_key = 'generate_employee_voucher';

	}

    public function index()
    {
        show_404();
    }

    public function generate_employee_voucher()
    {
        $logthis    = $this->log4php($this->log_key, APPLOG );

        // $num_of_week = date("N");
        // $allowed_day = array(1,2,3,4,5);
        // if(! in_array($num_of_week, $allowed_day)){
        //     $info = "Exit cron : num of week #".$num_of_week;
        //     $logthis->info($info);
        //     exit();
        // }

        //get promo id
        $promo_id   = $this->check_promo_employee();

        $promo_detail = $this->promodb->get_promo($promo_id);

        //generate to all employee

        //generate to head of employee
        $limit_employee = 1;
        $where          = " AND vce_organize_name = ? AND vce_position = ? ";
        $position_emp   = $this->config->item('voucher_employee')['position'];
        $organize_emp   = $this->config->item('voucher_employee')['organize'];
        $data_emp       = [$organize_emp['alpha'], $position_emp['employee']];
        $all_employee   = $this->promodb->getall_voucher_employee($where, $data_emp);
        $this->process_additional_voucher($all_employee, $promo_id, $promo_detail->prm_custom_code, $limit_employee);

        //generate to head of barista
        $limit_head     = 2;    
        $data_barista   = [$organize_emp['alpha'], $position_emp['barista']];
        $all_head       = $this->promodb->getall_voucher_employee($where, $data_barista);
        $this->process_additional_voucher($all_head, $promo_id, $promo_detail->prm_custom_code, $limit_head);

        //generate to trainer
        $limit_trainer  = 7;
        $data_trainer   = [$organize_emp['alpha'], $position_emp['trainer']];
        $all_trainer    = $this->promodb->getall_voucher_employee($where, $data_trainer);
        $this->process_additional_voucher($all_trainer, $promo_id, $promo_detail->prm_custom_code, $limit_trainer);

    }

    private function process_additional_voucher($all_data, $promo_id, $prm_custom_code, $limit)
    {
        foreach($all_data as $key => $value){
            //select user by phone number
            $this->userdb->db->where('user_phone', $value->vce_phone);
            $this->userdb->db->limit(1);
            $query = $this->userdb->db->get($this->userdb->tbl_user);
            $detail_user = $query->row();

            //generate sebanyak yang dibutuhkan
            $this->generate_voucher($promo_id, $prm_custom_code, $detail_user->user_id, $limit);
        }
    }

    private function generate_voucher($prm_id, $prm_code, $user_id, $limit)
    {
        $logthis    = $this->log4php($this->log_key, APPLOG );
        $status     = $this->config->item('promo')['status'];

        //hitung dulu kurang berapa
        $this->promodb->db->select('count(*) total');
        $this->promodb->db->where('prm_id', $prm_id);
        $this->promodb->db->where('user_id', $user_id);
        $query = $this->promodb->db->get($this->promodb->tbl_voucher);
        $voucher_detail = $query->row();
        $total = $limit - $voucher_detail->total;


        $info = "Create ". $total ." voucher for user #".$user_id.", ".$prm_code;

        //looping sisanya
        for($i=0; $i<$total; $i++){

            $loop_refresh = true;

            // keeps generating vc_code until vc_code not found in db
            while ($loop_refresh) {
                $vc_code = $this->promodb->generate_voucher_code($prm_code);
                $voucher = $this->promodb->get_voucher_custom_filter(' AND vc_code = ?', [$vc_code]);

                if (!$voucher) {
                    $loop_refresh = false;
                }
            }

            //create new voucher
            $data = [
                'prm_id' => $prm_id,
                'user_id' => $user_id,
                'vc_code' => $vc_code,
                'vc_status' => $status['active'],
                'created_date' => date('Y-m-d H:i:s'),
            ];
            $this->promodb->insert_voucher($data);
            $vc_id = $this->db->insert_id();

            $info .= ", vc:".$vc_id;

        }
        $logthis->info($info);
    }

    private function check_promo_employee()
    {
        $logthis    = $this->log4php($this->log_key, APPLOG );

        $promo_type = $this->config->item('promo')['type'];
        $discount_type = $this->config->item('promo')['discount_type'];
        $status = $this->config->item('promo')['status'];
        $promo_code = $this->config->item('promo')['promo_code'];
        $store_type = $this->config->item('store')['type'];

        //checking promo
        $promo_name = 'Employee Voucher '.date('d/m/y');
        $promo_custom_code = $promo_code['emp'].date('ymd');
        $promo_start = date('Y-m-d');
        $promo_end = date('Y-m-d 23:59:59');
        $promo = $this->promodb->get_promo_custom_filter(' AND prm_name = ?', [$promo_name]);
        $voucher_default = $this->promodb->get_voucher_default_by_vcdef_code($promo_code['emp']);

        if (!$promo) {
            //create new promo
            $prm_rules = [
                'limit_usage' => 0,
                'custom_function' => null,
                'disc_type' => $discount_type['freecup'],
                'disc_nominal' => 1,
                'disc_max' => 0,
                'min_order' => 0,
                'delivery_included' => false,
                'free_delivery' => false,
                "delivery_type" => $store_type['pickup_only'],
                'item_type' => $voucher_default->vcdef_type,
                'item_list' => json_decode($voucher_default->vcdef_list, true)
            ];

            //set data promo
            $data = [
                'prm_name' => $promo_name,
                'prm_custom_code' => $promo_custom_code,
                'prm_start' => $promo_start,
                'prm_end' => $promo_end,
                'prm_type' => $promo_type['generated'],
                'prm_img' => 'promo/freecup_190116.png',
                'prm_status' => $status['active'],
                'prm_rules' => json_encode($prm_rules),
                'created_by'=> 0,
                'created_date' => date('Y-m-d H:i:s'),
            ];
            $promo_id = $this->promodb->insert_promo($data);

            $info = "Insert new promo #" . $promo_id . " " . $promo_custom_code;
            $logthis->info($info);
        }else{
            $promo_id = $promo->prm_id;
            $info = "Found promo #" . $promo->prm_id . " " . $promo->prm_custom_code;
            $logthis->info($info);
        }
        return $promo_id;
    }

    private function get_data_employee()
    {
        $arr_emp[] = array('name' => 'Igo Alpha', 'email' => 'sindarigo@', 'phone' => '+62817888160');
        $arr_emp[] = array('name' => 'Andrie', 'email' => 'twinsjune11@gmail.com', 'phone' => '+628111861106');
        $arr_emp[] = array('name' => 'Vanni', 'email' => 'ggr234@yahoo.com', 'phone' => '+628111940058');
        $arr_emp[] = array('name' => 'Masthur', 'email' => 'masthur@', 'phone' => '+628119588283');
        $arr_emp[] = array('name' => 'Vincent', 'email' => 'vince210890@gmail.com', 'phone' => '+628170196699');
        $arr_emp[] = array('name' => 'Rindu', 'email' => 'rinduprasetyo@gmail.com', 'phone' => '+628176648079');
        $arr_emp[] = array('name' => 'Rachmat Sampurna', 'email' => 'rachmat.sk@gmail.com', 'phone' => '+6282113808312');
        $arr_emp[] = array('name' => 'Tania Lista', 'email' => 'tanialista@yahoo.com', 'phone' => '+6287787382093');
        $arr_emp[] = array('name' => 'Bagas Segara Putra', 'email' => 'bagassegara23@gmail.com', 'phone' => '+628881801162');
        $arr_emp[] = array('name' => 'Hyoga Taranova', 'email' => 'hyogataranova07@gmail.com', 'phone' => '+628970169086');
        $arr_emp[] = array('name' => 'Abdullatif', 'email' => 'abdulltf1258@gmail.com', 'phone' => '+628978048875');
        $arr_emp[] = array('name' => 'Gusti Irmatiana', 'email' => 'gustiirmatiana1995@gmail.com', 'phone' => '+628979896602');
        $arr_emp[] = array('name' => 'Andy setyawan', 'email' => 'andysetyawan937@gmail.com', 'phone' => '+628982254820');
        $arr_emp[] = array('name' => 'Nada', 'email' => 'nada@', 'phone' => '+628995768249');
        $arr_emp[] = array('name' => 'Johan', 'email' => 'johanbuger2@gmail.com', 'phone' => '+628997057904');
        $arr_emp[] = array('name' => 'Achmad syaugi', 'email' => 'syaugi1424@gmail.com', 'phone' => '+6281211511001');
        $arr_emp[] = array('name' => 'Thalia Vita Fardinata', 'email' => 'Thaliav.fardinata@gmail.com', 'phone' => '+6281932069373');
        $arr_emp[] = array('name' => 'Erika', 'email' => 'erikakomala14@gmail.com', 'phone' => '+6281213766928');
        $arr_emp[] = array('name' => 'Jenny', 'email' => 'div_2709@live.com', 'phone' => '+6281213934657');
        $arr_emp[] = array('name' => 'Muhammad Aldino Putra  Arya', 'email' => 'aldinoputra.apa@gmail.com', 'phone' => '+6287782224117');
        $arr_emp[] = array('name' => 'Andrew', 'email' => 'april_the_8th@yahoo.com', 'phone' => '+6281219651378');
        $arr_emp[] = array('name' => 'Vira Septi Riany', 'email' => 'vira@', 'phone' => '+6281280177934');
        $arr_emp[] = array('name' => 'Silvia  Sumbogo', 'email' => 'silvia@', 'phone' => '+6281280254190');
        $arr_emp[] = array('name' => 'Hardiatna', 'email' => 'hardiatna96@gmail.com', 'phone' => '+6281280766367');
        $arr_emp[] = array('name' => 'Junitama  Panjaitan', 'email' => 'junitamap@gmail.com', 'phone' => '+6281280775477');
        $arr_emp[] = array('name' => 'Giryndra', 'email' => 'giryndraryaputra@gmail.com', 'phone' => '+6281311093464');
        $arr_emp[] = array('name' => 'Ferdy', 'email' => 'suryadi.ferdy@gmail.com', 'phone' => '+6281283765252');
        $arr_emp[] = array('name' => 'Michael', 'email' => 'myohanes141097@gmail.com', 'phone' => '+6281284580095');
        $arr_emp[] = array('name' => 'Dika', 'email' => '20112014dika@gmail.com', 'phone' => '+6281286438561');
        $arr_emp[] = array('name' => 'Vincentius satrio', 'email' => 'satryopandunegara@gmail.com', 'phone' => '+6281288256663');
        $arr_emp[] = array('name' => 'Rizky Ramadhan', 'email' => 'rizkyramadhan563@gmail.com', 'phone' => '+6281290959085');
        $arr_emp[] = array('name' => 'Febiyanto', 'email' => 'febiyanto.s@yahoo.com', 'phone' => '+6281293530117');
        $arr_emp[] = array('name' => 'Lusi Muliasari', 'email' => 'lucy.m@', 'phone' => '+6289672053108');
        $arr_emp[] = array('name' => 'Roy', 'email' => 'roysaragih3895@gmail.com', 'phone' => '+6281296249912');
        $arr_emp[] = array('name' => 'Afriyani', 'email' => 'apriyani262@gmail.con', 'phone' => '+6281296457372');
        $arr_emp[] = array('name' => 'Sonia', 'email' => 'firlysonia07@gmail.com', 'phone' => '+6281297188065');
        $arr_emp[] = array('name' => 'Jordy', 'email' => 'jordydaniel17@gmail.com', 'phone' => '+6281311223272');
        $arr_emp[] = array('name' => 'Retyo Sri Hudara', 'email' => 'retyo@', 'phone' => '+6281311813072');
        $arr_emp[] = array('name' => 'Yassa', 'email' => 'ariyassaop@yahoo.com', 'phone' => '+6281313651553');
        $arr_emp[] = array('name' => 'Fikri Haikal', 'email' => 'fikri.exodus@gmail.com', 'phone' => '+6281314134455');
        $arr_emp[] = array('name' => 'Soebanar Soebanar', 'email' => 'soebanar@', 'phone' => '+6281315777000');
        $arr_emp[] = array('name' => 'Amanda marsha', 'email' => 'amandaamanda176@gmail.com', 'phone' => '+6281316287770');
        $arr_emp[] = array('name' => 'Angga Fardela Midian', 'email' => 'anggafardela@gmail.com', 'phone' => '+6282278492800');
        $arr_emp[] = array('name' => 'Oka', 'email' => 'okaafriza111@gmail.com', 'phone' => '+6281318604193');
        $arr_emp[] = array('name' => 'Adrian Marchell', 'email' => 'adrmchll14@gmail.com', 'phone' => '+6281319323386');
        $arr_emp[] = array('name' => 'Dhipa', 'email' => 'adhitelastico@gmail.com', 'phone' => '+6289525707154');
        $arr_emp[] = array('name' => 'Sigit', 'email' => 'sigit@', 'phone' => '+6281321763777');
        $arr_emp[] = array('name' => 'Mochamad bayu rizky', 'email' => 'mochamadbayurizky@gmail.com', 'phone' => '+6281342102134');
        $arr_emp[] = array('name' => 'dwicky', 'email' => 'darmawan7dwicky@gmail.com', 'phone' => '+6281357226661');
        $arr_emp[] = array('name' => 'Farrel jati prakoso', 'email' => 'farrelchemberrlain@gmail.com', 'phone' => '+6281380514911');
        $arr_emp[] = array('name' => 'rizky parusi', 'email' => 'riparbright32@gmail.com', 'phone' => '+6281386269991');
        $arr_emp[] = array('name' => 'Firman Adityo  Laksono', 'email' => 'adityafirman99@yahoo.com', 'phone' => '+6281392961780');
        $arr_emp[] = array('name' => 'Sopiyan', 'email' => 'sssyah77@gmail.com', 'phone' => '+6281394454158');
        $arr_emp[] = array('name' => 'Iqbal Rizky Noviansyah', 'email' => 'iqbalrizky03@gmail.com', 'phone' => '+6281398115861');
        $arr_emp[] = array('name' => 'Potato', 'email' => 'aviskaavi@icloud.con', 'phone' => '+6281410210235');
        $arr_emp[] = array('name' => 'Haikal Yoga', 'email' => 'haikalyoga19@gmail.con', 'phone' => '+6281510259840');
        $arr_emp[] = array('name' => 'Kepin', 'email' => 'lutfii.muhammad14@gmail.com', 'phone' => '+6281513219997');
        $arr_emp[] = array('name' => 'Nino Tannio', 'email' => 'ninoth@', 'phone' => '+6281513577270');
        $arr_emp[] = array('name' => 'Muhamad Yusuf', 'email' => 'muhammadyusuf2018@gmail.com', 'phone' => '+6281514189591');
        $arr_emp[] = array('name' => 'Fikri', 'email' => 'fikrirm1@gmail.com', 'phone' => '+6281584560195');
        $arr_emp[] = array('name' => 'Rheny Wulandary', 'email' => 'rhenyswulandary@gmail.com', 'phone' => '+6281586739178');
        $arr_emp[] = array('name' => 'Simborong', 'email' => 'ro.aldo1498@gmail.com', 'phone' => '+6281617627292');
        $arr_emp[] = array('name' => 'Kikiaulia', 'email' => 'aulliahkiki95@gmail.com', 'phone' => '+6281292520825');
        $arr_emp[] = array('name' => 'Prabowo Prajogio', 'email' => 'prabowo.prajogio@hotmail.com', 'phone' => '+6281806876211');
        $arr_emp[] = array('name' => 'Naufal Fakhrie', 'email' => 'naufal.fakhrie98@gmail.com', 'phone' => '+6281807201017');
        $arr_emp[] = array('name' => 'AGUS ARIFIN', 'email' => 'agus.arifin01@gmail.com', 'phone' => '+6281808288898');
        $arr_emp[] = array('name' => 'Marita Hermawati', 'email' => 'cenulll98@gmail.com', 'phone' => '+6281818972995');
        $arr_emp[] = array('name' => 'Amir', 'email' => 'amiruddin@', 'phone' => '+6281906333375');
        $arr_emp[] = array('name' => 'Tegar sejati', 'email' => 'hohounyiel@gmail.com', 'phone' => '+6281908070907');
        $arr_emp[] = array('name' => 'Tebo', 'email' => 'juansyahtebo@gmail.com', 'phone' => '+6281915973334');
        $arr_emp[] = array('name' => 'Rosdiana Rachim', 'email' => 'rosdiana.rchm@gmail.com', 'phone' => '+6281932297722');
        $arr_emp[] = array('name' => 'Felix', 'email' => 'felix@', 'phone' => '+628119789062');
        $arr_emp[] = array('name' => 'Baron', 'email' => 'baron.soemadibrata@gmail.com', 'phone' => '+6282110869936');
        $arr_emp[] = array('name' => 'Kamila', 'email' => 'kamilamila4927@gmail.com', 'phone' => '+6282110939115');
        $arr_emp[] = array('name' => 'Gusti', 'email' => 'g.harphyk@gmail.com', 'phone' => '+6282111667300');
        $arr_emp[] = array('name' => 'Ferryansah', 'email' => 'feriansyah.ryan@rocketmail.com', 'phone' => '+6282114122202');
        $arr_emp[] = array('name' => 'intannur', 'email' => 'intannur8133@gmail.com', 'phone' => '+6282123093135');
        $arr_emp[] = array('name' => 'Mohammad Azami', 'email' => 'm.azami3011@gmail.com', 'phone' => '+6282123691401');
        $arr_emp[] = array('name' => 'Jabber', 'email' => 'jabber780@gmail.com', 'phone' => '+6281546074302');
        $arr_emp[] = array('name' => 'Muhammad Noval', 'email' => 'muhammadnovaladinugroho@gmail.com', 'phone' => '+6282131325248');
        $arr_emp[] = array('name' => 'Aldi Wiguna Setiawan', 'email' => 'aldiw088@gmail.com', 'phone' => '+6282211119322');
        $arr_emp[] = array('name' => 'Dara Karuna', 'email' => 'darani@', 'phone' => '+6282213169438');
        $arr_emp[] = array('name' => 'Christoper', 'email' => 'christoper.t.p@gmail.com', 'phone' => '+6282217716545');
        $arr_emp[] = array('name' => 'Helena', 'email' => 'helena_pinping@hotmail.com', 'phone' => '+6282260930711');
        $arr_emp[] = array('name' => 'arif setiawan', 'email' => 'arifsetiawan43827@gmail.com', 'phone' => '+6282261601078');
        $arr_emp[] = array('name' => 'Eric', 'email' => 'petruseric@gmail.com', 'phone' => '+6282299310677');
        $arr_emp[] = array('name' => 'Firdha Ramadhan', 'email' => 'firdha961@gmail.com', 'phone' => '+6283127129326');
        $arr_emp[] = array('name' => 'Bukhari Muslim', 'email' => 'bm.bukharimuslim@gmail.com', 'phone' => '+6283198863653');
        $arr_emp[] = array('name' => 'Nain', 'email' => 'nainsiddd@gmail.com', 'phone' => '+6283807965350');
        $arr_emp[] = array('name' => 'Liqoyima', 'email' => 'liqoyima@gmail.com', 'phone' => '+6287770375137');
        $arr_emp[] = array('name' => 'Dodysetiawan', 'email' => 'dodysetiawan93@yahoo.com', 'phone' => '+6283871277726');
        $arr_emp[] = array('name' => 'Adinda namira', 'email' => 'adindanamirao@gmail.com', 'phone' => '+6283876900813');
        $arr_emp[] = array('name' => 'Dimas tito Wicaksono', 'email' => 'dimastitowicaksono7@gmail.com', 'phone' => '+6283877024299');
        $arr_emp[] = array('name' => 'Andriyanto', 'email' => 'andriiyanto03@gmail.com', 'phone' => '+6283890207152');
        $arr_emp[] = array('name' => 'maia', 'email' => 'goismaia@gmail.com', 'phone' => '+6283895639319');
        $arr_emp[] = array('name' => 'Kevin Vieri Kimio T', 'email' => 'kevinvierikimio@gmail.com', 'phone' => '+6285692296002');
        $arr_emp[] = array('name' => 'Hanum', 'email' => 'hanum.mirza@gmail.com', 'phone' => '+6285693999330');
        $arr_emp[] = array('name' => 'bii', 'email' => 'biian.biann@gmail.com', 'phone' => '+6285695627950');
        $arr_emp[] = array('name' => 'Fathin', 'email' => 'fathinw14@gmail.com', 'phone' => '+6285695662829');
        $arr_emp[] = array('name' => 'Moh. Sahban  Sunaki', 'email' => 'mohsahbansunaki@gmail.com', 'phone' => '+6285695810641');
        $arr_emp[] = array('name' => 'Dani', 'email' => 'danijuendi26@gmail.com', 'phone' => '+6285695833895');
        $arr_emp[] = array('name' => 'Hikmat Sapaat', 'email' => 'hikmatsapaat22@gmail.com', 'phone' => '+6285703242809');
        $arr_emp[] = array('name' => 'Faisal', 'email' => 'faisalakbar592@gmail.com', 'phone' => '+6285714665451');
        $arr_emp[] = array('name' => 'Dian', 'email' => 'dianrini37@gmail.com', 'phone' => '+6285716795008');
        $arr_emp[] = array('name' => 'Irfan', 'email' => 'irfankate71@gmail.com', 'phone' => '+6285717353814');
        $arr_emp[] = array('name' => 'Rakha', 'email' => 'rakhanugraha07@gmail.com', 'phone' => '+6285717458939');
        $arr_emp[] = array('name' => 'Dita lestari', 'email' => 'dita.lestari@', 'phone' => '+6285719192464');
        $arr_emp[] = array('name' => 'Avin', 'email' => 'avinkurniawan30@gmail.com', 'phone' => '+6285770000485');
        $arr_emp[] = array('name' => 'Aldifirdian', 'email' => 'firdialdi38@gmail.com', 'phone' => '+6285770495145');
        $arr_emp[] = array('name' => 'Anita', 'email' => 'anitaarief22@gmail.com', 'phone' => '+6285773890783');
        $arr_emp[] = array('name' => 'M Faridz Adam', 'email' => 'omzjhantungnot@yahoo.com', 'phone' => '+6285775648079');
        $arr_emp[] = array('name' => 'Aldi rinaldi', 'email' => 'aldirinaldi0306@gmail.com', 'phone' => '+6285775863421');
        $arr_emp[] = array('name' => 'alfain', 'email' => 'alfain.oji@gmail.com', 'phone' => '+6285777166893');
        $arr_emp[] = array('name' => 'Sairul Azis', 'email' => 'sairulazis@gmail.com', 'phone' => '+6285777291149');
        $arr_emp[] = array('name' => 'winna', 'email' => 't.wina@yahoo.com', 'phone' => '+6285778808662');
        $arr_emp[] = array('name' => 'Ferry Ferdian', 'email' => 'ferrymirsyadferdian@gmail.com', 'phone' => '+6285781445554');
        $arr_emp[] = array('name' => 'Rezky putra mahendra', 'email' => 'rezkyputra9@gmail.com', 'phone' => '+6285782850190');
        $arr_emp[] = array('name' => 'Fadel', 'email' => 'fadelmajid@gmail.com', 'phone' => '+6285786007245');
        $arr_emp[] = array('name' => 'Grasyela Putri', 'email' => 'grasyelaps@gmail.com', 'phone' => '+6285798531300');
        $arr_emp[] = array('name' => 'Hana Monika', 'email' => 'hanamonik@gmail.com', 'phone' => '+6285813566524');
        $arr_emp[] = array('name' => 'Inez', 'email' => 'tiarainez21@gmail.com', 'phone' => '+6285813890845');
        $arr_emp[] = array('name' => 'AdityaC', 'email' => 'achesar97@gmail.com', 'phone' => '+6285817755800');
        $arr_emp[] = array('name' => 'Rafidah', 'email' => 'putrarafidah@gmail.com', 'phone' => '+6281299870734');
        $arr_emp[] = array('name' => 'Kenny Kinarya Widhiarsa', 'email' => 'kenkinarya@gmail.com', 'phone' => '+6287887287471');
        $arr_emp[] = array('name' => 'Arvin Alka', 'email' => 'arvinalka@gmail.com', 'phone' => '+6285842115837');
        $arr_emp[] = array('name' => 'Prawira Wardana', 'email' => 'wardanaprawira@gmail.com', 'phone' => '+6285876221144');
        $arr_emp[] = array('name' => 'Lady', 'email' => 'oscarlady155@ymail.com', 'phone' => '+6285880590544');
        $arr_emp[] = array('name' => 'Rahma', 'email' => 'rahmassuryani@gmail.com', 'phone' => '+6285880852800');
        $arr_emp[] = array('name' => 'Ananda Rizkiyadi', 'email' => 'anandarizky485@gmail.com', 'phone' => '+6285888682986');
        $arr_emp[] = array('name' => 'Okta Rizky Saputra', 'email' => 'oktarizkysaputra@gmail.com', 'phone' => '+6285780411262');
        $arr_emp[] = array('name' => 'Peter Alexander Nugroho Waluyo', 'email' => 'peteralexanderrrr@gmail.com', 'phone' => '+6285889150014');
        $arr_emp[] = array('name' => 'Gerhan Destriano Eriyanda', 'email' => 'destriano02@gmail.com', 'phone' => '+6281314117559');
        $arr_emp[] = array('name' => 'Mutia', 'email' => 'husnulkmutia1@gmail.com', 'phone' => '+6285893414817');
        $arr_emp[] = array('name' => 'Kikit ariyanto', 'email' => 'imamjody14@gmail.com', 'phone' => '+6285893632613');
        $arr_emp[] = array('name' => 'Syahlanuari Syahlanuari', 'email' => 'alanazkasyahputra2@gmail.com', 'phone' => '+6285899409186');
        $arr_emp[] = array('name' => 'Engel', 'email' => 'engel@', 'phone' => '+6285959707092');
        $arr_emp[] = array('name' => 'Leslie', 'email' => 'leslie@', 'phone' => '+6287711257350');
        $arr_emp[] = array('name' => 'Novi Ariyanti', 'email' => 'noviariyantii971211@gmail.com', 'phone' => '+6287720557315');
        $arr_emp[] = array('name' => 'Raedian', 'email' => 'wiboworaedian@gmail.com', 'phone' => '+6287721628085');
        $arr_emp[] = array('name' => 'Dewi', 'email' => 'permatadewi146@gmail.com', 'phone' => '+6287751027312');
        $arr_emp[] = array('name' => 'Gunawan', 'email' => 'gunawandede1993@gmail.com', 'phone' => '+6287781478715');
        $arr_emp[] = array('name' => 'Agnes', 'email' => 'agnes@cocowork.co', 'phone' => '+6287785609503');
        $arr_emp[] = array('name' => 'Fendy', 'email' => 'fkenzhou@gmail.com', 'phone' => '+6287786127550');
        $arr_emp[] = array('name' => 'Betvic Ferinc Mangowal', 'email' => 'betvicferinc11@gmail.com', 'phone' => '+6287788553696');
        $arr_emp[] = array('name' => 'Kv', 'email' => 'mk013089@gmail.com', 'phone' => '+6287791733427');
        $arr_emp[] = array('name' => 'Theo Rionaldo', 'email' => 'theorionaldo27@gmail.com', 'phone' => '+6287800050941');
        $arr_emp[] = array('name' => 'Frendi', 'email' => 'frendiantoropu@gmail.com', 'phone' => '+6287864092214');
        $arr_emp[] = array('name' => 'ryanda alferis', 'email' => 'ryandaalferis93.ra@gmail.com', 'phone' => '+6287874731498');
        $arr_emp[] = array('name' => 'Dani astari', 'email' => 'astaridani@gmail.com', 'phone' => '+6287875291931');
        $arr_emp[] = array('name' => 'Fifi', 'email' => 'nafisahdwi@gmail.com', 'phone' => '+6287880625056');
        $arr_emp[] = array('name' => 'Purnomo', 'email' => 'purnomonasional@gmail.com', 'phone' => '+6287881591430');
        $arr_emp[] = array('name' => 'Andien', 'email' => 'andin_aulia@hotmail.com', 'phone' => '+6287882478377');
        $arr_emp[] = array('name' => 'Brilian Pamungkas', 'email' => 'pamungkasbrilian@gmail.com', 'phone' => '+6287887021634');
        $arr_emp[] = array('name' => 'Iwan', 'email' => 'nurstwn1998@gmail.com', 'phone' => '+6287887636972');
        $arr_emp[] = array('name' => 'Retno annisa ', 'email' => 'anas16tasha@gmail.com', 'phone' => '+6281299723406');
        $arr_emp[] = array('name' => 'Eko Novandri  Listyanto', 'email' => 'ekonovan96@gmail.com', 'phone' => '+6281387659502');
        $arr_emp[] = array('name' => 'Shatumanzila', 'email' => 'shatumanzilanur@gmail.com', 'phone' => '+6289510682531');
        $arr_emp[] = array('name' => 'faqih tai', 'email' => 'faqih.abdau12@gmail.com', 'phone' => '+6289513672943');
        $arr_emp[] = array('name' => 'Annisa', 'email' => 'annisa.windasari.aw@gmail.com', 'phone' => '+6289521249657');
        $arr_emp[] = array('name' => 'Anju', 'email' => 'anju.tamba13@gmai.com', 'phone' => '+6282246107499');
        $arr_emp[] = array('name' => 'May ria shafitri', 'email' => 'mayriashafitri01@gmail.com', 'phone' => '+6289530137550');
        $arr_emp[] = array('name' => 'Dicky Herdiansyah', 'email' => 'dickyidm008@gmail.com', 'phone' => '+6285947754502');
        $arr_emp[] = array('name' => 'Tria Bella', 'email' => 'triabella5@gmail.com', 'phone' => '+6289602474176');
        $arr_emp[] = array('name' => 'Hardiansyah ardi', 'email' => 'ardiava182@gmail.com', 'phone' => '+6289614725689');
        $arr_emp[] = array('name' => 'Reynaldi Mirza', 'email' => 'reynaldimirza@gmail.com', 'phone' => '+6289618805560');
        $arr_emp[] = array('name' => 'Lail', 'email' => 'lailahmad37@gmail.com', 'phone' => '+6289629989042');
        $arr_emp[] = array('name' => 'Alifah bening', 'email' => 'alifahbening240@gmail.com', 'phone' => '+6289638133257');
        $arr_emp[] = array('name' => 'Putri amelia', 'email' => 'putriameliaayu14@gmail.com', 'phone' => '+6289643455073');
        $arr_emp[] = array('name' => 'Rommy Altalariksyah', 'email' => 'rommy.altalariksyah@gmail.com', 'phone' => '+6289656300433');
        $arr_emp[] = array('name' => 'alvina', 'email' => 'alvinaananda19@gmail.com', 'phone' => '+6289675402612');
        $arr_emp[] = array('name' => 'Ananda daniel', 'email' => 'dogergans@gmail.com', 'phone' => '+6289681043437');
        $arr_emp[] = array('name' => 'Vega ayu erizka', 'email' => 'vegaeriska@gmail.com', 'phone' => '+6289697979834');
        $arr_emp[] = array('name' => 'Arlita  Syahputri', 'email' => 'kirana.putriyani29@gmail.com', 'phone' => '+628986492270');
        $arr_emp[] = array('name' => 'Muhammad Dzulfikar', 'email' => 'fikarfikar442@gmail.com', 'phone' => '+62895326562076');
        $arr_emp[] = array('name' => 'Mohamad Alamsyah', 'email' => 'mohamadalamsyah395@gmail.com', 'phone' => '+62895327362981');
        $arr_emp[] = array('name' => 'Al Fayedh', 'email' => 'friviand16@gmail.com', 'phone' => '+62895347544302');
        $arr_emp[] = array('name' => 'Tika', 'email' => 'wastika.gustin30@gmail.com', 'phone' => '+62895372497289');
        $arr_emp[] = array('name' => 'Imelda liem', 'email' => 'imeliem23@gmail.com', 'phone' => '+62895374892294');
        $arr_emp[] = array('name' => 'Dean', 'email' => 'deanfirr@gmail.com', 'phone' => '+62895378557565');
        $arr_emp[] = array('name' => 'Ryan G Saputra', 'email' => 'ryan.galihsaputra@gmail.con', 'phone' => '+62895389598556');
        $arr_emp[] = array('name' => 'Mitha', 'email' => 'mithadea21@gmail.com', 'phone' => '+62895412840662');
        $arr_emp[] = array('name' => 'Suaibah Aslamiah', 'email' => 'elsyakiir@gmail.com', 'phone' => '+62895414764874');
        $arr_emp[] = array('name' => 'Olla Dacinvi', 'email' => 'yolladavinci984@gmail.com', 'phone' => '+6283894451337');
        $arr_emp[] = array('name' => 'Exaudio', 'email' => 'lionel.exaudio@gmail.com', 'phone' => '+62895605968007');
        $arr_emp[] = array('name' => 'Iman  Ghozali', 'email' => 'imanghozali11@gmail.com', 'phone' => '+62895611915752');
        $arr_emp[] = array('name' => 'Roihatul Jannah', 'email' => 'jannah.jannahr@gmail.com', 'phone' => '+62895635851553');
        $arr_emp[] = array('name' => 'Kishia Dwi Putri', 'email' => 'dwiputri.kishia@gmail.com', 'phone' => '+6282373144224');
        $arr_emp[] = array('name' => 'Karina Dwi Nadiasari', 'email' => 'karina.nadiasari@gmail.com', 'phone' => '+6287889184292');
        $arr_emp[] = array('name' => 'Ahmad Maulana', 'email' => 'ahmadmaulana467@gmail.com', 'phone' => '+6285921409093');
        $arr_emp[] = array('name' => 'JK', 'email' => 'jhoni@ottencoffee.co.id', 'phone' => '+628126090704');
        $arr_emp[] = array('name' => 'Robin', 'email' => 'binn85@gmail.com', 'phone' => '+6281370422333');
        $arr_emp[] = array('name' => 'Elisa', 'email' => 'elisasuteja@gmail.com', 'phone' => '+6287888087333');
        $arr_emp[] = array('name' => 'Rifki', 'email' => 'rifkhitriyaputra@gmail.com', 'phone' => '+6287876893229');
        $arr_emp[] = array('name' => 'Indra', 'email' => 'end_boyz02@yahoo.co.id', 'phone' => '+6281932796777');
        $arr_emp[] = array('name' => 'Abdul MuMin', 'email' => 'abdmukmin@rocketmail.com', 'phone' => '+6285222576654');
        $arr_emp[] = array('name' => 'Rizky Gautama', 'email' => 'rizky@', 'phone' => '+6281242722026');
        $arr_emp[] = array('name' => 'Dady Mulyadi', 'email' => 'daddy.adv@gmail.com', 'phone' => '+6281386294657');
        $arr_emp[] = array('name' => 'Putripujir', 'email' => 'putripujir96@gmail.com', 'phone' => '+6285772618230');
        $arr_emp[] = array('name' => 'Hervianto Tri H. K.', 'email' => 'kevingarrix@gmail.com', 'phone' => '+6281908301985');
        $arr_emp[] = array('name' => 'M. Ivan Akbar', 'email' => 'mhmmdivan3@gmail.com', 'phone' => '+6281290886252');
        $arr_emp[] = array('name' => 'Arni Aprilda Putri', 'email' => 'arniaprildaaa123@gmail.com', 'phone' => '+6285775458585');
        $arr_emp[] = array('name' => 'Murniawati', 'email' => 'murniawatiphalevy21@gmail.com', 'phone' => '+6285891886811');
        $arr_emp[] = array('name' => 'Indriani Eka Mayhesti', 'email' => 'indrianimayhesti@gmail.com', 'phone' => '+6289601375953');
        $arr_emp[] = array('name' => 'Sheftyan Asti', 'email' => 'sheftyanasti@gmail.com', 'phone' => '+6281511827973');
        $arr_emp[] = array('name' => 'Dede Purwono', 'email' => 'ddpurwono2@gmail.com', 'phone' => '+62895342843962');
        $arr_emp[] = array('name' => 'Satrio', 'email' => 'satrio.stm69@gmail.com', 'phone' => '+6287885829156');
        $arr_emp[] = array('name' => 'Anggi Lestari', 'email' => 'anggilstrrr@gmail.com', 'phone' => '+62895326633665');
        $arr_emp[] = array('name' => 'Yuda Darmawansyah', 'email' => 'yudadarmawansyah12@gmail.com', 'phone' => '+6282114326254');
        $arr_emp[] = array('name' => 'Muhammad Ismail', 'email' => 'm.ismail0327@gmail.com', 'phone' => '+62895615790163');
        $arr_emp[] = array('name' => 'Kartisah', 'email' => 'kartisah.meylasarie@gmail.com', 'phone' => '+6285726466616');
        $arr_emp[] = array('name' => 'M. Narianto ', 'email' => 'mnarianto@gmail.cim', 'phone' => '+6289678467116');
        $arr_emp[] = array('name' => 'Asyafa Maulandy', 'email' => 'Andiajadah@gmail.com', 'phone' => '+6281315194025');
        $arr_emp[] = array('name' => 'Ariq Hafizh Bariqi', 'email' => 'ariq.hb@gmail.com', 'phone' => '+6281288893526');
        $arr_emp[] = array('name' => 'Rezga Fadhlil Alwafi', 'email' => 'rezga.alwafi@gmail.com', 'phone' => '+6285880300139');
        $arr_emp[] = array('name' => 'Ungar Lungguh Legowo', 'email' => 'ungar27@gmail.com', 'phone' => '+6289602499984');
        $arr_emp[] = array('name' => 'Egi Adi Sandra', 'email' => 'egyadisandra@gmail.com', 'phone' => '+6281510915557');
        $arr_emp[] = array('name' => 'Zulfikar Alief Oktavyantara', 'email' => 'zulfikarvyantara@gmail.com', 'phone' => '+6285883422336');
        $arr_emp[] = array('name' => 'Tri Purwani', 'email' => 'purwanitri13@gmail.com', 'phone' => '+6289630396730');
        $arr_emp[] = array('name' => 'Bayu Mukti Wibowo', 'email' => 'bayuwibowo167@gmail.com', 'phone' => '+6281280043452');
        $arr_emp[] = array('name' => 'Siti Rahmah Hidayah', 'email' => 'rahmahhidayah170420@gmail.com', 'phone' => '+6289657705575');
        $arr_emp[] = array('name' => 'Ardana Aji Pangestu Putra', 'email' => 'ardanaaji02@gmail.com', 'phone' => '+6289652403207');
        $arr_emp[] = array('name' => 'Yudo Alexandro', 'email' => 'yudho.delpiero@yahoo.com', 'phone' => '+6283814332973');
        $arr_emp[] = array('name' => 'Gadis Darasitta', 'email' => 'gadisdarasita85@gmail.com', 'phone' => '+6287836321617');
        $arr_emp[] = array('name' => 'Zahra Kintani Asyura', 'email' => 'zahrakintanii@gmail.com', 'phone' => '+6287789250499');
        $arr_emp[] = array('name' => 'Asido Alexander Ferguson', 'email' => 'asidoalexa@gmail.com', 'phone' => '+6285961136495');
        $arr_emp[] = array('name' => 'Hanny Maulyna', 'email' => 'ninoarrasyid0911@gmail.com', 'phone' => '+6282126254404');
        $arr_emp[] = array('name' => 'Louise Tabita', 'email' => 'louisetabita@gmail.com', 'phone' => '+6281317177021');
        $arr_emp[] = array('name' => 'Putri Anastasya Wulandari', 'email' => 'putri.anastasya19@gmail.com', 'phone' => '+6287764946661');
        $arr_emp[] = array('name' => 'Bisatyoargya', 'email' => 'argyabisatyo18@gmail.com', 'phone' => '+62895347104509');
        $arr_emp[] = array('name' => 'Bobby J', 'email' => 'bobbyj@', 'phone' => '+6281238458281');
        $arr_emp[] = array('name' => 'Luthfi sholahudin', 'email' => 'lutfishldin@gmail.com', 'phone' => '+6285706053564');
        $arr_emp[] = array('name' => 'Hendry Setyawan', 'email' => 'hendry.setyawan5397@gmail.com', 'phone' => '+6283821356575');
        $arr_emp[] = array('name' => 'Ahmad randu sentosa', 'email' => 'Ahmadrandu38@gmail.com', 'phone' => '+6283807980932');
        $arr_emp[] = array('name' => 'Deni danang ariyanto saputro', 'email' => 'Danangsaputro311294@gmail.com', 'phone' => '+6282235832612');
        $arr_emp[] = array('name' => 'Putri', 'email' => 'putrilarsti22@gmail.com', 'phone' => '+628986669852');
        $arr_emp[] = array('name' => 'Tovan', 'email' => 'christovancornelless@gmail.com', 'phone' => '+6281319290224');
        $arr_emp[] = array('name' => 'Ayu Fitriani', 'email' => 'afitriani495@gmail.com', 'phone' => '+6285899025032');
        $arr_emp[] = array('name' => 'Ridwan', 'email' => 'alfaridwan777@gmail.com', 'phone' => '+6288213020754');
        $arr_emp[] = array('name' => 'Permata Dewi', 'email' => 'permatadewi146@gmail.com', 'phone' => '+6287751027312');
        $arr_emp[] = array('name' => 'ita permatasari', 'email' => 'iithatha190495@gmail.com', 'phone' => '+628179910551');
        $arr_emp[] = array('name' => 'Rafsanjani', 'email' => 'Rafsa.almahdali@gmail.com', 'phone' => '+6285880488812');
        $arr_emp[] = array('name' => 'Muhamad Iqbal ', 'email' => 'deldung12@gmail.com', 'phone' => '+6287884978846');
        $arr_emp[] = array('name' => 'Finza', 'email' => 'Finza1994@gmail.com', 'phone' => '+62895392603637');
        $arr_emp[] = array('name' => 'M. Aditya', 'email' => 'Adityaaa312@gmail.com', 'phone' => '+6282299422000');
        $arr_emp[] = array('name' => 'Asrul Rasyid', 'email' => 'Rasyidasrul12@gmail.com', 'phone' => '+6281210549264');
        $arr_emp[] = array('name' => 'Achmad Nouval', 'email' => 'Novalyannuar@gmail.com ', 'phone' => '+6281533226009');
        $arr_emp[] = array('name' => 'Rizal', 'email' => 'Rizalcsr18@gmail.com', 'phone' => '+6281280305663');
        $arr_emp[] = array('name' => 'Putra', 'email' => 'Putrasetyaa@icloud.com', 'phone' => '+6287888656972');
        $arr_emp[] = array('name' => 'Ahmad Irfan', 'email' => 'ahmadirfan.ia@gmail.com', 'phone' => '+6281372648859');
        $arr_emp[] = array('name' => 'Muhammad Fauzi', 'email' => 'uzimuhammad02@gmail.com', 'phone' => '+6287784466388');
        $arr_emp[] = array('name' => 'Rahmat Alam Harikyawan', 'email' => 'xlimut_harry@yahoo.com', 'phone' => '+6285295714390');
        $arr_emp[] = array('name' => 'Muhammad Nurul Misbah', 'email' => 'nurulmisbah112@gmail.com', 'phone' => '+6285782226820');
        $arr_emp[] = array('name' => 'mukhlis ibrahim', 'email' => 'ibrahimmukhlis2@gmail.com', 'phone' => '+6285719003211');
        $arr_emp[] = array('name' => 'Muhamad Tauviqul Hakim', 'email' => 'tauviqullhakim@gmail.com', 'phone' => '+6281315400371');
        $arr_emp[] = array('name' => 'Maulana Azri', 'email' => 'maulanaazri30@gmail.com', 'phone' => '+6281286498241');
        $arr_emp[] = array('name' => 'Rizki Akbar', 'email' => 'rizki.akbar.ht@gmail.com', 'phone' => '+628118444418');
        $arr_emp[] = array('name' => 'Yudha Laksa', 'email' => 'yudhalaskad@gmail.com', 'phone' => '+6287889591879');
        $arr_emp[] = array('name' => 'Ulfa Fitriana', 'email' => 'ulfafitriana96@yahoo.com', 'phone' => '+6281380903550');
        $arr_emp[] = array('name' => 'Eko Faisal', 'email' => 'ekofaizal24@gmail.com', 'phone' => '+6281387699950');
        $arr_emp[] = array('name' => 'Reza', 'email' => 'rezasyafiq46@gmail.com', 'phone' => '+6287874248527');
        $arr_emp[] = array('name' => 'Bill Jati', 'email' => 'billjati14@gmail.com', 'phone' => '+6287720728600');
        $arr_emp[] = array('name' => 'Ahmad Renaldy', 'email' => 'ahmadrenal@gmail.com', 'phone' => '+62895412042530');
        $arr_emp[] = array('name' => 'Azka Millinia', 'email' => 'azkamillinia@gmail.com', 'phone' => '+6285773844309');
        $arr_emp[] = array('name' => 'Desta', 'email' => 'destarustia@gmail.com', 'phone' => '+6285872329083');
        $arr_emp[] = array('name' => 'Diago Osfaldo', 'email' => 'osfaldo98@gmail.com', 'phone' => '+6289637920550');
        $arr_emp[] = array('name' => 'Febrian', 'email' => 'febrian.a.dharma@gmail.com', 'phone' => '+6289601265646');
        $arr_emp[] = array('name' => 'Felia', 'email' => 'fefelfelia31@gmail.com', 'phone' => '+6289696854524');
        $arr_emp[] = array('name' => 'Iqbal Wahyudi', 'email' => 'iqbalwahyudi@gmail.com', 'phone' => '+6281388553435');
        $arr_emp[] = array('name' => 'Josephine', 'email' => 'josephinefani@gmail.com', 'phone' => '+6287777500015');
        $arr_emp[] = array('name' => 'Marlina', 'email' => 'marlinaoct.05@yahoo.com', 'phone' => '+6289662147900');
        $arr_emp[] = array('name' => 'Nadhif', 'email' => 'sxtnxs@gmail.com', 'phone' => '+628111118926');
        $arr_emp[] = array('name' => 'Rama', 'email' => 'ramavillopoto@gmail.com', 'phone' => '+6281221693934');
        $arr_emp[] = array('name' => 'Yogi', 'email' => 'sayarahmat.dwi@gmail.com', 'phone' => '+6281258142090');
        $arr_emp[] = array('name' => 'Joni Oktiawan', 'email' => 'jhonijofista123@gmail.com', 'phone' => '+6281283018158');
        $arr_emp[] = array('name' => 'Inka Enudia', 'email' => 'inkaenudia@gmail.com', 'phone' => '+6282110979733');
        $arr_emp[] = array('name' => 'Haviz Abdul Fatah', 'email' => 'havizabdulfatah@gmail.com', 'phone' => '+6288215992308');
        $arr_emp[] = array('name' => 'Rahmat hidayat', 'email' => 'rahmat.hidayat.math@gmail.com', 'phone' => '+6281289978067');
        $arr_emp[] = array('name' => 'debby', 'email' => 'debbycaspigo@gmail.com', 'phone' => '+6282114515464');
        $arr_emp[] = array('name' => 'Hans Kristian', 'email' => 'hans.kristian88@gmail.com', 'phone' => '+6281292418417');
        return $arr_emp;
    }

    private function get_data_head_barista()
    {
        $arr_emp[] = array('name' => 'Achmad syaugi', 'email' => 'syaugi1424@gmail.com', 'phone' => '+6281211511001');
        $arr_emp[] = array('name' => 'Thalia Vita Fardinata', 'email' => 'Thaliav.fardinata@gmail.com', 'phone' => '+6281932069373');
        $arr_emp[] = array('name' => 'Roy', 'email' => 'roysaragih3895@gmail.com', 'phone' => '+6281296249912');
        $arr_emp[] = array('name' => 'Dhipa', 'email' => 'adhitelastico@gmail.com', 'phone' => '+6289525707154');
        $arr_emp[] = array('name' => 'Fikri', 'email' => 'fikrirm1@gmail.com', 'phone' => '+6281584560195');
        $arr_emp[] = array('name' => 'Kikiaulia', 'email' => 'aulliahkiki95@gmail.com', 'phone' => '+6281292520825');
        $arr_emp[] = array('name' => 'Tebo', 'email' => 'juansyahtebo@gmail.com', 'phone' => '+6281915973334');
        $arr_emp[] = array('name' => 'Rosdiana Rachim', 'email' => 'rosdiana.rchm@gmail.com', 'phone' => '+6281932297722');
        $arr_emp[] = array('name' => 'Gusti', 'email' => 'g.harphyk@gmail.com', 'phone' => '+6282111667300');
        $arr_emp[] = array('name' => 'Ferryansah', 'email' => 'feriansyah.ryan@rocketmail.com', 'phone' => '+6282114122202');
        $arr_emp[] = array('name' => 'Aldi Wiguna Setiawan', 'email' => 'aldiw088@gmail.com', 'phone' => '+6282211119322');
        $arr_emp[] = array('name' => 'Dodysetiawan', 'email' => 'dodysetiawan93@yahoo.com', 'phone' => '+6283871277726');
        $arr_emp[] = array('name' => 'Dimas tito Wicaksono', 'email' => 'dimastitowicaksono7@gmail.com', 'phone' => '+6283877024299');
        $arr_emp[] = array('name' => 'Kevin Vieri Kimio T', 'email' => 'kevinvierikimio@gmail.com', 'phone' => '+6285692296002');
        $arr_emp[] = array('name' => 'Dani', 'email' => 'danijuendi26@gmail.com', 'phone' => '+6285695833895');
        $arr_emp[] = array('name' => 'alfain', 'email' => 'alfain.oji@gmail.com', 'phone' => '+6285777166893');
        $arr_emp[] = array('name' => 'Ferry Ferdian', 'email' => 'ferrymirsyadferdian@gmail.com', 'phone' => '+6285781445554');
        $arr_emp[] = array('name' => 'AdityaC', 'email' => 'achesar97@gmail.com', 'phone' => '+6285817755800');
        $arr_emp[] = array('name' => 'Kenny Kinarya Widhiarsa', 'email' => 'kenkinarya@gmail.com', 'phone' => '+6287887287471');
        $arr_emp[] = array('name' => 'Okta Rizky Saputra', 'email' => 'oktarizkysaputra@gmail.com', 'phone' => '+6285780411262');
        $arr_emp[] = array('name' => 'Peter Alexander Nugroho Waluyo', 'email' => 'peteralexanderrrr@gmail.com', 'phone' => '+6285889150014');
        $arr_emp[] = array('name' => 'Kikit ariyanto', 'email' => 'imamjody14@gmail.com', 'phone' => '+6285893632613');
        $arr_emp[] = array('name' => 'Syahlanuari Syahlanuari', 'email' => 'alanazkasyahputra2@gmail.com', 'phone' => '+6285899409186');
        $arr_emp[] = array('name' => 'Gunawan', 'email' => 'gunawandede1993@gmail.com', 'phone' => '+6287781478715');
        $arr_emp[] = array('name' => 'Betvic Ferinc Mangowal', 'email' => 'betvicferinc11@gmail.com', 'phone' => '+6287788553696');
        $arr_emp[] = array('name' => 'Iwan', 'email' => 'nurstwn1998@gmail.com', 'phone' => '+6287887636972');
        $arr_emp[] = array('name' => 'Annisa', 'email' => 'annisa.windasari.aw@gmail.com', 'phone' => '+6289521249657');
        $arr_emp[] = array('name' => 'Tria Bella', 'email' => 'triabella5@gmail.com', 'phone' => '+6289602474176');
        $arr_emp[] = array('name' => 'Reynaldi Mirza', 'email' => 'reynaldimirza@gmail.com', 'phone' => '+6289618805560');
        $arr_emp[] = array('name' => 'Alifah bening', 'email' => 'alifahbening240@gmail.com', 'phone' => '+6289638133257');
        $arr_emp[] = array('name' => 'Rommy Altalariksyah', 'email' => 'rommy.altalariksyah@gmail.com', 'phone' => '+6289656300433');
        $arr_emp[] = array('name' => 'Dean', 'email' => 'deanfirr@gmail.com', 'phone' => '+62895378557565');
        $arr_emp[] = array('name' => 'Ryan G Saputra', 'email' => 'ryan.galihsaputra@gmail.con', 'phone' => '+62895389598556');
        $arr_emp[] = array('name' => 'Iman  Ghozali', 'email' => 'imanghozali11@gmail.com', 'phone' => '+62895611915752');
        $arr_emp[] = array('name' => 'Louise Tabita', 'email' => 'louisetabita@gmail.com', 'phone' => '+6281317177021');
        $arr_emp[] = array('name' => 'Matthew Evan', 'email' => 'matthew_evan20@yahoo.com', 'phone' => '+6287777305937');

        return $arr_emp;
    }


    private function get_data_trainer()
    {
        $arr_emp[] = array('name' => 'Emanuel Andrew  Sumanti', 'email' => 'april_the_8th@yahoo.com', 'phone' => '+6281219651378');
        return $arr_emp;
    }

}