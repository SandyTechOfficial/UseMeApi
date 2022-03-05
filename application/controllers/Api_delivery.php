<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
class Api_delivery extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        /*cache control*/
        $this
            ->output
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this
            ->output
            ->set_header('Pragma: no-cache');
        header('Content-Type: application/json');
        $this
            ->load
            ->helper('url');
        $this
            ->load
            ->model("Api_model");
        $this->load->library('email');
    }

    /* index of the admin. Default: Dashboard; On No Login Session: Back to login page. */
    public function index()
    {
       
    }
    
    /** Driver Block Status **/
    
    function driverBlockStatus($para1 = '')
    {
        if ($this
            ->Api_model
            ->singleselectbox('driver', 'driver_id', $para1, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            $loop = $this->db->get_where('driver',array('driver_id' => $para1))->result_array();
            
            if(count($loop) > 0)
            {
                foreach($loop as $row)
                {
                    $data = $row['block'] == 'true' ? true : false;
                }
                $res = true;
                $message = 'Details retrived successfully';
            }else{
                $res = false;
                $data = 'fail';
                $message = 'Driver not Exist';
                
            }
            $responce = array(
                'success' => $res,
                'data' => $data,
                'message' => $message,
            );
        }
        else
        {
            $responce = $this
                ->Api_model
                ->tokenfailed();
        }

        echo json_encode($responce);
    }
    
    //Driver ON OFF
    
    function driver_status_update($driver_id,$status){
        
        if($driver_id != '' && $status != '')
        {
            $this
                ->db
                ->where('driver_id', $driver_id);
            $this
                ->db
                ->update('driver', array('available'=>$status));
            $res = true;
            $message = "Status Updated Successfully";
        }else{
            $res = false;
            $message = "Invalid details";
        }
        $responce = array(
                'success' => $res,
                'message' => $message,
            );
       echo json_encode($responce);
    }
    
    
    
    //done
    
    

    /** Driver Update **/
    function driver_profile_update($para1 = ''){
        
      /*  if ($this
            ->Api_model
            ->singleselectbox('driver', 'driver_id', $para1, 'token') == $this
            ->input
            ->get('api_token', true))
        { */
            $content_data = file_get_contents("php://input");
            $get_data = json_decode($content_data);
            $data['name'] = $get_data->name;
            $data['last_name'] = $get_data->last_name;
            $data['phone'] = $get_data->phone;
            $data['address'] = $get_data->address;
            $data['email'] = $get_data->email;
            $data['age'] = $get_data->age;
            $data['gender'] = $get_data->gender;
            $data['latitude'] = $get_data->latitude;
            $data['longitude'] = $get_data->longitude;
            
            $this
                ->db
                ->where('driver_id', $para1);
            $this
                ->db
                ->update('driver', $data);
            $responce = array(
                'success' => true,
                'data' => 'Success',
                'message' => 'Driver profile updated successfully',
            );
      /*  }
        else
        {
            $responce = $this
                ->Api_model
                ->tokenfailed();
        }*/

       echo json_encode($responce);
    }
    function driver_profile_image_update($para1 = ''){
        if ($this
            ->Api_model
            ->singleselectbox('driver', 'driver_id', $para1, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            $path = $_FILES['image']['name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $ext = 'png';
            $data_banner['image'] = 'driver_' . $para1 . '.' . $ext;
            $data_banner1['image'] = 'driver_' . $para1 . '.' . $ext;
            $data_banner1['imagepath'] = $path;
            $this
                ->crud_model
                ->file_up("image", "driver", $para1, '', 'no', '.' . $ext);
            $this
                ->db
                ->where('driver_id', $para1);
            $this
                ->db
                ->update('driver', $data_banner);
            $responce = array(
                'success' => true,
                'data' => 'Success',
                'message' => 'Profile Image Updated Successfully',
            );
        }
        else
        {
            $responce = $this
                ->Api_model
                ->tokenfailed();
        }

       echo json_encode($responce);
    }
    function profile_image($para1 = '', $para2 = '')
    {
        $image = $_FILES['image']['name'];
        $imagePath = 'uploads/profile_image/' . $image;
        $tmp_name = $_FILES['image']['tmp_name'];
        move_uploaded_file($tmp_name, $imagePath);
        $row = json_decode($_POST['name'], true);
        $data['username'] = $row['firstname'];
        $data['lastname'] = $row['lastname'];
        $data['dob'] = $row['dob'];
        $data['gender'] = $row['gender'];
        $data['email'] = $row['email'];
        $data['password'] = sha1($row['password']);
        $data['mobile'] = $row['mobile'];
        $data['address1'] = $row['address1'];
        $data['address2'] = $row['address2'];
        $data['city'] = $row['city'];
        $data['state'] = $row['state'];
        $data['zipcode'] = $row['zipcode'];
        $data['aboutyou'] = $row['aboutyou'];
        $data['work_exp'] = $row['workexp'];
        $data['latitude'] = $row['latitude'];
        $data['longitude'] = $row['longtitude'];
        $data['date'] = time();
        $data['status'] = 'success';
        $data['token'] = '1';
        $data['device_id'] = '1';
        $data['livestatus'] = 'true';
        $this
            ->db
            ->insert('provider', $data);
        $id = $this
            ->db
            ->insert_id();

        $path = $_FILES['image']['name'];
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $data_banner['image'] = 'provider_' . $id . '.' . $ext;
        $this
            ->crud_model
            ->file_up("image", "provider", $id, '', 'no', '.' . $ext);
        $this
            ->db
            ->where('provider_id', $id);
        $this
            ->db
            ->update('provider', $data_banner);
        foreach ($row['category'] as $row1)
        {
            $data1['categoryName'] = $row1['categoryName'];
            $data1['categoryId'] = $row1['categoryId'];
            $data1['subcategoryName'] = $row1['subcategoryName'];
            $data1['subcategoryId'] = $row1['subcategoryId'];
            $data1['experience'] = $row1['experience'];
            $data1['chargePreHrs'] = $row1['chargePreHrs'];
            $data1['quickPitch'] = $row1['quickPitch'];
            $data1['userid'] = $id;
            $this
                ->db
                ->insert('provider_databook', $data1);
        }

    }
    /** settings **/
    function settings()
    {
        $data = ["success" => true, "data" => ["app_name" => $this
            ->Api_model
            ->get_type_name_by_id('general_settings', '3', 'value') , "enable_stripe" => "1", "phone" => $this
            ->Api_model
            ->get_type_name_by_id('general_settings', '88', 'value') , "default_currency" => $this
            ->Api_model
            ->get_currency() , "enable_paypal" => "1", "address" => $this
            ->Api_model
            ->get_type_name_by_id('general_settings', '89', 'value') , "google_maps_key" => $this
            ->Api_model
            ->get_type_name_by_id('general_settings', '67', 'value') , "mobile_language" => "en", "app_version" => "1.0.0", "enable_version" => "1", "currency_right" => $this
            ->Api_model
            ->get_type_name_by_id('general_settings', '83', 'value') , "default_currency_decimal_digits" => "2", "enable_razorpay" => $this
            ->Api_model
            ->get_type_name_by_id('general_settings', '84', 'value') ], "message" => "Settings retrieved successfully"];
        echo json_encode($data);

    }
    /** orders **/
    function order($para1 = '', $para2 = '', $para3 = '')
    {

        if ($para1 == 'list')
        {

            if ($this
                ->Api_model
                ->singleselectbox('driver', 'driver_id', $para2, 'token') == $this
                ->input
                ->get('api_token', true))
            {

                $this
                    ->db
                    ->order_by('sale_id', 'desc');
                $loop = $this
                    ->db
                    ->get_where('sale', array(
                    'delivery_assigned' => $para2
                ))->result_array();
                // count($loop);
                foreach ($loop as $row2)
                {
                    $shippingaddress[] = json_decode($row2['shipping_address'], true);
                    $data[] = array(
                        'userid' => $row2['buyer'],
                        'sale_code' => $row2['sale_code'],
                        'product_details' => json_decode($row2['product_details'], true) ,
                        'address' => $shippingaddress,
                        'shipping' => $row2['shipping'],
                        'payment_type' => $row2['payment_type'],
                        'payment_status' => $row2['payment_status'],
                        'payment_timestamp' => $row2['payment_timestamp'],
                        'grand_total' => $row2['grand_total'],
                        'sale_datetime' => $row2['sale_datetime'],
                        'delivary_datetime' => $row2['delivary_datetime'],
                        'deliver_assignedtime' => $row2['deliver_assignedtime'],
                        'delivery_state' => $row2['delivery_state'],
                    );
                }

                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'order retrieved successfully',
                );
            }
            else
            {
                $responce = $this
                    ->Api_model
                    ->tokenfailed();
            }
        }
        else if ($para1 == 'orderhistory')
        {

            if ($this
                ->Api_model
                ->singleselectbox('driver', 'driver_id', $para2, 'token') == $this
                ->input
                ->get('api_token', true))
            {

                $this
                    ->db
                    ->order_by('sale_id', 'desc');
                $loop = $this
                    ->db
                    ->get_where('sale', array(
                    'delivery_assigned' => $para2
                ))->result_array();

                foreach ($loop as $row2)
                {
                    $shippingaddress[] = json_decode($row2['shipping_address'], true);
                    $data[] = array(
                        'userid' => $row2['buyer'],
                        'sale_code' => $row2['sale_code'],
                        'product_details' => json_decode($row2['product_details'], true) ,
                        'address' => $shippingaddress,
                        'shipping' => $row2['shipping'],
                        'payment_type' => $row2['payment_type'],
                        'payment_status' => $row2['payment_status'],
                        'payment_timestamp' => $row2['payment_timestamp'],
                        'grand_total' => $row2['grand_total'],
                        'sale_datetime' => $row2['sale_datetime'],
                        'delivary_datetime' => $row2['delivary_datetime'],
                        'deliver_assignedtime' => $row2['deliver_assignedtime'],
                        'delivery_state' => $row2['delivery_state'],
                    );
                }

                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'order retrieved successfully',
                );
            }
            else
            {
                $responce = $this
                    ->Api_model
                    ->tokenfailed();
            }

        }
        else if ($para1 == 'orderDetails')
        {

            if ($this
                ->Api_model
                ->singleselectbox('driver', 'driver_id', $para2, 'token') == $this
                ->input
                ->get('api_token', true))
            {

                $this
                    ->db
                    ->order_by('sale_id', 'desc');
                $loop = $this
                    ->db
                    ->get_where('sale', array(
                    'delivery_assigned' => $para2,
                    'sale_code' => $para3
                ))->result_array();

                foreach ($loop as $row2)
                {
                    $shippingaddress = json_decode($row2['shipping_address'], true);
                    $this
                        ->db
                        ->select('address1, display_name, phone, latitude, longitude');
                    $loop1 = $this
                        ->db
                        ->get_where('vendor', array(
                        'vendor_id' => $row2['vendor']
                    ))->result_array();
                    foreach ($loop1 as $row1)
                    {
                        $shipped = array(
                            'addressSelect' => $row1['address1'],
                            'username' => $row1['display_name'],
                            'phone' => $row1['phone'],
                            'userId' => $row2['vendor'],
                            'latitude' => floatval($row1['latitude']) ,
                            'longitude' => floatval($row1['longitude']) ,
                            'isDefault' => 'true',
                            'id' => ''
                        );
                    }
                    $data = array(
                        'userid' => $row2['buyer'],
                        'sale_code' => $row2['sale_code'],
                        'status' => $row2['status'],
                        'product_details' => json_decode($row2['product_details'], true) ,
                        'address' => $shippingaddress,
                        'shop' => $shipped,
                        'payment_type' => $row2['payment_type'],
                        'payment_status' => $row2['payment_status'],
                        'payment_timestamp' => $row2['payment_timestamp'],
                        'grand_total' => $row2['grand_total'],
                        'sale_datetime' => $row2['sale_datetime'],
                        'delivary_datetime' => $row2['delivary_datetime'],
                        'deliver_assignedtime' => $row2['deliver_assignedtime'],
                        'delivery_state' => $row2['delivery_state'],
                    );
                }

                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'orderdetails retrieved successfully',
                );
            }
            else
            {
                $responce = $this
                    ->Api_model
                    ->tokenfailed();
            }

        }
        echo json_encode($responce);
    }
    /** deliverStatus **/
    function deliverStatus($para1 = '', $para2 = '', $para3 = '', $para4 = '')
    {
        if ($para1 == 'update')
        {
            if($para2 == 'Start')
            {
                $this->db->where('sale_code',$para3);
                $this->db->update('sale',array('total_km'=>$para4));
            }
            if ($para2 != 'Delivered' && $para2 != 'Rejected')
            {

                $data['delivery_state'] = $para2;
                $this
                    ->db
                    ->where('sale_code', $para3);
                $this
                    ->db
                    ->update('sale', $data);
                $responce = array(
                    'success' => true,
                    'data' => true,
                    'message' => 'order retrieved successfully',
                );
            }else if($para2 == 'Rejected')
            {
                
                $driver_id = $this->db->get_where('sale',array('sale_code' => $para3))->row_array();
                
                
                $this->db->insert('rejected_orders',array('driver_id'=>$driver_id['delivery_assigned'], 'sale_code'=>$para3));
                
                $dataxy = array(
                    'delivery_assigned' => "0",
                    'delivery_state' => "0",
                    'status' => 'Rejected',
                    'deliver_assignedtime' => "0"
                    );
                
                $this->db->where('sale_code',$para3);
                $this->db->update('sale', $dataxy);
      
                $responce = array(
                    'success' => true,
                    'data' => true,
                    'message' => 'order Rejected successfully',
                );
            }
            else
            {

                $this
                    ->db
                    ->select('otp');
                $optloop = $this
                    ->db
                    ->get_where('sale', array(
                    'sale_code' => $para3
                ))->result_array();
               $delivery_status = json_decode($this
                        ->db
                        ->get_where('sale', array(
                        'sale_code' => $para3
                    ))->row()->delivery_status, true);
                    $delivery_status['deliverstatus'] = true;
                    $delivery_status['delivered'] = time();
                    $data['delivery_state'] = $para2;
                    $data['delivery_status'] = json_encode($delivery_status);
                    $data['status'] = 'Delivered';
                    $this
                        ->db
                        ->where('sale_code', $para3);
                    $this
                        ->db
                        ->update('sale', $data);
                    $responce = array(
                        'success' => true,
                        'data' => true,
                        'message' => 'order retrieved successfully',
                    );

            }
            echo json_encode($responce);
        }
    }
    /** login **/
    function login($para1 = '', $para2 = '')
    {
        $content_data = file_get_contents("php://input");
        $get_data = json_decode($content_data);
        
        $loop = $this
            ->db
            ->get_where('driver', array(
            'email' => $get_data->email,
            'password' => sha1($get_data->password) ,
            'block' => 'false'
        ))
            ->result_array();
        
        if(count($loop) > 0)
        {
            $this
                ->db
                ->where('email', $get_data->email);
            $this
                ->db
                ->update('driver', array('livestatus'=>'true'));
        }
        
        $loop2 = $this
            ->db
            ->get_where('driver', array(
            'email' => $get_data->email,
            'password' => sha1($get_data->password) ,
            'block' => 'false'
        ))
            ->result_array();
        
        if (count($loop2) > 0)
        {
                
            foreach ($loop2 as $row)
            {
                $data1['token'] = sha1($row['user_id']);
                $data = array(
                    'id' => $row['driver_id'],
                    'name' => $row['name'],
                    'last_name' => $row['last_name'],
                    'age' => $row['age'],
                    'email' => $row['email'],
                    'api_token' => $data1['token'],
                    'password' => 'hidden',
                    'device_token' => $row['device_id'],
                    'gender' => $row['gender'],
                    'phone' => $row['phone'],
                    'status' => $row['status'],
                    'auth' => true,
                    'liveStatus' => $row['livestatus'] == 'true' ? true : false,
                    'address' => $row['address'],
                    'image' => base_url() . 'uploads/driver_image/' . $row['image'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude']
                );
            }

            $this
                ->db
                ->where('driver_id', $row['driver_id']);
            $this
                ->db
                ->update('driver', $data1);
                
            $res = true;
            $message = 'profile retrieved successfully';
        }
        else
        {
            $res = false;
            $data = 'fail';
            $message = "Please Enter Valid Credentials.";
        }

        $responce = array(
            'success' => $res,
            'data' => $data,
            'message' => $message,
        );
        echo json_encode($responce);

    }
    /** statusUpdate **/
    function statusUpdate($para1 = '', $para2 = '', $para3 = '')
    {

        if ($this
            ->Api_model
            ->singleselectbox('driver', 'driver_id', $para2, 'token') == $this
            ->input
            ->get('api_token', true))
        {

            $data['livestatus'] = $para1;
            $this
                ->db
                ->where('driver_id', $para2);
            $this
                ->db
                ->update('driver', $data);
            $responce = array(
                'success' => true,
            );

        }
        else
        {
            $responce = $this
                ->Api_model
                ->tokenfailed();
        }
        echo json_encode($responce);
    }

    /** latlongUpdate **/
    function latlongUpdate($para1 = '', $para2 = '', $para3 = '')
    {
        if ($this
            ->Api_model
            ->singleselectbox('driver', 'driver_id', $para3, 'token') == $this
            ->input
            ->get('api_token', true))
        {

            $data['latitude'] = $para1;
            $data['longitude'] = $para2;
            $this
                ->db
                ->where('driver_id', $para3);
            $this
                ->db
                ->update('driver', $data);
            $responce = array(
                'success' => true,
            );

        }
        else
        {
            $responce = $this
                ->Api_model
                ->tokenfailed();
        }
        echo json_encode($responce);

    }

    /** dashboard **/
    function dashboard($para1 = '', $para2 = '', $para3 = '')
    {
        if ($this
            ->Api_model
            ->singleselectbox('driver', 'driver_id', $para2, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            if ($para1 == 'topbar')
            {
                $today = date("Y-m-d");
               
                $start = $this
                    ->Api_model
                    ->date_timestamp($today, 'start');
                $end = $this
                    ->Api_model
                    ->date_timestamp($today, 'end');
                
                
                $data = array(
                    'todayOrders' => $this
                        ->Api_model
                        ->count_4wcopt('vendor_invoice', 'driver_id', $para2, 'method !=', 'cancelled', 'deliver_assignedtime >=', $start, 'deliver_assignedtime <=', $end) ,
                    'totalCompleted' => $this
                        ->Api_model
                        ->count_2wcopt('vendor_invoice', 'driver_id', $para2, 'method !=', 'cancelled') ,
                    'todayEarn' => floatval($this
                        ->Api_model
                        ->sumof_sammaryReportdatewise('vendor_invoice', 'driver_id', $para2, 'deliver_assignedtime >=', $start, 'deliver_assignedtime <=', $end, 'driver_fees') + $this
                        ->Api_model
                        ->sumof_sammaryReportdatewise('vendor_invoice', 'driver_id', $para2, 'deliver_assignedtime >=', $start, 'deliver_assignedtime <=', $end, 'driver_tips')) ,
                    'totalEarn' => floatval($this
                        ->Api_model
                        ->sumof_sammaryReport('vendor_invoice', 'driver_id', $para2, 'driver_fees') + $this
                        ->Api_model
                        ->sumof_sammaryReport('vendor_invoice', 'driver_id', $para2, 'driver_tips')) ,
                );

                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'topbar retrieved successfully',
                );
            }
        }
        else
        {
            $responce = $this
                ->Api_model
                ->tokenfailed();

        }

        echo json_encode($responce);

    }

    /** register **/
    function register($para1 = '', $para2 = '', $para3 = '')
    {

        $row = json_decode($_POST['name'], true);
        
        $loop = $this
            ->db
            ->get_where('driver', array(
            'email' => $row['email']
        ))
            ->result_array();
        if (count($loop) == 0)
        {
                $data['name'] = $row['firstname'];
                $data['last_name'] = $row['lastname'];
                $data['date'] = time();
                $data['status'] = 'waiting';
                $data['age'] = $row['dob'];
                $data['gender'] = $row['gender'];
                $data['email'] = $row['email'];
                $data['password'] = sha1($row['password']);
                $data['phone'] = $row['mobile'];
                $data['address'] = $row['address1'] . ',' . $row['address2'] . ',' . $row['city'] . ',' . $row['state'] . ',' . $row['zipcode'] . '.';
                $data['latitude'] = $row['latitude'];
                $data['longitude'] = $row['longtitude'];
                $data['store_id'] = $row['storeId'];
                $data['drivingMode'] = $row['drivingMode'];
                $data['token'] = '';
                $data['device_id'] = '1';
                $data['livestatus'] = 'false';
                $data['block'] = 'false';
        
               $this
                    ->db
                    ->insert('driver', $data);
                    
                $id = $this
                        ->db
                        ->insert_id();
        
                $path = $_FILES['image']['name'];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $ext = 'png';
                $data_banner['image'] = 'driver_' . $id . '.' . $ext;
                $this
                    ->crud_model
                    ->file_up("image", "driver", $id, '', 'no', '.' . $ext);
                $this
                    ->db
                    ->where('driver_id', $id);
                $this
                    ->db
                    ->update('driver', $data_banner);
                    
            $res = true;
            $data1 = 'Success';
            $message = "Registered Successfully";
        }else{
            $res = false;
            $data1 = 'fail';
            $message = "Email Id already exists";
        }
        $response = array(
            'success' => $res,
            'data' => $data1,
            'message' => $message,
            );

        echo json_encode($response);

    }
    
    function demoMail(){
    $this->load->library('email');
                        $config = array();
                        $config['protocol'] = 'smtp';
                        $config['smtp_host'] = 'smtp-relay.sendinblue.com';
                        $config['smtp_user'] = 'ninad@hostingduty.com';
                        $config['smtp_pass'] = '7sDJCYQUzVWSRjxK';
                        $config['smtp_port'] = 587;
                        $this->email->initialize($config);
                        $this->email->set_newline("\r\n");  
                        $from_email = "support@gorunn.xyz";
                        $to_email ="richamandhan65@gmail.com";
                        $this->email->from($from_email, 'OTP Verification');
                        $this->email->to($to_email);
                        $this->email->subject('Verify OTP');
                        $this->email->message('Hello');        
                
                if($this->email->send()){
                   echo "Done";
                }else{
                    show_error($this->email->print_debugger());
                echo "Not Done";
                }
}
    
    function sendOTPOnEmail()
    {
        $this->load->library('email');
        $email = $this->input->post('email');

        if ($email != '' && $email != null) {

            $check_email = $this->db->get_where('driver', array('email' => $email))->result_array();

            $count_rows = count($check_email);

            if ($count_rows > 0) {
                $data = "";
                $res = false;
                $msg = "Email Already exists";
            } else {
                $random_otp = rand(1000, 9999);
                $message = "Your OTP is : $random_otp";
                
                        $config = array();
                        $config['protocol'] = 'smtp';
                        $config['smtp_host'] = 'smtp-relay.sendinblue.com';
                        $config['smtp_user'] = 'ninad@hostingduty.com';
                        $config['smtp_pass'] = '7sDJCYQUzVWSRjxK';
                        $config['smtp_port'] = 587;
                        $this->email->initialize($config);
                        $this->email->set_newline("\r\n");
                        $from_email = "support@gorunn.xyz";
                        $to_email = $email;
                        $this->email->from($from_email, 'OTP Verification');
                        $this->email->to($to_email);
                        $this->email->subject('Verify OTP');
                        $this->email->message($message);        
                
                if($this->email->send()){
                    $data['email'] = $email;
                    $data['otp'] = $random_otp;
                    $res = true;
                    $msg = 'OTP Sent Successfully';
                }else{
                    $data ='';
                    $res = false;
                    $msg = 'Email Not Sent. Please enter valid email';
                }
                
            }

        }else{
            $msg = "Email Already exists";
            $data = "";
            $res = false;
        }
        
        $responce = array(
                'success' => $res,
                'data' => $data,
                'message' => $msg
            );
        echo json_encode($responce);
    }
    
     function sendOTPOnSMS()
    {

        $phone = $this->input->post('phone');

        if ($phone != '' && $phone != null) {

            $check_phone = $this->db->get_where('driver', array('phone' => $phone))->result_array();

            $count_rows = count($check_phone);

            if ($count_rows > 0) {
                $data = "";
                $res = false;
                $msg = "Mobile Number Already exists";
            } else {
                    $otp = rand(1000, 9999);
                
					$authKey = "154897A8WTt9Ml45c61b747f4P1";
					$contactNumber = $phone;
					$senderId = "Bkisan";        
					$message = urlencode("Your OTP is ".$otp);
					$route = 4;
					$postData = array(
						'authkey' => $authKey,
						'mobiles' => $contactNumber,
						'message' => $message,
						'sender' => $senderId,
						'route' => $route
					);
			
					//API URL
					$url="http://api.msg91.com/api/sendhttp.php";
					$ch = curl_init();
					curl_setopt_array($ch, array(
						CURLOPT_URL => $url,
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_POST => true,
						CURLOPT_POSTFIELDS => $postData
					));
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					curl_exec($ch);
					curl_close($ch);
                
                $data['mobile'] = $phone;
                $data['otp'] = $otp;
                $res = true;
                $msg = 'OTP Sent Successfully';
                
            }

        }else{
            $msg = "Please Enter Valid Mobile Number";
            $data = "";
            $res = false;
        }
        
        $responce = array(
                'success' => $res,
                'data' => $data,
                'message' => $msg
            );
        echo json_encode($responce);
    }
    
    function order_cancellation_count_by_driver($driver_id){
        if($driver_id != null && $driver_id != 0)
        {
            $cancelled_orders = $this->db->get_where('order_cancelled_by_driver',array('driver_id'=>$driver_id));
            if($cancelled_orders->num_rows() > 0)
            {
                $records = $cancelled_orders->result_array();
                $total_counts = $cancelled_orders->num_rows();
               // $data['data'] = array();
                foreach($records as $row)
                {
                    $new_data[] = array(
                        'sale_code'=>$row['sale_code'],
                        'date'=>$row['date']
                    );
                }
                $data = $new_data;
                $res = true;
                $msg="Details Fetched Successfully";
            }else{
                $msg = "No records Found";
                $total_counts = 0;
                $data = array();
                $res = false;
            }
        }else{
            $total_counts = 0;
            $msg = "Driver Id should not be null";
            $data = array();
            $res = false;
        }
        
        $responce = array(
                'success' => $res,
                'total_counts' => $total_counts,
                'data' => $data,
                'message' => $msg
            );
        echo json_encode($responce);
    }
    
    function sale_data_between_date(){
        $driver_id = $this->input->post('driver_id');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        
        if($driver_id != '' && $start_date != '' && $end_date != '')
        {
            date_default_timezone_set('Asia/Kolkata');
            $st_date = date('Y-m-d', strtotime($start_date));
            $ed_date = date('Y-m-d', strtotime($end_date));
            $sale_array = array();
          
            $sale_entries = $this->db->get_where('sale',array('delivery_assigned'=>$driver_id));
            if($sale_entries->num_rows() > 0)
            {
                $sale_rows = $sale_entries->result_array();
                foreach($sale_rows as $row){
                    $sale_datetime = date("Y-m-d",$row['sale_datetime']);
                    if($sale_datetime >= $st_date && $sale_datetime <= $ed_date)
                    {
                        $sale_array[] = array(
                            'sale_code' => $row['sale_code']
                        );
                    }
                }
                $sale_arr_size =  count($sale_array);
                if($sale_arr_size > 0)
                {
                    $total_km = 0;
                    foreach($sale_array as $arr)
                    {
                        $sale_dat = $this->db->get_where('sale',array('sale_code'=>$arr['sale_code']))->row_array();
                        $total_km += $sale_dat['total_km'];
                        $sale_dat_arr[] = array(
                            'orderi_id'=> $sale_dat['sale_code'],
                    		'datetime' => date('d-m-Y H:i:s', $sale_dat['sale_datetime']),
                    		'grand_total' => $sale_dat['grand_total'],
                    		'km' => $sale_dat['total_km']
                        ); 
                    }
                    
                    $res = true;
                    $total_kilom = $total_km;
                    $data = $sale_dat_arr;
                    $msg = "Details Fetched Successfully";
                }else{
                   $res = false;
                   $total_kilom = 0;
                    $data = array();
                    $msg = "No records found"; 
                }
                
            }else{
                $res = false;
                $total_kilom = 0;
                $data = array();
                $msg = "No records found";
            }
            
        }else{
            $res = false;
            $total_kilom = 0;
            $data = array();
            $msg = "Invalid Details";
        }
        
        $responce = array(
                'success' => $res,
                'total_km'=> $total_kilom,
                'data' => $data,
                'message' => $msg
            );
        echo json_encode($responce);
    }

}
/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */

