<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-12">
        <h2><?php echo $title_form;?> Store Operational</h2>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-12">
        <?php echo $msg;?>
        <?php if ($show_form): ?>
            <div class="ibox">
                <div class="ibox-content" style="">
                    <?php echo form_open($current_url.'/'.($store_opt? $store_opt->sto_id : ''), ['id' => 'theform']); ?>
                    <input type="hidden" name="sto_id" value="<?php echo set_value('sto_id', $store_opt ? $store_opt->sto_id : 0); ?>">
                    <div class="form-row">
                        <div class="form-group col-6">
                                <h5>Store Name</h5>
                                <select class="form-control m-b" name="st_id">
                                <?php 
                                $st_id = set_value('st_id', ($store_opt ? $store_opt->st_id : ''));
                                foreach ($store as $st): ?>
                                    <option value="<?php echo $st->st_id; ?>" <?php echo $st_id === $st->st_id ? 'selected="selected"' : ''; ?>><?php echo $st->st_name; ?></option>
                                <?php endforeach; ?>
                                </select>
                            </select>
                        </div>
                        <div class="col-3">
                            <h5>Start Date</h5>
                            <input class="form-control" name="start_date" id="start_date" value="<?php echo set_value('start_date', $store_opt ? $store_opt->start_date : '' ); ?>">
                            <?php echo form_error('start_date'); ?>
                        </div>
                        <div class="col-3">
                            <h5>End Date</h5>
                            <input class="form-control" name="end_date" id="end_date" value="<?php echo set_value('end_date', $store_opt ? $store_opt->end_date : ''); ?>">
                            <?php echo form_error('end_date'); ?>
                        </div>
                    </div>

                    <div class="hr-line-dashed"></div>

                    <!-- Start Monday -->
                    <?php $status_mon = set_value('store_opt_list[monday][status]', (!empty($monday['status']) ? $monday['status'] : '')) == 'open' ? 'open' : 'close'; ?>
                    <?php 
                        $color_mon      = '';
                        $checked_mon    = '';
                        $display_mon    = 'none';
                        $status_monday  = 'close';

                        if((!empty($msg) && $title_form == 'Edit') || empty($msg)){
                            if($status_mon == 'open'){
                                $color_mon      = 'limegreen';
                                $checked_mon    = 'checked';
                                $display_mon    = 'block';
                                $status_monday  = 'open';
                            }
                        }
                    ?>
                    <label id="monday" class="form-control dd-handle" style="background : <?php echo $color_mon; ?>"> 
                    Monday
                    <input class="float-right change-checkbox" type="checkbox" name="checkbox[monday]" style="width:50px;height:20px;" data-day="monday" <?php echo $checked_mon;?>>
                    </label>
                    
                    <div class="monday" style="padding : 18px; display : <?php echo $display_mon; ?>">
                    <div class="form-row">
                        <div class="col">
                            <h5>Status</h5>
                            <input name="store_opt_list[monday][status]" class="form-control m-b change-disable" value="<?php echo $status_monday; ?>" disabled> 
                            <input name="store_opt_list[monday][status]" type="hidden" value="<?php echo $status_monday; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="col">
                            <h5>Store Type</h5>
                            <select class="form-control m-b change-type-disable" name="store_opt_list[monday][st_type]" data-day="monday" <?php echo $status_mon == 'close' ? 'disabled' : '' ?>>
                                    <?php                                     
                                    $type_mon = set_value('store_opt_list[monday][st_type]', (!empty($monday['st_type']) ? $monday['st_type'] : ''));
                                    foreach ($cst_type as $key => $val): ?>
                                        <option value="<?php echo $key; ?>" <?php echo  $type_mon === $key ? 'selected="selected"' : ''; ?>><?php echo $val; ?></option>
                                    <?php endforeach; ?>
                            </select>
                            <?php echo form_error("store_opt_list[monday][st_type]"); ?>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col">
                            <h5>Open Pickup Time</h5>
                            <input class="form-control time" name="store_opt_list[monday][pickup][open]" value="<?php echo set_value('store_opt_list[monday][pickup][open]', ($monday && $status_mon != 'close' ? $monday['pickup'][$cst_status['open']] : '09:00'));?>" 
                            <?php echo $status_mon == 'close' || $type_mon == 'delivery_only' ? 'disabled' : '' ?>>
                            <?php echo form_error('store_opt_list[monday][pickup][open]'); ?>
                        </div>
                        <div class="col">
                            <h5>Close Pickup Time</h5>
                            <input class="form-control time" name="store_opt_list[monday][pickup][close]" value="<?php echo set_value('store_opt_list[monday][pickup][close]', ($monday && $status_mon != 'close' ? $monday['pickup']['close'] : '21:00'));?>" 
                            <?php echo $status_mon == 'close' || $type_mon == 'delivery_only' ? 'disabled' : '' ?>>
                            <?php echo form_error('store_opt_list[monday][pickup][close]'); ?>
                        </div>
                        <div class="col">
                            <h5>Open Delivery Time</h5>
                            <input class="form-control time" name="store_opt_list[monday][delivery][open]" value="<?php echo set_value('store_opt_list[monday][delivery][open]', ($monday && $status_mon != 'close' ? $monday['delivery'][$cst_status['open']] : '09:00'));?>" 
                            <?php echo $status_mon == 'close' || $type_mon == 'pickup_only' ? 'disabled' : '' ?>>
                            <?php echo form_error('store_opt_list[monday][delivery][open]'); ?>
                        </div>
                        <div class="col">
                            <h5>Close Delivery Time</h5>
                            <input class="form-control time" name="store_opt_list[monday][delivery][close]" value="<?php echo set_value('store_opt_list[monday][delivery][close]', ($monday && $status_mon != 'close' ? $monday['delivery']['close'] : '21:00'));?>" 
                            <?php echo $status_mon == 'close' || $type_mon == 'pickup_only' ? 'disabled' : '' ?>>
                            <?php echo form_error('store_opt_list[monday][delivery][close]'); ?>
                        </div>
                      </div>  
                    </div>
                    <!-- End Monday -->  

                    <!-- Start Tuesday -->
                    <?php $status_tue = set_value('store_opt_list[tuesday][status]', (!empty($tuesday['status']) ? $tuesday['status'] : '')) == 'open' ? 'open' : 'close'; ?>
                    <?php 
                        $color_tue      = ''; 
                        $checked_tue    = ''; 
                        $display_tue    = 'none'; 
                        $status_tuesday = 'close';
                       
                        if((!empty($msg) && $title_form == 'Edit') || empty($msg)){
                            if($status_tue == 'open'){
                                $color_tue      = 'limegreen';
                                $checked_tue    = 'checked';
                                $display_tue    = 'block';
                                $status_tuesday = 'open';
                            }
                        }
                    ?>
                    <label id="tuesday" class="form-control dd-handle" style="background : <?php echo $color_tue; ?>"> 
                    Tuesday
                    <input class="float-right change-checkbox" type="checkbox" name="checkbox[tuesday]" style="width:50px;height:20px;" data-day="tuesday" <?php echo $checked_tue;?>>
                    </label>
                    
                    <div class="tuesday" style="padding : 18px; display : <?php echo $display_tue; ?>">
                    <div class="form-row">
                        <div class="col">
                            <h5>Status</h5>
                            <input name="store_opt_list[tuesday][status]" class="form-control m-b change-disable" disabled
                            value="<?php echo $status_tuesday; ?>"> 
                            <input name="store_opt_list[tuesday][status]" type="hidden"
                            value="<?php echo $status_tuesday; ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col">
                            <h5>Store Type</h5>
                            <select class="form-control m-b change-type-disable" name="store_opt_list[tuesday][st_type]" data-day="tuesday" <?php echo $status_tue == 'close' ? 'disabled' : '' ?>>
                                    <?php 
                                    $type_tue = set_value('store_opt_list[tuesday][st_type]', (!empty($tuesday['st_type']) ? $tuesday['st_type'] : ''));
                                    foreach ($cst_type as $key => $val): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $type_tue == $key ? 'selected="selected"' : ''; ?>><?php echo $val; ?></option>
                                    <?php endforeach; ?>
                            </select>
                            <?php echo form_error("store_opt_list[tuesday][st_type]"); ?>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col">
                            <h5>Open Pickup Time</h5>
                            <input class="form-control time" name="store_opt_list[tuesday][pickup][open]" value="<?php echo set_value('store_opt_list[tuesday][pickup][open]', ($tuesday && $status_tue != 'close' ? $tuesday['pickup'][$cst_status['open']] : '09:00'));?>"
                            <?php echo $status_tue == 'close' || $type_tue == 'delivery_only' ? 'disabled' : '' ?>>
                            <?php echo form_error('store_opt_list[tuesday][pickup][open]'); ?>
                        </div>
                        <div class="col">
                            <h5>Close Pickup Time</h5>
                            <input class="form-control time" name="store_opt_list[tuesday][pickup][close]" value="<?php echo set_value('store_opt_list[tuesday][pickup][close]', ($tuesday && $status_tue != 'close' ? $tuesday['pickup']['close'] : '21:00'));?>" 
                            <?php echo $status_tue == 'close' || $type_tue == 'delivery_only' ? 'disabled' : '' ?>>
                            <?php echo form_error('store_opt_list[tuesday][pickup][close]'); ?>
                        </div>
                        <div class="col">
                            <h5>Open Delivery Time</h5>
                            <input class="form-control time" name="store_opt_list[tuesday][delivery][open]" value="<?php echo set_value('store_opt_list[tuesday][delivery][open]', ($tuesday && $status_tue != 'close' ? $tuesday['delivery'][$cst_status['open']] : '09:00'));?>" 
                            <?php echo $status_tue == 'close' || $type_tue == 'pickup_only' ? 'disabled' : '' ?>>
                            <?php echo form_error('store_opt_list[tuesday][delivery][open]'); ?>
                        </div>
                        <div class="col">
                            <h5>Close Delivery Time</h5>
                            <input class="form-control time" name="store_opt_list[tuesday][delivery][close]" value="<?php echo set_value('store_opt_list[tuesday][delivery][close]', ($tuesday && $status_tue != 'close' ? $tuesday['delivery']['close'] : '21:00'));?>" 
                            <?php echo $status_tue == 'close' || $type_tue == 'pickup_only' ? 'disabled' : '' ?>>
                            <?php echo form_error('store_opt_list[tuesday][delivery][close]'); ?>
                        </div>
                      </div>
                    </div>
                    <!-- End Tuesday -->

                    <!-- Start Wednesday -->
                    <?php $status_wed = set_value('store_opt_list[wednesday][status]', (!empty($wednesday['status']) ? $wednesday['status'] : '')) == 'open' ? 'open' : 'close'; ?>
                    <?php 
                        $color_wed          = '';
                        $checked_wed        = '';
                        $display_wed        = 'none';
                        $status_wednesday   = 'close';
                       
                        if((!empty($msg) && $title_form == 'Edit') || empty($msg)){
                            if($status_wed == 'open'){
                                $color_wed          = 'limegreen';
                                $checked_wed        = 'checked';
                                $display_wed        = 'block';
                                $status_wednesday   = 'open';
                            }
                        }
                    ?>
                    <label id="wednesday" class="form-control dd-handle" style="background : <?php echo $color_wed; ?>"> 
                    Wednesday
                    <input class="float-right change-checkbox" type="checkbox" name="checkbox[wednesday]" style="width:50px;height:20px;" data-day="wednesday" <?php echo $checked_wed;?>>
                    </label>
                    
                    <div class="wednesday" style="padding : 18px; display : <?php echo $display_wed; ?>">
                    <div class="form-row">
                        <div class="col">
                            <h5>Status</h5>
                            <input name="store_opt_list[wednesday][status]" class="form-control m-b change-disable" disabled
                            value="<?php echo $status_wednesday; ?>"> 
                            <input name="store_opt_list[wednesday][status]" type="hidden"
                            value="<?php echo $status_wednesday; ?>">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col">
                            <h5>Store Type</h5>
                            <select class="form-control m-b change-type-disable" name="store_opt_list[wednesday][st_type]" data-day="wednesday" <?php echo $status_wed == 'close' ? 'disabled' : '' ?>>
                                    <?php 
                                    $type_wed = set_value('store_opt_list[wednesday][st_type]', (!empty($wednesday['st_type']) ? $wednesday['st_type'] : ''));
                                    foreach ($cst_type as $key => $val): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $type_wed == $key ? 'selected="selected"' : ''; ?>><?php echo $val; ?></option>
                                    <?php endforeach; ?>
                            </select>
                            <?php echo form_error("store_opt_list[wednesday][st_type]"); ?>
                        </div>
                    </div>  
                    <div class="form-row">
                        <div class="col">
                            <h5>Open Pickup Time</h5>
                            <input class="form-control time" name="store_opt_list[wednesday][pickup][open]" value="<?php echo set_value('store_opt_list[wednesday][pickup][open]', ($wednesday && $status_wed != 'close' ? $wednesday['pickup'][$cst_status['open']] : '09:00'));?>" 
                            <?php echo $status_wed == 'close' || $type_wed == 'delivery_only' ? 'disabled' : '' ?>>
                            <?php echo form_error('store_opt_list[wednesday][pickup][open]'); ?>
                        </div>
                        <div class="col">
                            <h5>Close Pickup Time</h5>
                            <input class="form-control time" name="store_opt_list[wednesday][pickup][close]" value="<?php echo set_value('store_opt_list[wednesday][pickup][close]', ($wednesday && $status_wed != 'close' ? $wednesday['pickup']['close'] : '21:00'));?>" 
                            <?php echo $status_wed == 'close' || $type_wed == 'deliveyr_only' ? 'disabled' : '' ?>>
                            <?php echo form_error('store_opt_list[wednesday][pickup][close]'); ?>
                        </div>
                        <div class="col">
                            <h5>Open Delivery Time</h5>
                            <input class="form-control time" name="store_opt_list[wednesday][delivery][open]" value="<?php echo set_value('store_opt_list[wednesday][delivery][open]', ($wednesday && $status_wed != 'close' ? $wednesday['delivery'][$cst_status['open']] : '09:00'));?>" 
                            <?php echo $status_wed == 'close' || $type_wed == 'pickup_only' ? 'disabled' : '' ?>>
                            <?php echo form_error('store_opt_list[wednesday][delivery][open]'); ?>
                        </div>
                        <div class="col">
                            <h5>Close Delivery Time</h5>
                            <input class="form-control time" name="store_opt_list[wednesday][delivery][close]" value="<?php echo set_value('store_opt_list[wednesday][delivery][close]', ($wednesday && $status_wed != 'close' ? $wednesday['delivery']['close'] : '21:00'));?>" 
                            <?php echo $status_wed == 'close' || $type_wed == 'pickup_only' ? 'disabled' : '' ?>>
                            <?php echo form_error('store_opt_list[wednesday][delivery][close]'); ?>
                        </div>
                      </div>
                    </div>
                    <!-- End Wednesday -->

                    <!-- Start Thursday -->
                    <?php $status_thur = set_value('store_opt_list[thursday][status]', (!empty($thursday['status']) ? $thursday['status'] : '')) == 'open' ? 'open' : 'close'; ?>
                    <?php 
                        $color_thur         = '';
                        $checked_thur       = '';
                        $display_thur       = 'none';
                        $status_thursday    = 'close';
                       
                        if((!empty($msg) && $title_form == 'Edit') || empty($msg)){
                            if($status_thur == 'open'){
                                $color_thur      = 'limegreen';
                                $checked_thur    = 'checked';
                                $display_thur    = 'block';
                                $status_thursday = 'open';
                            }
                        }

                    ?>
                    <label id="thursday" class="form-control dd-handle" style="background : <?php echo $color_thur; ?>"> 
                    Thursday
                    <input class="float-right change-checkbox" type="checkbox" name="checkbox[thursday]" style="width:50px;height:20px;" data-day="thursday" <?php echo $checked_thur;?>>
                    </label>
                    
                    <div class="thursday" style="padding : 18px; display : <?php echo $display_thur; ?>">
                    <div class="form-row">
                        <div class="col">
                            <h5>Status</h5>
                            <input name="store_opt_list[thursday][status]" class="form-control m-b change-disable" disabled
                            value="<?php echo $status_thursday; ?>"> 
                            <input name="store_opt_list[thursday][status]" type="hidden"
                            value="<?php echo $status_thursday; ?>">
                        </div>
                    </div>
                    <div class="form-row">
                          <div class="col">
                              <h5>Store Type</h5>
                              <select class="form-control m-b change-type-disable" name="store_opt_list[thursday][st_type]" data-day="thursday" <?php echo $status_thur == 'close' ? 'disabled' : '' ?>>
                                      <?php 
                                      $type_thur = set_value('store_opt_list[thursday][st_type]', (!empty($thursday['st_type']) ? $thursday['st_type'] : ''));
                                      foreach ($cst_type as $key => $val): ?>
                                          <option value="<?php echo $key; ?>" <?php echo $type_thur == $key ? 'selected="selected"' : ''; ?>><?php echo $val; ?></option>
                                      <?php endforeach; ?>
                              </select>
                              <?php echo form_error("store_opt_list[thursday][st_type]"); ?>
                          </div>
                    </div>

                    <div class="form-row">
                        <div class="col">
                              <h5>Open Pickup Time</h5>
                              <input class="form-control time" name="store_opt_list[thursday][pickup][open]" value="<?php echo set_value('store_opt_list[thursday][pickup][open]', ($thursday && $status_thur != 'close' ? $thursday['pickup'][$cst_status['open']] : '09:00'));?>" 
                              <?php echo $status_thur == 'close' || $type_thur == 'delivery_only' ? 'disabled' : '' ?>>
                              <?php echo form_error('store_opt_list[thursday][pickup][open]'); ?>
                          </div>
                          <div class="col">
                              <h5>Close Pickup Time</h5>
                              <input class="form-control time" name="store_opt_list[thursday][pickup][close]" value="<?php echo set_value('store_opt_list[thursday][pickup][close]', ($thursday && $status_thur != 'close' ? $thursday['pickup']['close'] : '21:00'));?>" 
                              <?php echo $status_thur == 'close' || $type_thur == 'delivery_only' ? 'disabled' : '' ?>>
                              <?php echo form_error('store_opt_list[thursday][pickup][close]'); ?>
                          </div>
                          <div class="col">
                              <h5>Open Delivery Time</h5>
                              <input class="form-control time" name="store_opt_list[thursday][delivery][open]" value="<?php echo set_value('store_opt_list[thursday][delivery][open]', ($thursday && $status_thur != 'close' ? $thursday['delivery'][$cst_status['open']] : '09:00'));?>" 
                              <?php echo $status_thur == 'close' || $type_thur == 'pickup_only' ? 'disabled' : '' ?>>
                              <?php echo form_error('store_opt_list[thursday][delivery][open]'); ?>
                          </div>
                          <div class="col">
                              <h5>Close Delivery Time</h5>
                              <input class="form-control time" name="store_opt_list[thursday][delivery][close]" value="<?php echo set_value('store_opt_list[thursday][delivery][close]', ($thursday && $status_thur != 'close' ? $thursday['delivery']['close'] : '21:00'));?>" 
                              <?php echo $status_thur == 'close' || $type_thur == 'pickup_only' ? 'disabled' : '' ?>>
                              <?php echo form_error('store_opt_list[thursday][delivery][close]'); ?>
                          </div>
                      </div> 
                    </div>
                    <!-- End Thursday -->     

                    <!-- Start Friday -->
                    <?php $status_fri = set_value('store_opt_list[friday][status]', (!empty($friday['status']) ? $friday['status'] : '')) == 'open' ? 'open' : 'close'; ?>
                    <?php 
                        $color_fri      = '';
                        $checked_fri    = '';
                        $display_fri    = 'none';
                        $status_friday  = 'close';
                       
                        if((!empty($msg) && $title_form == 'Edit') || empty($msg)){
                            if($status_fri == 'open'){
                                $color_fri      = 'limegreen';
                                $checked_fri    = 'checked';
                                $display_fri    = 'block';
                                $status_friday  = 'open';
                            }
                        }
                    ?>
                    <label id="friday" class="form-control dd-handle" style="background : <?php echo $color_fri; ?>"> 
                    Friday
                    <input class="float-right change-checkbox" type="checkbox" name="checkbox[friday]" style="width:50px;height:20px;" data-day="friday" <?php echo $checked_fri;?>>
                    </label>
                    
                    <div class="friday" style="padding : 18px; display : <?php echo $display_fri; ?>">
                    <div class="form-row">
                        <div class="col">
                            <h5>Status</h5>
                            <input name="store_opt_list[friday][status]" class="form-control m-b change-disable" disabled
                            value="<?php echo $status_friday; ?>"> 
                            <input name="store_opt_list[friday][status]" type="hidden"
                            value="<?php echo $status_friday; ?>">
                        </div>
                    </div>
                      <div class="form-row">
                          <div class="col">
                              <h5>Store Type</h5>
                              <select class="form-control m-b change-type-disable" name="store_opt_list[friday][st_type]" data-day="friday" <?php echo $status_fri == 'close' ? 'disabled' : '' ?>>
                                      <?php                                     
                                      $type_fri = set_value('store_opt_list[friday][st_type]', (!empty($friday['st_type']) ? $friday['st_type'] : ''));
                                      foreach ($cst_type as $key => $val): ?>
                                          <option value="<?php echo $key; ?>" <?php echo  $type_fri === $key ? 'selected="selected"' : ''; ?>><?php echo $val; ?></option>
                                      <?php endforeach; ?>
                              </select>
                              <?php echo form_error("store_opt_list[friday][st_type]"); ?>
                          </div>
                      </div>
                      <div class="form-row">
                          <div class="col">
                              <h5>Open Pickup Time</h5>
                              <input class="form-control time" name="store_opt_list[friday][pickup][open]" value="<?php echo set_value('store_opt_list[friday][pickup][open]', ($friday && $status_fri != 'close' ? $friday['pickup'][$cst_status['open']] : '09:00'));?>" 
                              <?php echo $status_fri == 'close' || $type_fri == 'delivery_only' ? 'disabled' : '' ?>>
                              <?php echo form_error('store_opt_list[friday][pickup][open]'); ?>
                          </div>
                          <div class="col">
                              <h5>Close Pickup Time</h5>
                              <input class="form-control time" name="store_opt_list[friday][pickup][close]" value="<?php echo set_value('store_opt_list[friday][pickup][close]', ($friday && $status_fri != 'close' ? $friday['pickup']['close'] : '21:00'));?>" 
                              <?php echo $status_fri == 'close' || $type_fri == 'delivery_only' ? 'disabled' : '' ?>>
                              <?php echo form_error('store_opt_list[friday][pickup][close]'); ?>
                          </div>
                          <div class="col">
                              <h5>Open Delivery Time</h5>
                              <input class="form-control time" name="store_opt_list[friday][delivery][open]" value="<?php echo set_value('store_opt_list[friday][delivery][open]', ($friday && $status_fri != 'close' ? $friday['delivery'][$cst_status['open']] : '09:00'));?>" 
                              <?php echo $status_fri == 'close' || $type_fri == 'pickup_only' ? 'disabled' : '' ?>>
                              <?php echo form_error('store_opt_list[friday][delivery][open]'); ?>
                          </div>
                          <div class="col">
                              <h5>Close Delivery Time</h5>
                              <input class="form-control time" name="store_opt_list[friday][delivery][close]" value="<?php echo set_value('store_opt_list[friday][delivery][close]', ($friday && $status_fri != 'close' ? $friday['delivery']['close'] : '21:00'));?>" 
                              <?php echo $status_fri == 'close'  || $type_fri == 'pickup_only' ? 'disabled' : '' ?>>
                              <?php echo form_error('store_opt_list[friday][delivery][close]'); ?>
                          </div>
                      </div>
                    </div> 
                    <!-- End Friday -->

                    <!-- Start Saturday -->
                    <?php $status_sat = set_value('store_opt_list[saturday][status]', (!empty($saturday['status']) ? $saturday['status'] : '')) == 'open' ? 'open' : 'close'; ?>
                    <?php 
                        $color_sat          = ''; 
                        $checked_sat        = ''; 
                        $display_sat        = 'none';
                        $status_saturday    = 'close';
                       
                        if((!empty($msg) && $title_form == 'Edit') || empty($msg)){
                            if($status_sat == 'open'){
                                $color_sat      = 'limegreen';
                                $checked_sat    = 'checked';
                                $display_sat    = 'block';
                                $status_saturday  = 'open';
                            }
                        }
                    ?>
                    <label id="saturday" class="form-control dd-handle" style="background : <?php echo $color_sat; ?>"> 
                    Saturday
                    <input class="float-right change-checkbox" type="checkbox" name="checkbox[saturday]" style="width:50px;height:20px;" data-day="saturday" <?php echo $checked_sat;?>>
                    </label>
                    
                    <div class="saturday" style="padding : 18px; display : <?php echo $display_sat; ?>">
                    <div class="form-row">
                        <div class="col">
                            <h5>Status</h5>
                            <input name="store_opt_list[saturday][status]" class="form-control m-b change-disable" disabled
                            value="<?php echo $status_saturday; ?>"> 
                            <input name="store_opt_list[saturday][status]" type="hidden"
                            value="<?php echo $status_saturday; ?>">
                        </div>
                    </div>
                      <div class="form-row">
                          <div class="col">
                              <h5>Store Type</h5>
                              <select class="form-control m-b change-type-disable" name="store_opt_list[saturday][st_type]" data-day="saturday" <?php echo $status_sat == 'close' ? 'disabled' : '' ?>>
                                      <?php 
                                      $type_sat = set_value('store_opt_list[saturday][st_type]', (!empty($saturday['st_type']) ? $saturday['st_type'] : ''));
                                      foreach ($cst_type as $key => $val): ?>
                                          <option value="<?php echo $key; ?>" <?php echo $type_sat == $key ? 'selected="selected"' : ''; ?>><?php echo $val; ?></option>
                                      <?php endforeach; ?>
                              </select>
                              <?php echo form_error("store_opt_list[saturday][st_type]"); ?>
                          </div>
                      </div>
                      <div class="form-row">
                          <div class="col">
                              <h5>Open Pickup Time</h5>
                              <input class="form-control time" name="store_opt_list[saturday][pickup][open]" value="<?php echo set_value('store_opt_list[saturday][pickup][open]', ($saturday && $status_sat != 'close' ? $saturday['pickup'][$cst_status['open']] : '09:00'));?>" 
                              <?php echo $status_sat == 'close' || $type_sat == 'delivery_only' ? 'disabled' : '' ?>>
                              <?php echo form_error('store_opt_list[saturday][pickup][open]'); ?>
                          </div>
                          <div class="col">
                              <h5>Close Pickup Time</h5>
                              <input class="form-control time" name="store_opt_list[saturday][pickup][close]" value="<?php echo set_value('store_opt_list[saturday][pickup][close]', ($saturday && $status_sat != 'close' ? $saturday['pickup']['close'] : '21:00'));?>" 
                              <?php echo $status_sat == 'close' || $type_sat == 'delivery_only' ? 'disabled' : '' ?>>
                              <?php echo form_error('store_opt_list[saturday][pickup][close]'); ?>
                          </div>
                          <div class="col">
                              <h5>Open Delivery Time</h5>
                              <input class="form-control time" name="store_opt_list[saturday][delivery][open]" value="<?php echo set_value('store_opt_list[saturday][delivery][open]', ($saturday && $status_sat != 'close' ? $saturday['delivery'][$cst_status['open']] : '09:00'));?>" 
                              <?php echo $status_sat == 'close' || $type_sat == 'pickup_only' ? 'disabled' : '' ?>>
                              <?php echo form_error('store_opt_list[saturday][delivery][open]'); ?>
                          </div>
                          <div class="col">
                              <h5>Close Delivery Time</h5>
                              <input class="form-control time" name="store_opt_list[saturday][delivery][close]" value="<?php echo set_value('store_opt_list[saturday][delivery][close]', ($saturday && $status_sat != 'close' ? $saturday['delivery']['close'] : '21:00'));?>" 
                              <?php echo $status_sat == 'close' || $type_sat == 'pickup_only' ? 'disabled' : '' ?>>
                              <?php echo form_error('store_opt_list[saturday][delivery][close]'); ?>
                          </div>
                      </div>
                    </div>
                    <!-- End Saturday -->

                    <!-- Start Sunday -->
                    <?php $status_sun = set_value('store_opt_list[sunday][status]', (!empty($sunday['status']) ? $sunday['status'] : '')) == 'open' ? 'open' : 'close'; ?>
                    <?php 
                        $color_sun      = ''; 
                        $checked_sun    = ''; 
                        $display_sun    = 'none'; 
                        $status_sunday  = 'close';
                                
                       
                        if((!empty($msg) && $title_form == 'Edit') || empty($msg)){
                            if($status_sun == 'open'){
                                $color_sun      = 'limegreen';
                                $checked_sun    = 'checked';
                                $display_sun    = 'block';
                                $status_sunday  = 'open';
                            }
                        }
                    ?>
                    <label id="sunday" class="form-control dd-handle" style="background : <?php echo $color_sun; ?>"> 
                    Sunday
                    <input class="float-right change-checkbox" type="checkbox" name="checkbox[sunday]" style="width:50px;height:20px;" data-day="sunday" <?php echo $checked_sun;?>>
                    </label>
                    
                    <div class="sunday" style="padding : 18px; display : <?php echo $display_sun; ?>">
                    <div class="form-row">
                        <div class="col">
                            <h5>Status</h5>
                            <input name="store_opt_list[sunday][status]" class="form-control m-b change-disable" disabled
                            value="<?php echo $status_sunday; ?>"> 
                            <input name="store_opt_list[sunday][status]" type="hidden"
                            value="<?php echo $status_sunday; ?>">
                        </div>
                    </div>

                      <div class="form-row">
                          <div class="col">
                              <h5>Store Type</h5>
                              <select class="form-control m-b change-type-disable" name="store_opt_list[sunday][st_type]" data-day="sunday" <?php echo $status_sun == 'close' ? 'disabled' : '' ?>>
                                      <?php 
                                      $type_sun = set_value('store_opt_list[sunday][st_type]', (!empty($sunday['st_type']) ? $sunday['st_type'] : ''));
                                      foreach ($cst_type as $key => $val): ?>
                                          <option value="<?php echo $key; ?>" <?php echo $type_sun == $key ? 'selected="selected"' : ''; ?>><?php echo $val; ?></option>
                                      <?php endforeach; ?>
                              </select>
                              <?php echo form_error("store_opt_list[sunday][st_type]"); ?>
                          </div>
                      </div>
                      <div class="form-row">
                          <div class="col">
                              <h5>Open Pickup Time</h5>
                              <input class="form-control time" name="store_opt_list[sunday][pickup][open]" value="<?php echo set_value('store_opt_list[sunday][pickup][open]', ($sunday && $status_sun != 'close' ? $sunday['pickup'][$cst_status['open']] : '09:00'));?>" 
                              <?php echo $status_sun == 'close' || $type_sun == 'delivery_only' ? 'disabled' : '' ?>>
                              <?php echo form_error('store_opt_list[sunday][pickup][open]'); ?>
                          </div>
                          <div class="col">
                              <h5>Close Pickup Time</h5>
                              <input class="form-control time" name="store_opt_list[sunday][pickup][close]" value="<?php echo set_value('store_opt_list[sunday][pickup][close]', ($sunday && $status_sun != 'close' ? $sunday['pickup']['close'] : '21:00'));?>" 
                              <?php echo $status_sun == 'close' || $type_sun == 'delivery_only' ? 'disabled' : '' ?>>
                              <?php echo form_error('store_opt_list[sunday][pickup][close]'); ?>
                          </div>
                          <div class="col">
                              <h5>Open Delivery Time</h5>
                              <input class="form-control time" name="store_opt_list[sunday][delivery][open]" value="<?php echo set_value('store_opt_list[sunday][delivery][open]', ($sunday && $status_sun != 'close' ? $sunday['delivery'][$cst_status['open']] : '09:00'));?>" 
                              <?php echo $status_sun == 'close' || $type_sun == 'pickup_only' ? 'disabled' : '' ?>>
                              <?php echo form_error('store_opt_list[sunday][delivery][open]'); ?>
                          </div>
                          <div class="col">
                              <h5>Close Delivery Time</h5>
                              <input class="form-control time" name="store_opt_list[sunday][delivery][close]" value="<?php echo set_value('store_opt_list[sunday][delivery][close]', ($sunday && $status_sun != 'close' ? $sunday['delivery']['close'] : '21:00'));?>" 
                              <?php echo $status_sun == 'close' || $type_sun == 'pickup_only' ? 'disabled' : '' ?>>
                              <?php echo form_error('store_opt_list[sunday][delivery][close]'); ?>
                          </div>
                      </div>
                    </div>
                    <!-- End Sunday -->

                    <div class="hr-line-dashed"></div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary"><?php echo $title_form;?></button>
                        <button type="reset" class="btn btn-warning">Reset</button>
                    </div>
                    <?php echo form_close(); ?>
                </div>
            </div>
        <?php endif ?>
        </div>
    </div>
</div>
<!-- /.content-wrapper -->

<script>
$(document).ready(function(){

    // change status with checkbox
    $('.change-checkbox').click(function(){
        var day = $(this).data('day');
        if ($(this).is(":checked")) {
            $('#'+day+'').css("background","limeGreen");
            $('.'+day+'').css("display","block");	
            $('input[name="store_opt_list['+day+'][status]"]').val('open');
            $('select[name="store_opt_list['+day+'][st_type]"]').attr('disabled', false);
            $('input[name="store_opt_list['+day+'][pickup][open]"]').attr('disabled', false);
            $('input[name="store_opt_list['+day+'][pickup][close]"]').attr('disabled', false);
            $('input[name="store_opt_list['+day+'][delivery][open]"]').attr('disabled', false);
            $('input[name="store_opt_list['+day+'][delivery][close]"]').attr('disabled', false);
            $('input[name="store_opt_list['+day+'][pickup][open]"]').val('09:00');
            $('input[name="store_opt_list['+day+'][pickup][close]"]').val('21:00');
            $('input[name="store_opt_list['+day+'][delivery][open]"]').val('09:00');
            $('input[name="store_opt_list['+day+'][delivery][close]"]').val('21:00');
            $(".change-type-disable").change();
        } else {
            $('#'+day+'').css("background","");
            $('.'+day+'').css("display","none");	
            $('input[name="store_opt_list['+day+'][status]"]').val('close');
            $('select[name="store_opt_list['+day+'][st_type]"]').attr('disabled', true);
            $('input[name="store_opt_list['+day+'][pickup][open]"]').attr('disabled', true);
            $('input[name="store_opt_list['+day+'][pickup][close]"]').attr('disabled', true);
            $('input[name="store_opt_list['+day+'][delivery][open]"]').attr('disabled', true);
            $('input[name="store_opt_list['+day+'][delivery][close]"]').attr('disabled', true);
        }
    });

    //validation for store type
    $(".change-type-disable").change(function () {
        var type        = $(this).val();
        var day         = $(this).data('day');
        var status      = $('input[name="store_opt_list['+day+'][status]"]').val();
        var st_status   = $(".change-disabled").val();

        if(status == 'open'){
            if(type == 'pickup_only') {
                $('input[name="store_opt_list['+day+'][delivery][open]"]').attr('disabled', true);
                $('input[name="store_opt_list['+day+'][delivery][close]"]').attr('disabled', true); 
                $('input[name="store_opt_list['+day+'][pickup][open]"]').attr('disabled', false);
                $('input[name="store_opt_list['+day+'][pickup][close]"]').attr('disabled', false);             
            }else if(type == 'delivery_only') {
                $('input[name="store_opt_list['+day+'][pickup][open]"]').attr('disabled', true);
                $('input[name="store_opt_list['+day+'][pickup][close]"]').attr('disabled', true);
                $('input[name="store_opt_list['+day+'][delivery][open]"]').attr('disabled', false);
                $('input[name="store_opt_list['+day+'][delivery][close]"]').attr('disabled', false);
            }else{
                $('input[name="store_opt_list['+day+'][pickup][open]"]').attr('disabled', false);
                $('input[name="store_opt_list['+day+'][pickup][close]"]').attr('disabled', false);
                $('input[name="store_opt_list['+day+'][delivery][open]"]').attr('disabled', false);
                $('input[name="store_opt_list['+day+'][delivery][close]"]').attr('disabled', false);
            }
        }else if(st_status == 'close'){
            $('input[name="store_opt_list['+day+'][pickup][open]"]').attr('disabled', true);
            $('input[name="store_opt_list['+day+'][pickup][close]"]').attr('disabled', true);
            $('input[name="store_opt_list['+day+'][delivery][open]"]').attr('disabled', true);
            $('input[name="store_opt_list['+day+'][delivery][close]"]').attr('disabled', true);
        }
    });

    // datepicker for search
    $('#start_date, #end_date').datetimepicker({
        timepicker: false,
        format:'Y-m-d',
        lang:'en'
    });

   // timepicker for search
    $(".time").datetimepicker({
        datepicker:false,
        format:'H:i',
        lang:'en'
    });


});

</script> 

<script src="<?php echo ADMIN_TEMPLATE_URL;?>js/plugins/chosen/chosen.jquery.js"></script>
<script src="<?php echo ADMIN_TEMPLATE_URL;?>js/plugins/select2/select2.full.min.js"></script>
