<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
class Api_vendor extends CI_Controller
{

    function __construct()
    {
        parent::__construct();
        /*cache control*/
        header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET,HEAD,OPTIONS,POST,PUT");
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");

        header('Content-Type: application/json');
        $this
            ->load
            ->helper('url');
        $this
            ->load
            ->model("Api_model");
        $this->load->library('Curl');

    }

   
   /* setting */
    function setting()
    {

        $data = ["success" => true, "data" => ["app_name" => $this
            ->Api_model
            ->get_type_name_by_id('general_settings', '3', 'value') , "default_delivery_fees" => $this
            ->Api_model
            ->get_type_name_by_id('general_settings', '86', 'value') , "min_purchase_df" => $this
            ->Api_model
            ->get_type_name_by_id('general_settings', '92', 'value') , "default_currency" => $this
            ->Api_model
            ->get_currency() , "enable_paypal" => "1", "google_maps_key" => $this
            ->Api_model
            ->get_type_name_by_id('general_settings', '67', 'value') , "mobile_language" => "en", "app_version" => "1.0.0", "enable_version" => "1", "currency_right" => $this
            ->Api_model
            ->get_type_name_by_id('general_settings', '83', 'value') , "default_currency_decimal_digits" => "2", "razorpay_key" => $this
            ->Api_model
            ->get_type_name_by_id('general_settings', '84', 'value') , "language" => 'English', "autoassing" => $this
            ->Api_model
            ->get_type_name_by_id('general_settings', '91', 'value') ? true : false,

        ], "message" => "Settings retrieved successfully"];
        echo json_encode($data);

    }

 
     /* export action */
    function export_action($para1 = '', $para2 = '', $para3 = '')
    {

        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $para2, 'token') == $this
            ->input
            ->get('api_token', true))
        {

            $this
                ->load
                ->model("Ieport_model");
            $date = date("Y-m-d");

            if ($para1 == 'sales')
            {

                $filename = $date . '_sales.csv';
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=$filename");
                header("Content-Type: application/csv; ");
                $usersData = $this
                    ->Ieport_model
                    ->fetch_data('sales', $para2);

            }
            else if ($para1 == 'orders')
            {

                $filename = $date . '_orders.csv';
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=$filename");
                header("Content-Type: application/csv; ");
                $usersData = $this
                    ->Ieport_model
                    ->fetch_data('orders', $para2);

            }
            else if ($para1 == 'my_productlist' && $para3 != 2)
            {

                $filename = $date . '_my_productlist.csv';
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=$filename");
                header("Content-Type: application/csv; ");
                $usersData = $this
                    ->Ieport_model
                    ->myproduct('export', $para2);
            }
            else if ($para1 == 'my_productlist' && $para3 == 2)
            {

                $filename = $date . '_my_productlist.csv';
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=$filename");
                header("Content-Type: application/csv; ");
                $usersData = $this
                    ->Ieport_model
                    ->myre_product('export', $para2);
            }
            else if ($para1 == 'my_variantlist' && $para3 != 2)
            {

                $filename = $date . '_my_variantlist.csv';
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=$filename");
                header("Content-Type: application/csv; ");
                $usersData = $this
                    ->Ieport_model
                    ->myvariant('export', $para2);
            }
            else if ($para1 == 'my_variantlist' && $para3 == 2)
            {

                $filename = $date . '_my_variantlist.csv';
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=$filename");
                header("Content-Type: application/csv; ");
                $usersData = $this
                    ->Ieport_model
                    ->myre_variant('export', $para2);
            }
            else if ($para1 == 'my_addons' && $para3 == 2)
            {

                $filename = $date . '_my_addions.csv';
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachment; filename=$filename");
                header("Content-Type: application/csv; ");
                $usersData = $this
                    ->Ieport_model
                    ->myaddons('export', $para2);
            }

            $file = fopen('php://output', 'w');

            $header = array(
                "",
                "",
                ""
            );
            fputcsv($file, $header);
            foreach ($usersData as $key => $line)
            {
                fputcsv($file, $line);
            }

            fputcsv($file, '1');
            fclose($file);
            exit;

        }
        else
        {
            $responce = $this
                ->Api_model
                ->tokenfailed();
        }
    }


    /** Order Details by Order Id(Sale code) */
    
    
     function OrderDetails($salecode = '')
    {
            $loop = $this->db->get_where('sale',array('sale_code' => $salecode))->result_array();
            
            if(count($loop) > 0)
            {
                foreach($loop as $row)
                {
                    $loop1 = json_decode($row['product_details'], true);
                    $i = 1;
                    foreach ($loop1 as $row1)
                    {
                        if ($i == 1)
                        {
                            $image = $row1['image'];
                            $name = $row1['product_name'];
                        }
                        $i++;
                    }
                    date_default_timezone_set('Asia/Kolkata');
                    $date = date('M, d Y, h:i A', $row['sale_datetime']);
                    $data[] = array(
                        'status' => $row['status'],
                        'price' =>$row['grand_total'],
                        'contact'=>$this
                            ->db
                            ->get_where('user', array(
                            'user_id' => $row['buyer']
                        ))->row()->phone,
                        'username'=> $this
                            ->db
                            ->get_where('user', array(
                            'user_id' => $row['buyer']
                        ))->row()->username,
                        'userId'=>$row['buyer'],
                        'paymentType'=>$row['payment_type'],
                        'details'=> '1',
                        'itemImage'=> $image,
                        'date'=> $date,
                        'itemTotal'=>count($loop1) ,
                        'orderType'=>$row['order_type'],
                        'deliverSlot'=>$row['delivery_slot'],
                        'shopTypeId'=>$this
                            ->db
                            ->get_where('shop_focus', array(
                            'shop_focus_id' => $row['focus_id']
                        ))->row()->shop_type,
                        'pImage'=>$row['p_image'] ? base_url() . 'uploads/sales_image/' . $row['p_image'] : 'no_image',
                    );
                }
                $res = true;
                $message = 'Details retrived successfully';
            }else{
                $res = false;
                $data = 'fail';
                $message = 'Order not Exist';
                
            }
            $responce = array(
                'success' => $res,
                'data' => $data,
                'message' => $message,
            );

        echo json_encode($responce);
    }


    /**  Order */

    function Order($para1 = '', $para2 = '', $para3 = '', $para4 = '')
    {

        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $para4, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            if ($para1 == 'list')
            {

                $this
                    ->db
                    ->select('sale_code, status, grand_total, buyer, payment_type, product_details, sale_datetime,order_type,delivery_slot,focus_id,p_image ');
                $this
                    ->db
                    ->order_by('sale_id', 'desc');
                $loop = $this
                    ->db
                    ->get_where('sale', array(
                    'vendor' => $para4,
                    'status' => $para2
                ))->result_array();
                foreach ($loop as $row)
                {
                    $loop1 = json_decode($row['product_details'], true);
                    $i = 1;
                    foreach ($loop1 as $row1)
                    {
                        if ($i == 1)
                        {
                            $image = $row1['image'];
                            $name = $row1['product_name'];
                        }
                        $i++;
                    }
                    date_default_timezone_set('Asia/Kolkata');
                    $date = date('M, d Y, h:i A', $row['sale_datetime']);
                    $data[] = array(
                        'orderId' => $row['sale_code'],
                        'status' => $row['status'],
                        'price' => $row['grand_total'],
                        'contact' => $this
                            ->db
                            ->get_where('user', array(
                            'user_id' => $row['buyer']
                        ))->row()->phone,
                        'username' => $this
                            ->db
                            ->get_where('user', array(
                            'user_id' => $row['buyer']
                        ))->row()->username,
                        'userId' => $row['buyer'],
                        'paymentType' => $row['payment_type'],
                        'details' => '1',
                        'itemImage' => $image,
                        'date' => $date,
                        'itemTotal' => count($loop1) ,
                        'orderType' => $row['order_type'],
                        'deliverSlot' => $row['delivery_slot'],
                        'shopTypeId' => $this
                            ->db
                            ->get_where('shop_focus', array(
                            'shop_focus_id' => $row['focus_id']
                        ))->row()->shop_type,
                        'pImage' => $row['p_image'] ? base_url() . 'uploads/sales_image/' . $row['p_image'] : 'no_image',
                    );
                }
                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'coupons retrieved successfully',
                );

            }
            else if ($para1 == 'invoicefull')
            {

                $loop = $this
                    ->db
                    ->get_where('sale', array(
                    'sale_code' => $para2
                ))->result_array();
                foreach ($loop as $row)
                {

                    $vendor_detail = array(
                        'username' => $this
                            ->db
                            ->get_where('user', array(
                            'user_id' => $row['buyer']
                        ))->row()->username,
                        'phone' => $this
                            ->db
                            ->get_where('user', array(
                            'user_id' => $row['buyer']
                        ))->row()->phone,
                        'addressSelect' => $this
                            ->db
                            ->get_where('user', array(
                            'user_id' => $row['buyer']
                        ))->row()->address1,

                    );

                    $data = array(
                        'orderId' => $row['sale_code'],
                        'cart' => json_decode($row['product_details'], true) ,
                        'userDetails' => json_decode($row['shipping_address'], true) ,
                        'grand_total' => $row['grand_total'],
                        'username' => $this
                            ->db
                            ->get_where('user', array(
                            'user_id' => $row['buyer']
                        ))->row()->username,
                        'userId' => $row['buyer'],
                        'paymentType' => $row['payment_type'],
                        'paymentType' => $row['payment_type'],
                        'vendorDetails' => $vendor_detail,
                        'date' => date('M, d Y, h:i A', $row['sale_datetime']) ,
                        'orderType' => $row2['order_type']
                    );
                }
                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'invoice retrieved successfully',
                );

            }
            else if ($para1 == 'summaryReport')
            {

                $data = array(
                    'totalRevenue' => $this
                        ->Api_model
                        ->sumof_sammaryReport('vendor_invoice', 'vendor_id', $para4, 'settlement_value') ,
                    'thisMonthRevenue' => $this
                        ->Api_model
                        ->count_monthreport(date('m') , 'vendor_invoice', 'vendor_id', $para4, 'sum', 'settlement_value') ,
                    'totalPaid' => $this
                        ->Api_model
                        ->sumof_sammaryReportwithc('vendor_invoice', 'vendor_id', $para4, 'status', 'paid', 'settlement_value') ,
                    'totalDue' => $this
                        ->Api_model
                        ->sumof_sammaryReportwithc('vendor_invoice', 'vendor_id', $para4, 'status', 'due', 'settlement_value') ,
                    'totalOrders' => $this
                        ->Api_model
                        ->count_2wcopt('vendor_invoice', 'vendor_id', $para4, 'method !=', 'cancelled') ,
                    'thisMonthOrders' => $this
                        ->Api_model
                        ->count_monthreport(date('m') , 'vendor_invoice', 'vendor_id', $para4, 'count', '') ,
                    'totalPaidOrders' => $this
                        ->Api_model
                        ->count_3wcopt('vendor_invoice', 'vendor_id', $para4, 'method !=', 'cancelled', 'status', 'paid') ,
                    'totalDueOrders' => $this
                        ->Api_model
                        ->count_3wcopt('vendor_invoice', 'vendor_id', $para4, 'method !=', 'cancelled', 'status', 'due') ,
                );

                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'summary report retrieved successfully',
                );

            }
            else if ($para1 == 'invoiceList')
            {
                $this
                    ->db
                    ->order_by('vendor_invoice_id', 'desc');
                $loop = $this
                    ->db
                    ->get_where('vendor_invoice', array(
                    'vendor_id' => $para4
                ))->result_array();
                $i = 0;
                foreach ($loop as $row)
                {

                    $i++;
                    $data[] = array(
                        'id' => $i,
                        'invoiceID' => '#' . $row['invoice_id'],
                        'itemAmount' => $row['order_amount'],
                        'settlementAmount' => $row['settlement_value'],
                        'commission' => $row['commission'],
                        'paymentMethod' => $row['method'],
                        'status' => $row['status'],
                        'date' => date("F j, Y", $row['order_date']) ,
                        'transactionID' => $row['transaction_id'] == '' ? 'waiting' : $row['transaction_id'],
                    );

                }

                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'payment details retrieved successfully',
                );

            }
            else if ($para1 == 'invoiceView')
            {
                $loop = $this
                    ->db
                    ->get_where('sale', array(
                    'sale_code' => $para2
                ))->result_array();
                //'payment' => json_decode($row['payment_details']),
                //'orderDate' =>  date("F j, Y",$row['sale_datetime']),
                //'userId' =>   '12',
                //		'status' =>  $row['status'],
                //	'saleCode' => $row['sale_code'],
                //	'instanceDelivery' => false,
                foreach ($loop as $row)
                {
                    $addressShop = array(
                        'addressSelect' => $this
                            ->Api_model
                            ->singleselectbox('vendor', 'vendor_id', $row['vendor'], 'address1') ,
                        'username' => $this
                            ->Api_model
                            ->singleselectbox('vendor', 'vendor_id', $row['vendor'], 'display_name') ,
                        'phone' => $this
                            ->Api_model
                            ->singleselectbox('vendor', 'vendor_id', $row['vendor'], 'phone') ,
                    );

                    $data = array(
                        'id' => $row['sale_id'],
                        'productDetails' => json_decode($row['product_details']) ,
                        'addressUser' => json_decode($row['shipping_address']) ,
                        'addressShop' => $addressShop,
                        'payment' => json_decode($row['payment_details']) ,
                        'orderDate' => date("F j, Y", $row['sale_datetime']) ,
                        'status' => $row['status'],
                        'pickup_image' => $row['pickup_image'],
                        'drop_image' => $row['drop_image'],
                        'userId' => '12',
                        'saleCode' => $row['sale_code'],
                        'instanceDelivery' => false,
                    );

                }
                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'invoice details retrieved successfully',
                );
            }
            else if ($para1 == 'statusUpdate')
            {

                $data['status'] = $para3;

                $this
                    ->db
                    ->where('sale_code', $para2);
                $this
                    ->db
                    ->update('sale', $data);

                $responce = array(
                    'success' => true,
                );
            }
            else if ($para1 == 'search')
            {

                $this
                    ->db
                    ->like('sale_code', $para3);
                $this
                    ->db
                    ->select('sale_code, status, grand_total, buyer, payment_type, product_details, sale_datetime,order_type,delivery_slot');
                $this
                    ->db
                    ->order_by('sale_id', 'desc');
                $loop = $this
                    ->db
                    ->get_where('sale', array(
                    'vendor' => $para4
                ))->result_array();
                foreach ($loop as $row)
                {
                    $loop1 = json_decode($row['product_details'], true);
                    $i = 1;
                    foreach ($loop1 as $row1)
                    {
                        if ($i == 1)
                        {
                            $image = $row1['image'];
                            $name = $row1['product_name'];
                        }
                        $i++;
                    }
                    $date = date('M, d Y, h:i A', $row['sale_datetime']);
                    $data[] = array(
                        'orderId' => $row['sale_code'],
                        'status' => $row['status'],
                        'price' => $row['grand_total'],
                        'contact' => $this
                            ->db
                            ->get_where('user', array(
                            'user_id' => $row['buyer']
                        ))->row()->phone,
                        'username' => $this
                            ->db
                            ->get_where('user', array(
                            'user_id' => $row['buyer']
                        ))->row()->username,
                        'userId' => $row['buyer'],
                        'paymentType' => $row['payment_type'],
                        'details' => '1',
                        'itemImage' => $image,
                        'date' => $date,
                        'itemTotal' => count($loop1) ,
                        'orderType' => $row['order_type'],
                        'deliverSlot' => $row['delivery_slot']
                    );
                }
                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'coupons retrieved successfully',
                );
            }
            else if ($para1 == 'amountUpdate')
            {

                $data['grand_total'] = $para2;
                $this
                    ->db
                    ->where('sale_code', $para3);
                $this
                    ->db
                    ->update('sale', $data);
                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'coupons retrieved successfully',
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

    /** dashboard **/
    function dashboard($para1 = '', $para2 = '', $para3 = '')
    {
        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $para2, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            if ($para1 == 'topbar')
            {

                $neworders = $this
                    ->Api_model
                    ->count_2wcopt('sale', 'vendor', $para2, 'status', 'Placed');
                $processing = $this
                    ->Api_model
                    ->count_2wcopt('sale', 'vendor', $para2, 'status', 'Accepted');
                $outForDelivery = $this
                    ->Api_model
                    ->count_2wcopt('sale', 'vendor', $para2, 'status', 'Shipped');
                $completed = $this
                    ->Api_model
                    ->count_2wcopt('sale', 'vendor', $para2, 'status', 'Completed');

                $totalorders = $neworders + $processing + $outForDelivery + $completed;

                $newOrdersPercent = intval(($neworders * 100) / $totalorders);
                $processingPercent = intval(($processing * 100) / $totalorders);
                $outForDeliveryPercent = intval(($outForDelivery * 100) / $totalorders);
                $completePercent = intval(($completed * 100) / $totalorders);

                $codearn = $this
                    ->Api_model
                    ->sumof_sammaryReportwithc('vendor_invoice', 'vendor_id', $para2, 'method', 'cash on delivery', 'settlement_value');
                $onlineearn = $this
                    ->Api_model
                    ->sumof_sammaryReportwithc('vendor_invoice', 'vendor_id', $para2, 'method', 'online', 'settlement_value');

                $type = $this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para2, 'type');

                if ($type == 2)
                {
                    $totalproducts = $this
                        ->Api_model
                        ->count_wcopt('restaurantproduct', 'added_by', json_encode(array(
                        'type' => 'vendor',
                        'id' => $para2
                    )));
                }
                else
                {
                    $totalproducts = $this
                        ->Api_model
                        ->count_wcopt('product', 'added_by', json_encode(array(
                        'type' => 'vendor',
                        'id' => $para2
                    )));
                }

                $data = array(
                    'newOrders' => $neworders,
                    'processing' => $processing,
                    'outForDelivery' => $outForDelivery,
                    'completed' => $completed,
                    'newOrdersPercent' => $newOrdersPercent,
                    'processingPercent' => $processingPercent,
                    'outForDeliveryPercent' => $outForDeliveryPercent,
                    'completePercent' => $completePercent,
                    'totalEarnCod' => $codearn,
                    'totalEarnOnline' => $onlineearn,
                    'totalEarn' => $codearn + $onlineearn,
                    'totalProducts' => $totalproducts,
                    'totalCategory' => $this
                        ->Api_model
                        ->count_wcopt('category', 'data_vendors', $para2) ,
                    'totalSales' => $this
                        ->Api_model
                        ->count_wcopt('sale', 'vendor', $para2) ,
                    'thisMonthSales' => $this
                        ->Api_model
                        ->count_monthreport(date('m') , 'vendor_invoice', 'vendor_id', $para2, 'sum', 'settlement_value')
                );
                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'dashboard retrieved successfully',
                );
            }
            else if ($para1 == 'bargraph')
            {

                for ($i = 1;$i <= 12;$i++)
                {
                    $data[] = array(
                        'month' => $i,
                        'instance' => $this
                            ->Api_model
                            ->count_monthreport($i, 'sale', 'vendor', $para2, 'instance', '') ,
                        'schedule' => $this
                            ->Api_model
                            ->count_monthreport($i, 'sale', 'vendor', $para2, 'schedule', '') ,
                    );

                }

                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'dashboard retrieved successfully',
                );
            }
            else if ($para1 == 'liveordertrack')
            {
                $this
                    ->db
                    ->select('order_amount, buyer_id, invoice_id');
                $this
                    ->db
                    ->order_by('vendor_invoice_id', 'desc');
                $this
                    ->db
                    ->limit(1);
                $loop = $this
                    ->db
                    ->get_where('vendor_invoice', array(
                    'vendor_id' => $para2
                ))->result_array();
                if (count($loop) > 0)
                {
                    foreach ($loop as $row)
                    {
                        $data = array(
                            'count' => $this
                                ->Api_model
                                ->count_2wcopt('sale', 'vendor', $para2, 'status', 'Placed') ,
                            'orderID' => $row['invoice_id'],
                            'name' => $this
                                ->Api_model
                                ->singleselectbox('user', 'user_id', $row['buyer_id'], 'username') ,
                            'amount' => $row['order_amount'],
                        );
                    }
                }
                else
                {
                    $data = array(
                        'count' => 0,
                        'orderID' => '',
                        'name' => '',
                        'amount' => '',
                    );
                }

                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'order count successfully',
                );
            }
            else if ($para1 == 'initialOrderCount')
            {

                $responce = array(
                    'success' => true,
                    'data' => $this
                        ->Api_model
                        ->count_2wcopt('sale', 'vendor', $para2, 'status', 'Placed') ,
                    'message' => 'order count successfully',
                );
            }
            else if ($para1 == 'live_status')
            {

                $data['livestatus'] = $para3;
                $this
                    ->db
                    ->where('vendor_id', $para2);
                $this
                    ->db
                    ->update('vendor', $data);
                $responce = array(
                    'success' => true,
                    'data' => 'success',
                    'message' => 'user update successfully',
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

    


   

    function setdefault($para1 = '', $para2 = '')
    {

        $data['isDefault'] = false;
        $this
            ->db
            ->where('userid', $para1);
        $this
            ->db
            ->update('address', $data);

        $data['isDefault'] = 'true';
        $this
            ->db
            ->where('address_id', $para2);
        $this
            ->db
            ->update('address', $data);
        $data = ["success" => true, "data" => [], "message" => "setdefault address successfully"];

        echo json_encode($data);
    }
   /* shoptheoffer */
    function shoptheoffer($para1 = '', $para2)
    {
        $loop = $this
            ->db
            ->get_where('shop_the_offer')
            ->result_array();
        foreach ($loop as $row2)
        {
            $data[] = array(
                'offer' => $row2['offer'],
                'image' => base_url() . 'uploads/shop_the_offer_image/' . $row2['image'],
            );
        }

        $responce = array(
            'success' => true,
            'data' => $data,
            'message' => 'shop offer  successfully',
        );
        echo json_encode($responce);
    }

    
    
    function profileUpdateSteps($para1 = '', $para2 = '', $para3 = ''){
      
        if ($para1 == 'basic'){
             $general_info = json_decode($this->input->post('title'));
       
             $data['company'] = $general_info->companyLegalName;
             $data['display_name'] = $general_info->storeName;
             $data['name'] = $general_info->storeName;
             $data['subtitle'] = $general_info->subtitle;
             $data['general_detail'] = $this->input->post('title');
             $data['latitude']   =    $general_info->latitude;
             $data['longitude']   = $general_info->longitude;
             $data['address1']   = $general_info->pickupAddress;
             $data['phone'] = $general_info->mobile;
            
            
             $data['livestatus'] = 'false';
             $data['profile_complete'] = '2';

            
            $path = $_FILES['image']['name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $data['logo'] = 'vendor_' . $para3 . '.' . 'png';
            
            $this->db->where('vendor_id', $para3);
            $this->db->update('vendor', $data);
            $this ->crud_model ->file_up("image", "vendor", $para3, '', 'no', '.png');
        } else if($para1 =='kyc'){
             $general_info = json_decode($this->input->post('title'));
             $data['kyc_seller'] = $this->input->post('title'); 
             $data['bank_details'] = $this->input->post('bank');
             $data['profile_complete'] = '3';
             $this->db->where('vendor_id', $para3);
             $this->db->update('vendor', $data);
         
        } else if($para1 =='setting'){

             $data['profile_complete'] = '4';
             $this->db->where('vendor_id', $para3);
             $this->db->update('vendor', $data);
        }
    }
    
    function updatePriceplan($para1='', $para2){
        
    }
    
    
    
    function getGlobalObject($table ='', $col='', $para='', $select=''){
      $loop =  $this->db->get_where($table, array($col=>$para))->result_array();
         $selectValue = '';
      foreach($loop as $row){
          $selectValue = $row[$select];
      }
         $responce = array(
                    'success' => true,
                    'data' =>  $selectValue,
                );
       echo json_encode($responce);
    }
    

    /** profile update**/
    function profileUpdate($para1 = '', $para2 = '', $para3 = '')
    {
        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $para2, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            $content_data = file_get_contents("php://input");
            $get_data = json_decode($content_data);
            if ($para1 == 'general')
            {
                $vendor_details = $this->db->get_where('vendor',array('vendor_id'=>$para2))->row_array();
                if($vendor_details['profile_complete'] == 5)
                {
                    $profile_comp = 5;
                }else{
                    $profile_comp = true;
                }
                //$address = $this->Api_model->get_lat_long($get_data->address);
                //$latlong = explode("_",$address );
                $data['company'] = $get_data->shopName;
                $data['display_name'] = $get_data->shopName;
                if ($get_data->address != '')
                {
                    $data['address1'] = $get_data->address;
                }

              //  $data['phone'] = $get_data->phone;
                $data['phone'] = $get_data->mobile;
                $data['description'] = $get_data->description;
                $data['latitude'] = $get_data->latitude;
                $data['longitude'] = $get_data->longitude;
                $data['general_detail'] = json_encode($get_data);
                $data['name'] = $get_data->ownerName;
                $data['display_name'] = $get_data->storeName;
                $data['company'] = $get_data->companyLegalName;
                $data['address1'] = $get_data->pickupAddress;
                $data['subtitle'] = $get_data->subTitle;
                $data['livestatus'] = 'true';
                $data['profile_complete'] = $profile_comp;
                $data['driver_assign'] = $get_data->driverAssign;
                $data['auto_assign'] = $get_data->autoAssign;
                
                
                $this->db->where('vendor_id', $para2);
                $this->db->update('vendor', $data);
                $responce = array(
                    'success' => true,
                    'data' => 'success'
                );

            }
            else if ($para1 == 'bank_detail')
            {

                $data['bank_details'] = json_encode($get_data);
                $this
                    ->db
                    ->where('vendor_id', $para2);
                $this
                    ->db
                    ->update('vendor', $data);
                $responce = array(
                    'success' => true,
                );
            }
            else if ($para1 == 'profilelist')
            {
                $this
                    ->db
                    ->select('general_detail, bank_details, rating');
                $loop = $this
                    ->db
                    ->get_where('vendor', array(
                    'vendor_id' => $para2
                ))->result_array();
                foreach ($loop as $row)
                {
                    $data = array(
                        'general' => json_decode($row['general_detail'], true) ,
                        'bankDetails' => json_decode($row['bank_details'], true) ,
                        'rating' => floatval($row['rating']) ,
                        'totalOrders' => $this
                            ->Api_model
                            ->count_2wcopt('vendor_invoice', 'vendor_id', $para2, 'method !=', 'cancelled') ,
                        'totalProducts' => $this
                            ->Api_model
                            ->count_wcopt('product', 'added_by', json_encode(array(
                            'type' => 'vendor',
                            'id' => $para2
                        ))) ,
                    );
                    $responce = array(
                        'success' => true,
                        'data' => $data,
                        'message' => 'profile retrieved successfully',
                    );
                }

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

    /* register */
   
    function register()
    {

        $content_data = file_get_contents("php://input");
        $get_data = json_decode($content_data);
        $loop = $this
            ->db
            ->get_where('vendor', array(
            'email' => $get_data->emailId
        ))
            ->result_array();
        if (count($loop) == 0)
        {
            $general_detail = array(
                'email' => $get_data->emailId,
                'name' => '',
                'shopName' => '',
                'subTitle' => '',
                'alternativeMobile' => '',
                'openTime' => '',
                'closeTime' => '',
                'description' => '',
                'startedYear' => '',
                'address' => '',
                'latitude' => 0.1,
                'longitude' => 0.1,
                'autoAssign' => true,
                'driverAssign' => true,
            );
            $bank_detail = array(
                'bankName' => '',
                'accountNo' => '',
                'swiftCode' => '',
            );

            $data['display_name'] = $get_data->name;
            $data['email'] = $get_data->emailId;
            $data['phone'] = $get_data->phone;
            $data['type'] = $this ->Api_model ->singleselectbox('shop_focus', 'shop_focus_id', $get_data->shopTypeId, 'shop_type');
            $data['focus_id'] = $get_data->shopTypeId;
            $data['driver_assign'] = true;
            $data['auto_assign'] = true;
            $data['password'] = sha1($get_data->password);
            $data['general_detail'] = json_encode($general_detail);
            $data['bank_details'] = json_encode($bank_detail);
            $data['create_timestamp'] = time();
            $data['status'] = 'waiting';
            $data['profile_complete'] = 1;
            $data['rating'] = 0; 
            
            $this->db->insert('vendor', $data);
            
            $id = $this
                ->db
                ->insert_id();
           // $data = 'success';
           $res = true;
           $message = "register retrieved successfully";
        }
        else
        {
            $res = false;
            $data = 'fail';
            $message = "Invalid Input";
        }

        $data1['fromTime'] = '9:00 AM';
        $data1['toTime'] = '9:00 PM';
        $data1['time_id'] = 21;
        $data1['status'] = 'success';
        $data1['date'] = time();
        $data1['vendor'] = $id;
        $this
            ->db
            ->insert('deliverytimeslot', $data1); 
        $responce = array(
            'success' => $res,
            'data' => $data,
            'message' => $message,
        );
        echo json_encode($responce);
    }
  
  
  /** deliveryTimeSlot **/
    function deliveryTimeSlot($para1 = '', $para2 = '', $para3 = '')
    {

        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $para3, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            $content_data = file_get_contents("php://input");
            $get_data = json_decode($content_data);
            if ($para1 == 'do_add')
            {

                $data['fromTime'] = $get_data->fromTime;
                $data['toTime'] = $get_data->toTime;
                $data['time_id'] = $get_data->timeId;
                $data['status'] = 'ok';
                $data['date'] = time();
                $data['vendor'] = $para3;
                $this
                    ->db
                    ->insert('deliverytimeslot', $data);
                $responce = array(
                    'success' => true,
                );
            }
            else if ($para1 == 'list')
            {

                $loop = $this
                    ->db
                    ->get_where('deliverytimeslot', array(
                    'vendor' => $para3
                ))->result_array();
                foreach ($loop as $row)
                {
                    $data[] = array(
                        'fromTime' => $row['fromTime'],
                        'toTime' => $row['toTime'],
                        'timeId' => intval($row['time_id']) ,
                        'id' => intval($row['deliverytimeslot_id'])
                    );
                }
                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'delivery timeslot retrieved successfully',
                );
            }
            else if ($para1 == 'delete')
            {

                $this
                    ->db
                    ->where('deliverytimeslot_id', $para2);
                $this
                    ->db
                    ->delete('deliverytimeslot');
                $responce = array(
                    'success' => true,
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

    /* login */
    function login($para1 = '', $para2 = '')
    {

        $content_data = file_get_contents("php://input");
        $get_data = json_decode($content_data);

        /*$data['device_id'] = $get_data->deviceToken;
        $this->db->where('email',$get_data->email);
        $this->db->update('user',$data);*/

        $loop = $this
            ->db
            ->get_where('vendor', array(
            'email' => $get_data->email,
            'password' => sha1($get_data->password) ,
        ))
            ->result_array();
        if (count($loop) > 0)
        {
            foreach ($loop as $row)
            {
                if ($row['logo'] != '')
                {
                    $img = base_url() . 'uploads/vendor_image/' . $row['logo'];
                }
                else
                {
                    $img = 'no_image';
                }

                if ($row['cover_image'] != '')
                {
                    $coverImage = base_url() . 'uploads/cover_image/' . $row['cover_image'];
                }
                else
                {
                    $coverImage = 'no_image';
                }

                $data1['token'] = sha1($row['vendor_id']);
                

                $data = array(
                    'id' => $row['vendor_id'],
                    'name' => $row['name'],
                    'shopName' => $row['display_name'],
                    'email' => $row['email'],
                    'api_token' => $data1['token'],
                    'device_token' => '',
                    'phone' => $row['phone'],
                    'status' => $row['status'],
                    'auth' => true,
                    'image' => $img,
                    'coverImage' => $coverImage,
                    'shopTypeId' => intval($row['type']) ,
                    'focusID' => intval($row['focus_id']) ,
                    'focusName' => $this
                        ->Api_model
                        ->singleselectbox('shop_focus', 'shop_focus_id', $row['focus_id'], 'title') ,
                    'colorCode' => $this
                        ->Api_model
                        ->singleselectbox('shop_focus', 'shop_focus_id', $row['focus_id'], 'color_code') ,
                    'registerDate' => date('d M-Y', $row['create_timestamp']) ,
                    'liveStatus' => $row['livestatus'] ? true : false,
                    'autoAssign' => $row['auto_assign'] ? true : false,
                    'driverAllAccess' => $row['driver_assign'] ? true : false,
                    'profileStatus' => $row['status']=='waiting'?false:true,
                    'profileComplete' => $row['profile_complete'], 
                    'planName' => $this->Api_model->singleselectbox('vendor_membership', 'vendor_membership_id', $row['plan'], 'plan_name'),
                    'expiredTime' => $row['member_expire_timestamp'],
                    'productLimit' =>  $this->Api_model->singleselectbox('vendor_membership', 'vendor_membership_id', $row['plan'], 'product_upload_limit'),
                );

            }
            $this
                ->db
                ->where('vendor_id', $row['vendor_id']);
            $this
                ->db
                ->update('vendor', $data1);

        }
        else
        {
            $data = array(
                'auth' => false,
            );
        }

        $responce = array(
            'success' => true,
            'data' => $data,
            'message' => 'profile retrieved successfully',
        );
        echo json_encode($responce);

    }
    
    
    /** memebership plan **/
    
    function membership_plan($para1='', $para2='', $para3){
        if($para1=='list'){
            $loop = $this->db->get_where('vendor_membership', array('focus_id'=>$para2))->result_array();
            foreach($loop as $row){
             $data[] = array(
                        'id' => $row['vendor_membership_id'],
                        'planname' => $row['plan_name'],
                        'price' => $row['price'],
                        'commission' => $row['commision'],
                        'productlimit' => $row['product_upload_limit'],
                        'validity' => $row['validity'],
                        'focus_id' => $row['focus_id'],
                        'uploadImage' => '',
                 
                    );
            }
            
             $responce = array(
            'success' => true,
            'data' => $data,
            'message' => 'membership retrieved successfully',
        );
        } else if($para1=='plan_update'){
        $content_data = file_get_contents("php://input");
        $get_data = json_decode($content_data);
              $data['plan'] = $get_data->planId;
              $data['payment_type'] = $get_data->paymentType;
              $data['payment_status'] =  $get_data->paymentStatus;
              $data['profile_complete'] =  5;
              $this->db->where('vendor_id',  $get_data->vendorId);
              $this->db->update('vendor', $data);
             $responce = array(
            'success' => true,
            'message' => 'membership retrieved successfully',
        );
        }
        
         echo json_encode($responce);
    }
    
    

    /** order assign */

    function orderAssign($para1 = '', $para2 = '', $para3 = '', $para4 = '')
    {
       if ($para1 == 'autoAssign' && $para2 == 1)
            {
                $shoplatitude = $this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para4, 'latitude');
                $shoplongitude = $this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para4, 'longitude');
                $result = $this
                    ->Api_model
                    ->nearestDriver($shoplatitude, $shoplongitude);
                foreach ($result as $row)
                {

                    $data['delivery_assigned'] = $row['driver_id'];
                    $data['deliver_assignedtime'] = time();
                    $data['status'] = 'Shipped';
                    $this
                        ->db
                        ->where('sale_code', $para3);
                    $this
                        ->db
                        ->update('sale', $data);
                    
                    $data1['driver_id'] =   $row['driver_id'];
                    $data1['deliver_assignedtime'] = $data['deliver_assignedtime'];
                    $this->db->where('invoice_id', $para3);
                    $this->db->update('vendor_invoice', $data1);
                    
                    
                    
                    $data1 = array(
                        'driverId' => $row['driver_id'],
                        'driverName' => $row['name'],
                        'phone' => $row['phone'],
                        'latitude' => $row['latitude'],
                        'longitude' => $row['longitude']
                    );
                }

            }
            else if ($para1 == 'autoAssign' && $para2 == 0)
            {

                $shoplatitude = $this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para4, 'latitude');
                $shoplongitude = $this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para4, 'longitude');
                $result = $this
                    ->Api_model
                    ->nearestDriverwithVendor($shoplatitude, $shoplongitude, $para4);
                foreach ($result as $row)
                {

                    $data['delivery_assigned'] = $row['driver_id'];
                    $data['deliver_assignedtime'] = time();
                    $data['status'] = 'Shipped';
                    $this
                        ->db
                        ->where('sale_code', $para3);
                    $this
                        ->db
                        ->update('sale', $data);
                    
                      
                    $data1['driver_id'] =   $row['driver_id'];
                    $data1['deliver_assignedtime'] = $data['deliver_assignedtime'];
                    $this->db->where('invoice_id', $para3);
                    $this->db->update('vendor_invoice', $data1);
                    
                    
                    $data1 = array(
                        'driverId' => $row['driver_id'],
                        'driverName' => $row['name'],
                        'phone' => $row['phone'],
                        'latitude' => $row['latitude'],
                        'longitude' => $row['longitude']
                    );
                }

            }
            else if ($para1 == 'manual' && $para2 == 1)
            {

                $shoplatitude = $this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para4, 'latitude');
                $shoplongitude = $this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para4, 'longitude');
                $result = $this
                    ->Api_model
                    ->nearestDriverAll($shoplatitude, $shoplongitude);
                foreach ($result as $row)
                {
                    $data1[] = array(
                        'id' => $row['driver_id'],
                        'name' => $row['name'],
                    );
                }

            }
            else if ($para1 == 'manual' && $para2 == 0)
            {

                $shoplatitude = $this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para4, 'latitude');
                $shoplongitude = $this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para4, 'longitude');

                $result = $this
                    ->Api_model
                    ->nearestDriverwithVendor($shoplatitude, $shoplongitude, $para4);

                foreach ($result as $row)
                {
                    $data1[] = array(
                        'id' => $row['driver_id'],
                        'name' => $row['name'],
                    );
                }

            }
            else if ($para1 == 'update')
            {

                $data['delivery_assigned'] = $para2;
                $data['deliver_assignedtime'] = time();
                $data['status'] = 'Shipped';
                $this
                    ->db
                    ->where('sale_code', $para3);
                $this
                    ->db
                    ->update('sale', $data);
                
                  
                    $data1['driver_id'] =   $row['driver_id'];
                    $data1['deliver_assignedtime'] = $data['deliver_assignedtime'];
                    $this->db->where('invoice_id', $para3);
                    $this->db->update('vendor_invoice', $data1);
                
                
                $data1 = array(
                    'driverId' => $para2,
                    'driverName' => $this
                        ->Api_model
                        ->singleselectbox('driver', 'driver_id', $para2, 'name') ,
                    'phone' => $this
                        ->Api_model
                        ->singleselectbox('driver', 'driver_id', $para2, 'phone') ,
                    'latitude' => $this
                        ->Api_model
                        ->singleselectbox('driver', 'driver_id', $para2, 'latitude') ,
                    'longitude' => $this
                        ->Api_model
                        ->singleselectbox('driver', 'driver_id', $para2, 'longitude') ,
                );

            }

            $responce = array(
                'success' => true,
                'data' => $data1,
                'message' => 'profile retrieved successfully',
            );
        echo json_encode($responce);
    }
    
    /** Order Cancelled By Driver Logic **/
    
    /** */
    /** Duplicate Order Assign */
    
     function orderAssignTwo($para1 = '', $para2 = '', $para3 = '', $para4 = '')
    {
            if ($para1 == 'autoAssign' && $para2 == 1)
            {
                $shoplatitude = $this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para4, 'latitude');
                $shoplongitude = $this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para4, 'longitude');
                $result = $this
                    ->Api_model
                    ->nearestDriver($shoplatitude, $shoplongitude);
                foreach ($result as $row)
                {

                    $data['delivery_assigned'] = $row['driver_id'];
                    $data['deliver_assignedtime'] = time();
                    $data['status'] = 'Shipped';
                    $this
                        ->db
                        ->where('sale_code', $para3);
                    $this
                        ->db
                        ->update('sale', $data);
                    
                    $data1['driver_id'] =   $row['driver_id'];
                    $data1['deliver_assignedtime'] = $data['deliver_assignedtime'];
                    $this->db->where('invoice_id', $para3);
                    $this->db->update('vendor_invoice', $data1);
                    
                    
                    
                    $data1 = array(
                        'driverId' => $row['driver_id'],
                        'driverName' => $row['name'],
                        'phone' => $row['phone'],
                        'latitude' => $row['latitude'],
                        'longitude' => $row['longitude']
                    );
                }

            }
            else if ($para1 == 'autoAssign' && $para2 == 0)
            {

                $shoplatitude = $this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para4, 'latitude');
                $shoplongitude = $this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para4, 'longitude');
                $result = $this
                    ->Api_model
                    ->nearestDriverwithVendor($shoplatitude, $shoplongitude, $para4);
                foreach ($result as $row)
                {

                    $data['delivery_assigned'] = $row['driver_id'];
                    $data['deliver_assignedtime'] = time();
                    $data['status'] = 'Shipped';
                    $this
                        ->db
                        ->where('sale_code', $para3);
                    $this
                        ->db
                        ->update('sale', $data);
                    
                      
                    $data1['driver_id'] =   $row['driver_id'];
                    $data1['deliver_assignedtime'] = $data['deliver_assignedtime'];
                    $this->db->where('invoice_id', $para3);
                    $this->db->update('vendor_invoice', $data1);
                    
                    
                    $data1 = array(
                        'driverId' => $row['driver_id'],
                        'driverName' => $row['name'],
                        'phone' => $row['phone'],
                        'latitude' => $row['latitude'],
                        'longitude' => $row['longitude']
                    );
                }

            }
            else if ($para1 == 'manual' && $para2 == 1)
            {

                $shoplatitude = $this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para4, 'latitude');
                $shoplongitude = $this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para4, 'longitude');
                $result = $this
                    ->Api_model
                    ->nearestDriver($shoplatitude, $shoplongitude);
                foreach ($result as $row)
                {
                    $data1[] = array(
                        'id' => $row['driver_id'],
                        'name' => $row['name'],
                    );
                }

            }
            else if ($para1 == 'manual' && $para2 == 0)
            {

                $shoplatitude = $this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para4, 'latitude');
                $shoplongitude = $this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para4, 'longitude');

                $result = $this
                    ->Api_model
                    ->nearestDriverwithVendor($shoplatitude, $shoplongitude, $para4);

                foreach ($result as $row)
                {
                    $data1[] = array(
                        'id' => $row['driver_id'],
                        'name' => $row['name'],
                    );
                }

            }
            else if ($para1 == 'update')
            {

                $data['delivery_assigned'] = $para2;
                $data['deliver_assignedtime'] = time();
                $data['status'] = 'Shipped';
                $this
                    ->db
                    ->where('sale_code', $para3);
                $this
                    ->db
                    ->update('sale', $data);
                
                  
                    $data1['driver_id'] =   $row['driver_id'];
                    $data1['deliver_assignedtime'] = $data['deliver_assignedtime'];
                    $this->db->where('invoice_id', $para3);
                    $this->db->update('vendor_invoice', $data1);
                
                
                $data1 = array(
                    'driverId' => $para2,
                    'driverName' => $this
                        ->Api_model
                        ->singleselectbox('driver', 'driver_id', $para2, 'name') ,
                    'phone' => $this
                        ->Api_model
                        ->singleselectbox('driver', 'driver_id', $para2, 'phone') ,
                    'latitude' => $this
                        ->Api_model
                        ->singleselectbox('driver', 'driver_id', $para2, 'latitude') ,
                    'longitude' => $this
                        ->Api_model
                        ->singleselectbox('driver', 'driver_id', $para2, 'longitude') ,
                );

            }

            $responce = array(
                'success' => true,
                'data' => $data1,
                'message' => 'profile retrieved successfully',
            );

        
      //  echo json_encode($responce);
    }
    
    
    
    
    /** */
    
    
    
    
    function CancelOrderByDriver()
    {
        $driver_id = $this->input->post('driver_id');
        $sale_code = $this->input->post('sale_code');
        if($driver_id != null && $sale_code != null)
        {
            $driver_array = array();
            
            $sale = $this->db->get_where('sale',array('sale_code'=>$sale_code))->row_array();
            
            if($sale['cancelled_by'] != null && $sale['cancelled_by'] != '' && $sale['cancelled_by'] != '[]')
            {
                $driver_array = json_decode($sale['cancelled_by']);
            }
            
            array_push($driver_array, $driver_id);
            
            $this->db->where('sale_code',$sale_code);
            $this->db->update('sale', array('cancelled_by'=>json_encode($driver_array),'delivery_state'=>'Rejected')); 
            
            $this->db->insert('order_cancelled_by_driver',array('sale_code'=>$sale_code,'driver_id'=>$driver_id));
            
            $this->orderAssignTwo('autoAssign', 1, $sale_code, $sale['vendor']);

            $res = true;
            $msg = 'Order Cancelled Successfully.';
        }else{
            $res = false;
            $msg = 'Data is Incomplete.';
        }
        
        $responce = array(
                'success' => true,
                'message' => $msg,
            );
        echo json_encode($responce);
    }
    
    
    /*** End Order Cancelled By Driver Logic **/
    /** driver details */
    
    function driverDetails($para1='', $para2='', $para3=''){
     
        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $para3, 'token') == $this
            ->input
            ->get('api_token', true))
        {
          if ($para1 == 'do_add')
            {
              
               $this->db->select('deliver_assignedtime,status,delivery_assigned');
               $loop = $this->db->get_where('sale',array('sale_code'=>$para2))->result_array();
            foreach ($loop as $row) {
               
              $data = array(
                            'driverName' =>$this->Api_model ->singleselectbox('driver', 'driver_id', $row['delivery_assigned'], 'name'),
                            'phone' => $this->Api_model ->singleselectbox('driver', 'driver_id', $row['delivery_assigned'], 'phone'),
                            'status' => $row['status'],
                            'orderDate' => date('d  M,Y h:i A', $row['deliver_assignedtime']),
                            'id' => $row['delivery_assigned'],
                        );
            }
              
              
              
               $responce = array(
                'success' => true,
                'data' => $data,
                'message' => 'driver details retrieved successfully',
            ); 
              
          }
        }  else
        {
            $responce = $this
                ->Api_model
                ->tokenfailed();

        }
        echo json_encode($responce);
    }
    

    
    
   /* banner */
    function banner($para1 = '', $para2 = '', $para3 = '')
    {
        if ($this
            ->Api_model ->singleselectbox('vendor', 'vendor_id', $para3, 'token') == $this ->input ->get('api_token', true))
        {
            if ($para1 == 'add')
            {

                $data['title'] = $this ->input ->post('title');
                $data['para'] = $this ->input->post('para');
                $data['type'] = $this->input  ->post('type');
                $data['vendor'] = $para3;
                $data['date'] = time();
                $this
                    ->db
                    ->insert('banner', $data);
                $id = $this
                    ->db
                    ->insert_id();
                $path = $_FILES['image']['name'];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $databanner['image'] = 'banner_' . $id . '.' . $ext;
                $this
                    ->crud_model
                    ->file_up("image", "banner", $id, '', 'no', '.' . $ext);
                $this
                    ->db
                    ->where('banner_id', $id);
                $this
                    ->db
                    ->update('banner', $databanner);
                $responce = array(
                    'success' => true,
                );

            }
            else if ($para1 == 'list')
            {
                $loop = $this
                    ->db
                    ->get_where('banner', array(
                    'type' => $para2,
                    'vendor' => $para3
                ))->result_array();
                if (count($loop) > 0)
                {
                    foreach ($loop as $row)
                    {
                        $data[] = array(
                            'type' => $row['type'],
                            'uploadImage' => base_url() . 'uploads/banner_image/' . $row['image'],
                            'title' => $row['title'],
                            'para' => $row['para'],
                            'id' => $row['banner_id'],
                        );

                    }
                    $responce = array(
                        'success' => true,
                        'data' => $data,
                    );
                }

            }
            else if ($para1 == 'delete')
            {

                $this
                    ->db
                    ->where('banner_id', $para2);
                $this
                    ->db
                    ->delete('banner');
                $responce = array(
                    'success' => true,
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

    /** global dropdown */

    function globaldropdown($para1 = '', $para2 = '')
    {
        $this
            ->db
            ->order_by($para1 . '_id', 'asc');
        $loop = $this
            ->db
            ->get_where($para1)->result_array();
        if (count($loop) > 0)
        {
            foreach ($loop as $row)
            {
                $data[] = array(
                    'id' => $row[$para1 . '_id'],
                    'name' => $row[$para2],
                );

            }
            $responce = array(
                'success' => true,
                'data' => $data,
            );
        }
        echo json_encode($responce);
    }

    /** global dropdown with single condition*/

    function globaldropdownsc($para1 = '', $para2 = '', $para3 = '', $para4 = '', $para5 = '')
    {
        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $para5, 'token') == $this
            ->input
            ->get('api_token', true))
        {

            $this
                ->db
                ->order_by($para1 . '_id', 'asc');

            $loop = $this
                ->db
                ->get_where($para1, array(
                $para3 => $para4
            ))->result_array();
            if (count($loop) > 0)
            {
                foreach ($loop as $row)
                {
                    $data[] = array(
                        'id' => $row[$para1 . '_id'],
                        'name' => $row[$para2],
                    );

                }
                $responce = array(
                    'success' => true,
                    'data' => $data,
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

    /** subcategory */

    function subcategory($para1 = '', $para2 = '', $para3 = '')
    {
        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $para3, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            if ($para1 == 'add')
            {

                $data['sub_category_name'] = $this
                    ->input
                    ->post('sub_category_name');
                $data['category'] = $this
                    ->input
                    ->post('category');
                $data['data_vendor'] = $this
                    ->input
                    ->post('vendor');
                $this
                    ->db
                    ->insert('sub_category', $data);
                $id = $this
                    ->db
                    ->insert_id();

                $path = $_FILES['image']['name'];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $data_banner['banner'] = 'sub_category_' . $id . '.' . $ext;
                $this
                    ->crud_model
                    ->file_up("image", "sub_category", $id, '', 'no', '.' . $ext);
                $this
                    ->db
                    ->where('sub_category_id', $id);
                $this
                    ->db
                    ->update('sub_category', $data_banner);
                $responce = array(
                    'success' => true,
                );

            }
            else if ($para1 == 'list')
            {
                $loop = $this
                    ->db
                    ->get_where('sub_category', array(
                    'data_vendor' => $para3
                ))->result_array();
                if (count($loop) > 0)
                {
                    foreach ($loop as $row)
                    {
                        $data[] = array(
                            'categoryId' => $row['category'],
                            'subcategoryName' => $row['sub_category_name'],
                            'image' => base_url() . 'uploads/sub_category_image/' . $row['banner'],
                            'userId' => '1',
                            'id' => $row['sub_category_id'],
                        );

                    }
                    $responce = array(
                        'success' => true,
                        'data' => $data,
                    );
                }

            }
            else if ($para1 == 'delete')
            {

                unlink("uploads/sub_category_image/" . $this
                    ->crud_model
                    ->get_type_name_by_id('sub_category', $para2, 'banner'));
                $this
                    ->db
                    ->where('sub_category_id', $para2);
                $this
                    ->db
                    ->delete('sub_category');
                $responce = array(
                    'success' => true,
                );
            }
            else if ($para1 == 'edit')
            {

                $data['sub_category_name'] = $this
                    ->input
                    ->post('sub_category_name');
                $data['category'] = $this
                    ->input
                    ->post('category');
                $this
                    ->db
                    ->where('sub_category_id', $para2);
                $this
                    ->db
                    ->update('sub_category', $data);
                $id = $para2;
                $path = $_FILES['image']['name'];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                if ($ext != NULL)
                {
                    $databanner['banner'] = 'sub_category_' . $id . '.' . $ext;
                    $this
                        ->crud_model
                        ->file_up("image", "sub_category", $id, '', 'no', '.' . $ext);
                    $this
                        ->db
                        ->where('sub_category_id', $id);
                    $this
                        ->db
                        ->update('sub_category', $databanner);
                }
                $responce = array(
                    'success' => true,
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

    /** category **/

    function category($para1 = '', $para2 = '', $para3 = '')
    {

        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $para3, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            if ($para1 == 'add')
            {

                $data['category_name'] = $this
                    ->input
                    ->post('categoryName');
                $data['data_vendors'] = $this
                    ->input
                    ->post('vendor');
               $this
                    ->db
                    ->insert('category', $data);
                        
               $id = $this
                        ->db
                        ->insert_id();
                        $path = $_FILES['image']['name'];
                        $ext = pathinfo($path, PATHINFO_EXTENSION);
                        $databanner['banner'] = 'category_' . $id . '.png';
                        $this
                            ->crud_model
                            ->file_up("image", "category", $id, '', 'no', '.png');
                        $this
                            ->db
                            ->where('category_id', $id);
                        if($this
                            ->db
                            ->update('category', $databanner)){
                                $responce = array(
                                    'success' => true,
                                );
                            }else{
                                $responce = array(
                                    'success' => false,
                                );
                            }
               

            }
            else if ($para1 == 'list')
            {
                $this
                    ->db
                    ->order_by('category_id', 'desc');
                $loop = $this
                    ->db
                    ->get_where('category', array(
                    'data_vendors' => $para3
                ))->result_array();
                if (count($loop) > 0)
                {
                    foreach ($loop as $row)
                    {
                        $data[] = array(
                            'categoryName' => $row['category_name'],
                            'image' => base_url() . 'uploads/category_image/' . $row['banner'],
                            'userId' => '1',
                            'id' => $row['category_id'],
                        );

                    }
                    $responce = array(
                        'success' => true,
                        'data' => $data,
                    );
                }

            }
            else if ($para1 == 'delete')
            {
                unlink("uploads/category_image/" . $this
                    ->crud_model
                    ->get_type_name_by_id('category', $para3, 'banner'));
                $this
                    ->db
                    ->where('category_id', $para2);
                $this
                    ->db
                    ->delete('category');
                $responce = array(
                    'success' => true,
                );
            }
            else if ($para1 == 'edit')
            {

                $data['category_name'] = $this
                    ->input
                    ->post('categoryName');
                $data['data_vendors'] = $this
                    ->input
                    ->post('vendor');
                $this
                    ->db
                    ->where('category_id', $para2);
                $this
                    ->db
                    ->update('category', $data);
                $id = $para2;
                $path = $_FILES['image']['name'];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                if ($ext != NULL)
                {
                    $databanner['banner'] = 'category_' . $id . '.png';
                    $this
                        ->crud_model
                        ->file_up("image", "category", $id, '', 'no', '.png');
                    $this
                        ->db
                        ->where('category_id', $id);
                    $this
                        ->db
                        ->update('category', $databanner);
                }
                $responce = array(
                    'success' => true,
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
    
    
  
    

    function csvimport($para1 = '', $para2 = '', $para3 = '', $para4 = '')
    {
        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $para2, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            $this
                ->load
                ->model("Ieport_model");

            $filename = $_FILES["file"]["tmp_name"];
            if ($_FILES["file"]["size"] > 0)
            {

                $file = fopen($filename, "r");
                while (($importdata = fgetcsv($file, 10000, ",")) !== false)
                {

                    if ($para1 == 'product_add')
                    {
                        
                        if($para3 == 2)
                        {
                            $data1 = array(
                                'title' => $importdata[3],
                                'category' => $importdata[1],
                                'rating_total' => 0,
                                'rating_num' => 0,
                                'add_timestamp' => time() ,
                                'status' => 'false',
                                'added_by' => json_encode(array(
                                    'type' => 'vendor',
                                    'id' => $para2
                                )),
                                'product_type' => $para3,
                                );
                                print_r($data1);
                                $this
                                    ->Ieport_model
                                    ->restaurantproductaddcsv($data1);
                            
                        }else{

                        if ($this
                            ->Api_model
                            ->count_2wcopt('category', 'data_vendors', $para2, 'category_id', $importdata[1]) == 1 && $this
                            ->Api_model
                            ->count_2wcopt('sub_category', 'data_vendor', $para2, 'sub_category_id', $importdata[2]) == 1)
                        {
                                 $data = array(
                                'title' => $importdata[3],
                                'category' => $importdata[1],
                                'sub_category' => $importdata[2],
                                'rating_total' => 0,
                                'rating_num' => 0,
                                'add_timestamp' => time() ,
                                'status' => 'false',
                                'added_by' => json_encode(array(
                                    'type' => 'vendor',
                                    'id' => $para2
                                )) ,
                                'product_type' => $para3,
                            );
                            
                                 print_r($data);
    
                                $this
                                    ->Ieport_model
                                    ->productaddcsv($data);

                        }
                    }

                    }
                    else if ($para1 == 'product_update')
                    {
                        
                    
                    if($para3 == 2)
                        {
                            $data1 = array(
                                'restaurantproduct_id' => $importdata[0],
                                'title' => $importdata[3],
                                'category' => $importdata[1],
                                'rating_total' => 0,
                                'rating_num' => 0,
                                'add_timestamp' => time() ,
                                'status' => 'true',
                                'added_by' => json_encode(array(
                                    'type' => 'vendor',
                                    'id' => $para2
                                )),
                                'product_type' => $para3,
                                );
                                print_r($data1);
                                $this
                                    ->Ieport_model
                                    ->restaurantproductupdatecsv($data1,$importdata[0]);
                            
                        }else{

                        if ($this
                            ->Api_model
                            ->count_2wcopt('category', 'data_vendors', $para2, 'category_id', $importdata[1]) == 1 && $this
                            ->Api_model
                            ->count_2wcopt('sub_category', 'data_vendor', $para2, 'sub_category_id', $importdata[2]) == 1)
                        {
                            //echo 1;
                            $data = array(
                                'product_id' => $importdata[0],
                                'title' => $importdata[3],
                                'category' => $importdata[1],
                                'sub_category' => $importdata[2],
                                'rating_total' => 0,
                                'rating_num' => 0,
                                'add_timestamp' => time() ,
                                'status' => 'true',
                                'added_by' => json_encode(array(
                                    'type' => 'vendor',
                                    'id' => $para2
                                )) ,
                                'product_type' => $para3,
                            );
                            print_r($data);

                            $this
                                ->Ieport_model
                                ->updateproductcsv($data, $importdata[0]);
                        }
                        
                    }

                    }
                    else if ($para1 == 'variant_2_add')
                    {

                        if ($this
                            ->Api_model
                            ->count_2wcopt('product', 'added_by', json_encode(array(
                            'type' => 'vendor',
                            'id' => $para2
                        )) , 'product_id', $importdata[1]) == 1)
                        {

                            $data = array(
                                'product_id' => $importdata[1],
                                'quantity' => $importdata[2],
                                'unit' => $importdata[3],
                                'sale_price' => $importdata[4],
                                'strike_price' => $importdata[5],
                                'vendor_id' => $para2,
                                'type' => $para3,
                            );
                            print_r($data);

                            $this
                                ->Ieport_model
                                ->variantaddcsv($data);

                        }

                    }
                    else if ($para1 == 'variant_2_update')
                    {

                        if ($this
                            ->Api_model
                            ->count_2wcopt('product', 'added_by', json_encode(array(
                            'type' => 'vendor',
                            'id' => $para2
                        )) , 'product_id', $importdata[1]) == 1 && $this
                            ->Api_model
                            ->count_2wcopt('variant_2', 'vendor_id', $para2, 'variant_2_id', $importdata[0]) == 1)
                        {
                            echo $importdata[0];
                            $data = array(
                                'product_id' => $importdata[1],
                                'quantity' => $importdata[2],
                                'unit' => $importdata[3],
                                'sale_price' => $importdata[4],
                                'strike_price' => $importdata[5],
                                'vendor_id' => $para2,
                                'type' => $para3,
                            );

                            $this
                                ->Ieport_model
                                ->updatevariantcsv($data, $importdata[0]);
                        }

                    }
                    else if ($para1 == 'variant_add')
                    {

                        if ($this
                            ->Api_model
                            ->count_2wcopt('restaurantproduct', 'added_by', json_encode(array(
                            'type' => 'vendor',
                            'id' => $para2
                        )) , 'restaurantproduct_id', $importdata[1]) == 1)
                        {

                            $data = array(
                                'product_id' => $importdata[1],
                                'quantity' => $importdata[2],
                                'unit' => $importdata[3],
                                'sale_price' => $importdata[4],
                                'strike_price' => $importdata[5],
                                'discount' => $importdata[6],
                                'tax' => $importdata[7],
                                'vendor_id' => $para2,
                                'type' => $para3,
                            );
                            print_r($data);

                            $this
                                ->Ieport_model
                                ->variantreaddcsv($data);

                        }

                    }else if ($para1 == 'variant_update'){
                        if ($this
                            ->Api_model
                            ->count_2wcopt('restaurantproduct', 'added_by', json_encode(array(
                            'type' => 'vendor',
                            'id' => $para2
                        )) , 'restaurantproduct_id', $importdata[1]) == 1 && $this
                            ->Api_model
                            ->count_2wcopt('variant', 'vendor_id', $para2, 'variant_id', $importdata[0]) == 1)
                        {
                           // echo $importdata[0];
                            $data = array(
                                'product_id' => $importdata[1],
                                'quantity' => $importdata[2],
                                'unit' => $importdata[3],
                                'sale_price' => $importdata[4],
                                'strike_price' => $importdata[5],
                                'vendor_id' => $para2,
                                'type' => $para3,
                            );

                            $this
                                ->Ieport_model
                                ->updatesimplevariantcsv($data, $importdata[0]);
                        }
                    }

                }
                fclose($file);
            }
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

    /* product */
    function vendor_product_update($vendor_id = '', $product_id = '', $type = ''){
        
        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $vendor_id, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            
            $content_data = file_get_contents("php://input");
            $get_data = json_decode($content_data);
            
            if($type == 2)
            {
                $data1['title'] = $get_data->title;
                $data1['category'] = $get_data->category;
                $data1['description'] = $get_data->description;
                $this->db->where('restaurantproduct_id',$product_id);
                $this->db->update('restaurantproduct',$data1);
                
                $responce = array(
                    'success' => true,
                    'data' => $data1,
                    'message' => 'Item details updated successfully',
                );
            }else{
                $data['title'] = $get_data->title;
                $data['category'] = $get_data->category;
                $data['sub_category'] = $get_data->sub_category;
                $this->db->where('product_id',$product_id);
                $this->db->update('product',$data);
                
                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'product details updated successfully',
                );
            }
            
            
            
        }else{
            $responce = $this
                ->Api_model
                ->tokenfailed(); 
        }
        echo json_encode($responce);
        
    }
    function product($para1 = '', $para2 = '', $para3 = '', $para4 = '')
    {
        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $para4, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            if ($para1 == 'add')
            {

                $data['category'] = $this
                    ->input
                    ->post('category');
                $data['sub_category'] = $this
                    ->input
                    ->post('subCategory');
                $data['title'] = $this
                    ->input
                    ->post('productTitle');
                $data['status'] = 'true';
                $data['product_type'] = $para2;
                $data['rating_num'] = 0;
                $data['rating_total'] = 0.0;
                $data['rating_user'] = '[]';
                $data['add_timestamp'] = time();
                $data['variant_status'] = true;
              
                $data['added_by'] = json_encode(array(
                    'type' => 'vendor',
                    'id' => $this
                        ->input
                        ->post('vendor')
                ));
                $this
                    ->db
                    ->insert('product', $data);
                $id = $this
                    ->db
                    ->insert_id();
                $data1['product_id'] = $id;
                $data1['quantity'] = $this
                    ->input
                    ->post('quantity');
                $data1['discount'] = $this->input->post('discount');
                $data1['tax'] = $this->input->post('tax');
                $data1['name'] = '';
                $data1['unit'] = $this
                    ->input
                    ->post('unit');
                $data1['sale_price'] = $this
                    ->input
                    ->post('salePrice');
                $data1['strike_price'] = $this
                    ->input
                    ->post('strikePrice');
                $data1['type'] = $para2;
                $data1['vendor_id'] = $this
                    ->input
                    ->post('vendor');
                $this
                    ->db
                    ->insert('variant_2', $data1);

                $id1 = $this
                    ->db
                    ->insert_id();
                $path = $_FILES['image']['name'];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $data_image['image'] = 'product_' . $id1 . '.' . $ext;
                $this
                    ->db
                    ->where('variant_2_id', $id1);
                $this
                    ->db
                    ->update('variant_2', $data_image);
                $this
                    ->crud_model
                    ->file_up("image", "product", $id1, '', 'no', '.' . $ext);

                $responce = array(
                    'success' => $databanner['image'],
                );

            }
            else if ($para1 == 'list')
            {

                $this
                    ->db
                    ->select('category_id, category_name');
                $loop = $this
                    ->db
                    ->get_where('category', array(
                    'data_vendors' => $para4
                ))->result_array();

                foreach ($loop as $row)
                {
                    $this
                        ->db
                        ->select('product_id, title , product_type,status,sub_category');
                    $data1 = [];
                    $this
                        ->db
                        ->order_by('product_id', 'desc');
                    $loop1 = $this
                        ->db
                        ->get_where('product', array(
                        'category' => $row['category_id']
                    ))->result_array();

                    foreach ($loop1 as $row1)
                    {
                        $sub_cat = $this->db->get_where('sub_category',array('sub_category_id'=>$row1['sub_category']))->row_array();
                        $this
                            ->db
                            ->limit(1);
                        $variant = $this
                            ->db
                            ->get_where('variant_2', array(
                            'product_id' => $row1['product_id']
                        ))->result_array();

                        $variants = [];
                        if (count($variant) != 0)
                        {
                            foreach ($variant as $row2)
                            {
                                $i++;
                                $variants = array(
                                    'variant_id' => $row2['variant_2_id'],
                                    'product_id' => $row2['product_id'],
                                    'quantity' => $row2['quantity'],
                                    'name' => $row2['name'],
                                    'unit' => $row2['unit'],
                                    'sale_price' => $row2['sale_price'],
                                    'strike_price' => $row2['strike_price'],
                                    'type' => $row2['type'],
                                    'selected' => false,
                                    'uploadImage' => base_url() . 'uploads/product_image/' . $row2['image'],

                                );
                            }
                        }
                        else
                        {

                            $variants = array(
                                'variant_id' => '',
                                'product_id' => $row2['product_id'],
                                'quantity' => '',
                                'name' => '',
                                'unit' => '',
                                'sale_price' => '',
                                'strike_price' => '',
                                'type' => '',
                                'selected' => false,
                                'uploadImage' => 'no_image',

                            );
                        }

                        if ($row1['status'] == 'true')
                        {
                            $row1['status'] = true;
                        }
                        else
                        {
                            $row1['status'] = false;
                        }

                        $data1[] = array(

                            'id' => $row1['product_id'],
                            'sub_category_id' => $row1['sub_category'],
                            'sub_category_name' => $sub_cat['sub_category_name'],
                            'product_name' => $row1['title'],
                            'variant' => $variants,
                            'productType' => $row1['product_type'],
                            'status' => $row1['status'],
                        );

                    }
                    $data[] = array(
                        'id' => $row['category_id'],
                        'categoryName' => $row['category_name'],
                        'productdetails' => $data1,
                    );

                }

                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'product retrieved successfully',
                );

            }
            else if ($para1 == 'delete')
            {

                $this
                    ->db
                    ->where('product_id', $para2);
                $this
                    ->db
                    ->delete('product');
                $this
                    ->db
                    ->where('product_id', $para2);
                $this
                    ->db
                    ->delete('variant_2');
                $responce = array(
                    'success' => true,
                );
            }
            else if ($para1 == 'edit')
            {

                $data['category_name'] = $this
                    ->input
                    ->post('categoryName');
                $data['data_vendors'] = $this
                    ->input
                    ->post('vendor');
                $this
                    ->db
                    ->where('category_id', $para2);
                $this
                    ->db
                    ->update('category', $data);
                $id = $para2;
                $path = $_FILES['image']['name'];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                if ($ext != NULL)
                {
                    $databanner['banner'] = 'category_' . $id . '.' . $ext;
                    $this
                        ->crud_model
                        ->file_up("image", "category", $id, '', 'no', '.' . $ext);
                    $this
                        ->db
                        ->where('category_id', $id);
                    $this
                        ->db
                        ->update('category', $databanner);
                }
                $responce = array(
                    'success' => true,
                );
            }
            else if ($para1 == 'variantlist')
            {
                $variant = $this
                    ->db
                    ->get_where('variant_2', array(
                    'product_id' => $para2
                ))->result_array();
                $data = [];
                foreach ($variant as $row2)
                {

                    $data[] = array(
                        'variant_id' => $row2['variant_2_id'],
                        'product_id' => $row2['product_id'],
                        'quantity' => $row2['quantity'],
                        'name' => $row2['name'],
                        'unit' => $row2['unit'],
                        'sale_price' => $row2['sale_price'],
                        'strike_price' => $row2['strike_price'],
                        'tax' => $row2['tax'],
                        'discount' => doubleval(['discount']),
                        'type' => $row2['type'],
                        'selected' => false,
                        'uploadImage' => base_url() . 'uploads/product_image/' . $row2['image'],

                    );
                }
                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'product retrieved successfully',
                );

            }
            else if ($para1 == 'variant_add')
            {

                $data1['product_id'] = $para2;
                $data1['quantity'] = $this
                    ->input
                    ->post('quantity');
                $data1['name'] = '';
                $data1['unit'] = $this
                    ->input
                    ->post('unit');
                $data1['sale_price'] = $this
                    ->input
                    ->post('salePrice');
                $data1['strike_price'] = $this
                    ->input
                    ->post('strikePrice');
                $data1['type'] = $para3;
                $data1['vendor_id'] = $this
                    ->input
                    ->post('vendor');
                 $data1['discount'] = $this ->input->post('discount');
                 $data1['tax'] = $this ->input->post('tax');
                $this
                    ->db
                    ->insert('variant_2', $data1);
                $id1 = $this
                    ->db
                    ->insert_id();
                $path = $_FILES['image']['name'];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $data_image['image'] = 'product_' . $id1 . '.' . $ext;
                $this
                    ->db
                    ->where('variant_2_id', $id1);
                $this
                    ->db
                    ->update('variant_2', $data_image);
                $this
                    ->crud_model
                    ->file_up("image", "product", $id1, '', 'no', '.' . $ext);

                $responce = array(
                    'success' => true,
                    'data' => 'success',
                    'message' => 'variant added successfully',
                );
            }
            else if ($para1 == 'variant_edit')
            {

                $data1['quantity'] = $this
                    ->input
                    ->post('quantity');
                $data1['name'] = '';
                $data1['unit'] = $this
                    ->input
                    ->post('unit');
                $data1['sale_price'] = $this
                    ->input
                    ->post('salePrice');
                $data1['strike_price'] = $this
                    ->input
                    ->post('strikePrice');
                $data1['type'] = $para3;
                $data1['discount'] = $this ->input->post('discount');
                $data1['tax'] = $this ->input->post('tax');

                $id1 = $this
                    ->input
                    ->post('variant_id');
                    
                    $data1['image'] = "product_" . $id1 . ".png";

                $path = $_FILES['image']['name'];

                $ext = pathinfo($path, PATHINFO_EXTENSION);
                if ($ext != NULL)
                {

                    $this
                        ->crud_model
                        ->file_up("image", "product", $id1, '', 'no', '.' . $ext);
                     // ->file_up("image", "product", $id1, '', 'no', '.jpg');
                }
                $this
                    ->db
                    ->where('variant_2_id', $id1);
                $this
                    ->db
                    ->update('variant_2', $data1);

                $responce = array(
                    'success' => true,
                    'data' => 'success',
                    'message' => 'variant added successfully',
                );
            }
            else if ($para1 == 'product_status')
            {

                $data['status'] = $para3;
                $this
                    ->db
                    ->where('product_id', $para2);
                $this
                    ->db
                    ->update('product', $data);
                $responce = array(
                    'success' => true,
                    'data' => 'success',
                    'message' => 'product successfully',
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

    function variant_2($para1 = '', $para2 = '')
    {

        if ($para1 == 'delete')
        {

            $this
                ->db
                ->where('variant_2_id', $para2);
            $this
                ->db
                ->delete('variant_2');
        }
        $responce = array(
            'success' => true,
        );
        echo json_encode($responce);
    }
    
    function addon($para1 = '',$para2 = '')
    {
        if($para1 == "delete")
        {
            $this
                ->db
                ->where('addon_id', $para2);
            $this
                ->db
                ->delete('addon');
        }
        $responce = array(
            'success' => true,
        );
        echo json_encode($responce);
    }

    function variant($para1 = '', $para2 = '')
    {

        if ($para1 == 'delete')
        {

            $this
                ->db
                ->where('variant_id', $para2);
            $this
                ->db
                ->delete('variant');
        }
        $responce = array(
            'success' => true,
        );
        echo json_encode($responce);
    }

    /* Item Product */

    function Itemproduct($para1 = '', $para2 = '', $para3 = '', $para4 = '')
    {
        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $para4, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            if ($para1 == 'add')
            {

                $data['category'] = $this
                    ->input
                    ->post('category');
                $data['title'] = $this
                    ->input
                    ->post('productTitle');
                $data['status'] = 'true';
                $data['product_type'] = $para2;
                $data['rating_num'] = 0;
                $data['rating_total'] = 0.0;
                $data['rating_user'] = '[]';
                $data['add_timestamp'] = time();
                $data['variant_status'] = true;
                $data['added_by'] = json_encode(array(
                    'type' => 'vendor',
                    'id' => $this
                        ->input
                        ->post('vendor')
                ));
                $this
                    ->db
                    ->insert('restaurantproduct', $data);
               $id = $this
                    ->db
                    ->insert_id();
                $data1['product_id'] = $id;
                $data1['quantity'] = $this
                    ->input
                    ->post('quantity');
                
                $data1['name'] = '';
                $data1['unit'] = $this
                    ->input
                    ->post('unit');
                
                $data1['sale_price'] = $this
                    ->input
                    ->post('salePrice');
                
                $data1['strike_price'] = $this
                    ->input
                    ->post('strikePrice');
                
                $data1['type'] = $para2;
                
                $data1['vendor_id'] = $this
                    ->input
                    ->post('vendor');
                    
                $data1['food_type'] = $this
                    ->input
                    ->post('foodType');

                $this
                    ->db
                    ->insert('variant', $data1);

                $id1 = $this
                    ->db
                    ->insert_id();
                    
                $path = $_FILES['image']['name'];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $data_image['image'] = 'restaurantproduct_' . $id1 . '.' . $ext;
                $this
                    ->db
                    ->where('variant_id', $id1);
                $this
                    ->db
                    ->update('variant', $data_image);
                $this
                    ->crud_model
                    ->file_up("image", "restaurantproduct", $id1, '', 'no', '.' . $ext);

                $responce = array(
                    'success' => $databanner['image'],
                );

            }
            else if ($para1 == 'list')
            {

                $this
                    ->db
                    ->select('category_id, category_name');
                $loop = $this
                    ->db
                    ->get_where('category', array(
                    'data_vendors' => $para4
                ))->result_array();

                foreach ($loop as $row)
                {
                    $this
                        ->db
                        ->select('restaurantproduct_id, title , product_type,status');
                    $data1 = [];
                    $this
                        ->db
                        ->order_by('restaurantproduct_id', 'desc');
                    $loop1 = $this
                        ->db
                        ->get_where('restaurantproduct', array(
                        'category' => $row['category_id']
                    ))->result_array();

                    foreach ($loop1 as $row1)
                    {

                        $this
                            ->db
                            ->limit(1);
                        $variant = $this
                            ->db
                            ->get_where('variant', array(
                            'product_id' => $row1['restaurantproduct_id']
                        ))->result_array();

                        $variants = [];

                        foreach ($variant as $row2)
                        {
                            $i++;
                            $variants = array(
                                'variant_id' => $row2['variant_id'],
                                'product_id' => $row2['product_id'],
                                'quantity' => $row2['quantity'],
                                'name' => $row2['name'],
                                'unit' => $row2['unit'],
                                'sale_price' => $row2['sale_price'],
                                'strike_price' => $row2['strike_price'],
                                'tax' => $row['tax'],
                                'discount' => doubleval(['discount']),
                                'type' => $row2['type'],
                                'itemType' => $row2['food_type'],
                                'selected' => false,
                                'uploadImage' => base_url() . 'uploads/restaurantproduct_image/' . $row2['image'],

                            );
                        }

                        if ($row1['status'] == 'true')
                        {
                            $row1['status'] = true;
                        }
                        else
                        {
                            $row1['status'] = false;
                        }

                        $data1[] = array(

                            'id' => $row1['restaurantproduct_id'],
                            'product_name' => $row1['title'],
                            'variant' => $variants,
                            'productType' => $row1['product_type'],
                            'status' => $row1['status'],
                        );

                    }
                    $data[] = array(
                        'id' => $row['category_id'],
                        'categoryName' => $row['category_name'],
                        'productdetails' => $data1,
                    );

                }

                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'product retrieved successfully',
                );

            }
            else if ($para1 == 'delete')
            {

                $this
                    ->db
                    ->where('restaurantproduct_id', $para2);
                $this
                    ->db
                    ->delete('restaurantproduct');
                $this
                    ->db
                    ->where('variant_id', $para2);
                $this
                    ->db
                    ->delete('variant');
                $this
                    ->db
                    ->where('addon_id', $para2);
                $this
                    ->db
                    ->delete('addon');
                $responce = array(
                    'success' => true,
                );
            }
            else if ($para1 == 'edit')
            {

                $data['category_name'] = $this
                    ->input
                    ->post('categoryName');
                $data['data_vendors'] = $this
                    ->input
                    ->post('vendor');
                $this
                    ->db
                    ->where('category_id', $para2);
                $this
                    ->db
                    ->update('category', $data);
                $id = $para2;
                $path = $_FILES['image']['name'];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                if ($ext != NULL)
                {
                    $databanner['banner'] = 'category_' . $id . '.' . $ext;
                    $this
                        ->crud_model
                        ->file_up("image", "category", $id, '', 'no', '.' . $ext);
                    $this
                        ->db
                        ->where('category_id', $id);
                    $this
                        ->db
                        ->update('category', $databanner);
                }
                $responce = array(
                    'success' => true,
                );
            }
            else if ($para1 == 'variantlist')
            {

                $variant = $this
                    ->db
                    ->get_where('variant', array(
                    'product_id' => $para2
                ))->result_array();
                $data = [];
                foreach ($variant as $row2)
                {

                    $data[] = array(
                        'variant_id' => $row2['variant_id'],
                        'product_id' => $row2['product_id'],
                        'quantity' => $row2['quantity'],
                        'name' => $row2['name'],
                        'unit' => $row2['unit'],
                        'sale_price' => $row2['sale_price'],
                        'strike_price' => $row2['strike_price'],
                        'tax' => $row2['tax'],
                        'discount' => doubleval($row2['discount']) ,
                        'type' => $row2['type'],
                        'foodType' => $row2['food_type'],
                        'selected' => false,
                        'uploadImage' => base_url() . 'uploads/restaurantproduct_image/' . $row2['image'],

                    );
                }
                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'product retrieved successfully',
                );

            }
            else if ($para1 == 'variant_add')
            {

                $data1['product_id'] = $para2;
                $data1['quantity'] = $this
                    ->input
                    ->post('quantity');
                $data1['name'] = '';
                $data1['unit'] = $this
                    ->input
                    ->post('unit');
                $data1['sale_price'] = $this
                    ->input
                    ->post('salePrice');
                $data1['strike_price'] = $this
                    ->input
                    ->post('strikePrice');
                $data1['type'] = $para3;
                $data1['food_type'] = $this
                    ->input
                    ->post('foodType');
                $data1['vendor_id'] = $this
                    ->input
                    ->post('vendor');
                $this
                    ->db
                    ->insert('variant', $data1);
                $id1 = $this
                    ->db
                    ->insert_id();
                $path = $_FILES['image']['name'];
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                $data_image['image'] = 'restaurantproduct_' . $id1 . '.' . $ext;
                $this
                    ->db
                    ->where('variant_id', $id1);
                $this
                    ->db
                    ->update('variant', $data_image);
                $this
                    ->crud_model
                    ->file_up("image", "restaurantproduct", $id1, '', 'no', '.' . $ext);

                $responce = array(
                    'success' => true,
                    'data' => 'success',
                    'message' => 'variant added successfully',
                );
            }
            else if ($para1 == 'variant_edit')
            {

                $data1['product_id'] = $para2;
                $data1['quantity'] = $this
                    ->input
                    ->post('quantity');
                $data1['name'] = '';
                $data1['unit'] = $this
                    ->input
                    ->post('unit');
                $data1['sale_price'] = $this
                    ->input
                    ->post('salePrice');
                $data1['strike_price'] = $this
                    ->input
                    ->post('strikePrice');
                $data1['type'] = $para3;
                $data1['food_type'] = $this
                    ->input
                    ->post('foodType');
                 $data1['discount'] = $this->input->post('discount');
                 $data1['tax'] = $this->input->post('tax');
            

                


                $id1 = $this
                    ->input
                    ->post('variant_id');

                    $data1['image'] = "restaurantproduct_" . $id1 . ".png";

                $path = $_FILES['image']['name'];

                $ext = pathinfo($path, PATHINFO_EXTENSION);
                if ($ext != NULL)
                {

                    $this
                        ->crud_model
                    //    ->file_up("image", "restaurantproduct", $id1, '', 'no', '.' . $ext);
                          ->file_up("image", "restaurantproduct", $id1, '', 'no', '.png');
                }
                $this
                    ->db
                    ->where('variant_id', $id1);
                $this
                    ->db
                    ->update('variant', $data1);

                $responce = array(
                    'success' => true,
                    'data' => 'success',
                    'message' => 'variant Updated successfully',
                );
            }
            else if ($para1 == 'product_status')
            {

                $data['status'] = $para3;
                $this
                    ->db
                    ->where('restaurantproduct_id', $para2);
                $this
                    ->db
                    ->update('restaurantproduct', $data);
                $responce = array(
                    'success' => true,
                    'data' => 'success',
                    'message' => 'product successfully',
                );

            }
            else if ($para1 == 'addon_list')
            {

                $loop = $this
                    ->db
                    ->get_where('addon', array(
                    'product_id' => $para2
                ))->result_array();
                $data = [];
                foreach ($loop as $row2)
                {

                    $data[] = array(
                        'id' => $row2['addon_id'],
                        'productId' => $row2['product_id'],
                        'name' => $row2['name'],
                        'salesPrice' => floatval($row2['sales_price']) ,
                        'type' => $row2['type'],
                        'foodType' => $row2['food_type'],
                    );
                }
                $responce = array(
                    'success' => true,
                    'data' => $data,
                    'message' => 'addon retrieved successfully',
                );

            }
            else if ($para1 == 'addon_add')
            {
                $content_data = file_get_contents("php://input");
                $get_data = json_decode($content_data);
                $data['sales_price'] = $get_data->salesPrice;
                $data['food_type'] = $get_data->foodType;
                $data['name'] = $get_data->name;
                $data['type'] = $get_data->type;
                $data['product_id'] = $get_data->productId;
                $data['vendor_id'] = $para4;
                
                if($this
                    ->db
                    ->insert('addon', $data)){
                        $res = true;
                        
                    }else{
                        $res = false;
                    }
                $responce = array(
                    'success' => $res,
                    'data' => $data
                );
            }
            else if ($para1 == 'addon_update')
            {

                $content_data = file_get_contents("php://input");
                $get_data = json_decode($content_data);
                $data['sales_price'] = $get_data->salesPrice;
                $data['food_type'] = $get_data->foodType;
                $data['name'] = $get_data->name;
                $this
                    ->db
                    ->where('addon_id', $para2);
                $this
                    ->db
                    ->update('addon', $data);

                $responce = array(
                    'success' => true,
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

    function profileimage($para1 = '', $para2 = '')
    {
        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $para1, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            $id = $para1;
            $path = $_FILES['image']['name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $data_banner['logo'] = 'vendor_' . $id . '.' . $ext;
            $this
                ->crud_model
                ->file_up("image", "vendor", $id, '', 'no', '.png');
            $this
                ->db
                ->where('vendor_id', $id);
            $this
                ->db
                ->update('vendor', $data_banner);
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

    function coverimage($para1 = '', $para2 = '')
    {
        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $para1, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            $id = $para1;
            $path = $_FILES['image']['name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $data_banner['cover_image'] = 'cover_' . $id . '.jpg';
            $this
                ->crud_model
                ->file_up("image", "cover", $id, '', 'no', '.jpg');
            $this
                ->db
                ->where('vendor_id', $id);
            $this
                ->db
                ->update('vendor', $data_banner);
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

    function passwordManagement($para1 = '', $para2 = '')
    {
        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $para2, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            if ($para1 == 'update')
            {
                if ($this
                    ->Api_model
                    ->singleselectbox('vendor', 'vendor_id', $para2, 'password') == sha1($this
                    ->input
                    ->post('prePassword')))
                {
                    $data['password'] = sha1($this
                        ->input
                        ->post('password'));
                    $this
                        ->db
                        ->where('vendor_id', $para2);
                    $this
                        ->db
                        ->update('vendor', $data);
                    $responce = array(
                        'success' => true,
                    );
                }
                else
                {
                    $responce = array(
                        'success' => false,
                    );
                }
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

    function home_settings($para1 = '', $para2 = '')
    {

        if ($para1 == 'featured')
        {
            $this
                ->db
                ->order_by('product_id', 'RANDOM');
            $this
                ->db
                ->limit(9);
            $this
                ->db
                ->select('product_id, title , sale_price , purchase_price, discount, rating_num, num_of_imgs, color, unit');

            $loop = $this
                ->db
                ->get_where('product', array(
                'featured' => 'ok'
            ))
                ->result_array();
            if (count($loop) > 0)
            {
                foreach ($loop as $row)
                {

                    if ($row['color'] == 'null')
                    {
                        $color1 = 'no';
                    }
                    else
                    {
                        $color1 = 'yes';
                    }
                    $data[] = array(
                        'id' => $row['product_id'],
                        'product_name' => $row['title'],
                        'price' => $row['sale_price'],
                        'strike' => $row['purchase_price'],
                        'offer' => intval($row['discount']) ,
                        'rating' => intval($row['rating_num']) ,
                        'image' => base_url() . 'uploads/product_image/product_' . $row['product_id'] . '_1_thumb.jpg',
                        'num_of_imgs' => intval($row['num_of_imgs']) ,
                        'color' => $color1,
                        'unit' => $row['unit'],
                    );
                }
            }
            else
            {
                $data[] = array();
            }
            $responce = array(
                'success' => true,
                'data' => $data,
                'message' => 'address retrieved successfully',
            );
        }
        else if ($para1 == 'todaydeals')
        {
            $this
                ->db
                ->limit(4);

            $this
                ->db
                ->select('product_id, title , sale_price , purchase_price, discount, rating_num, num_of_imgs, color');

            $loop = $this
                ->db
                ->get_where('product', array(
                'deal' => 'ok'
            ))
                ->result_array();
            if (count($loop) > 0)
            {
                foreach ($loop as $row)
                {
                    if ($row['color'] == 'null')
                    {
                        $color1 = 'no';
                    }
                    else
                    {
                        $color1 = 'yes';
                    }
                    $data[] = array(
                        'id' => $row['product_id'],
                        'product_name' => $row['title'],
                        'price' => $row['sale_price'],
                        'strike' => $row['purchase_price'],
                        'offer' => intval($row['discount']) ,
                        'rating' => intval($row['rating_num']) ,
                        'image' => base_url() . 'uploads/product_image/product_' . $row['product_id'] . '_1_thumb.jpg',
                        'num_of_imgs' => intval($row['num_of_imgs']) ,
                        'color' => $color1
                    );
                }
            }
            else
            {
                $data[] = array();
            }
            $responce = array(
                'success' => true,
                'data' => $data,
                'message' => 'address retrieved successfully',
            );

        }
        else if ($para1 == 'most_popular')
        {
            $loop = $this
                ->db
                ->get_where('most_popular')
                ->result_array();
            foreach ($loop as $row)
            {
                $list[] = array(
                    'title' => $row['title1'],
                    'image' => base_url() . 'uploads/most_popular_image/' . $row['image1'],
                    'offer' => $row['offer1'],
                );
            }

            $data[] = array(
                'title' => 'Today Fashion Deals',
                'image' => base_url() . 'uploads/general_image/background.jpg',
                'popularList' => $list,
            );

            $responce = array(
                'success' => true,
                'data' => $data,
                'message' => 'address retrieved successfully',
            );
        }
        else if ($para1 == 'bestof')
        {

            $this
                ->db
                ->limit(4);
            $this
                ->db
                ->select('categoryid, bestof_id');
            $loop = $this
                ->db
                ->get_where('bestof')
                ->result_array();

            foreach ($loop as $row)
            {
                $this
                    ->db
                    ->select('product_id, title , sale_price , purchase_price, discount, rating_num, num_of_imgs, color, unit');
                $data1 = [];
                $this
                    ->db
                    ->order_by('rand()');
                $this
                    ->db
                    ->limit(10);
                $loop1 = $this
                    ->db
                    ->get_where('product', array(
                    'category' => $row['categoryid']
                ))->result_array();
                foreach ($loop1 as $row1)
                {
                    if ($row1['color'] == 'null')
                    {
                        $color1 = 'no';
                    }
                    else
                    {
                        $color1 = 'yes';
                    }
                    $data1[] = array(
                        'id' => $row1['product_id'],
                        'product_name' => $row1['title'],
                        'price' => $row1['sale_price'],
                        'strike' => $row1['purchase_price'],
                        'offer' => intval($row1['discount']) ,
                        'rating' => intval($row1['rating_num']) ,
                        'image' => base_url() . 'uploads/product_image/product_' . $row1['product_id'] . '_1_thumb.jpg',
                        'num_of_imgs' => intval($row1['num_of_imgs']) ,
                        'color' => $color1,
                        'unit' => $row1['unit'],
                    );
                }

                $data[] = array(
                    'id' => $row['bestof_id'],
                    'category' => $this
                        ->Api_model
                        ->singleselectbox('category', 'category_id', 61, 'category_name') ,
                    'productdetails' => $data1,
                );
            }

            $responce = array(
                'success' => true,
                'data' => $data,
                'message' => 'subcategories retrieved successfully',
            );
        }

        echo json_encode($responce);

    }

    function category_wise_product($para1 = '', $para2 = '')
    {

        $this
            ->db
            ->limit(4);
        $this
            ->db
            ->select('sub_category_id, sub_category_name, banner');
        $loop = $this
            ->db
            ->get_where('sub_category', array(
            'category' => $para1
        ))->result_array();

        foreach ($loop as $row)
        {
            $this
                ->db
                ->select('product_id, title ,  rating_num, num_of_imgs');
            $data1 = [];
            $this
                ->db
                ->order_by('rand()');
            $this
                ->db
                ->limit(10);
            $loop1 = $this
                ->db
                ->get_where('product', array(
                'category' => $para1,
                'sub_category' => $row['sub_category_id']
            ))->result_array();
            foreach ($loop1 as $row1)
            {
                if ($row1['color'] == 'null')
                {
                    $color1 = 'no';
                }
                else
                {
                    $color1 = 'yes';
                }
                $data1[] = array(
                    'id' => $row1['product_id'],
                    'product_name' => $row1['title'],
                    'price' => $row1['sale_price'],
                    'strike' => $row1['purchase_price'],
                    'offer' => intval($row1['discount']) ,
                    'rating' => intval($row1['rating_num']) ,
                    'image' => base_url() . 'uploads/product_image/product_' . $row1['product_id'] . '_1_thumb.jpg',
                    'num_of_imgs' => intval($row1['num_of_imgs']) ,
                    'color' => $color1,
                    'unit' => $row1['unit'],
                );
            }

            $data[] = array(
                'id' => $row['sub_category_id'],
                'subcategory_name' => $row['sub_category_name'],
                'image' => base_url() . 'uploads/sub_category_image/' . $row['banner'],
                'productdetails' => $data1,
            );
        }

        $responce = array(
            'success' => true,
            'data' => $data,
            'message' => 'subcategories retrieved successfully',
        );
        echo json_encode($responce);
    }

    /** bestof **/
    function bestof($para1 = '', $para2 = '')
    {

        $this
            ->db
            ->limit(4);
        $this
            ->db
            ->select('categoryid, bestof_id');
        $loop = $this
            ->db
            ->get_where('bestof')
            ->result_array();

        foreach ($loop as $row)
        {
            $this
                ->db
                ->select('product_id, title , sale_price , purchase_price, discount, rating_num, num_of_imgs, color, unit');
            $data1 = [];
            $this
                ->db
                ->order_by('rand()');
            $this
                ->db
                ->limit(10);
            $loop1 = $this
                ->db
                ->get_where('product', array(
                'category' => $row['categoryid']
            ))->result_array();
            foreach ($loop1 as $row1)
            {
                if ($row1['color'] == 'null')
                {
                    $color1 = 'no';
                }
                else
                {
                    $color1 = 'yes';
                }
                $data1[] = array(
                    'id' => $row1['product_id'],
                    'product_name' => $row1['title'],
                    'price' => $row1['sale_price'],
                    'strike' => $row1['purchase_price'],
                    'offer' => intval($row1['discount']) ,
                    'rating' => intval($row1['rating_num']) ,
                    'image' => base_url() . 'uploads/product_image/product_' . $row1['product_id'] . '_1_thumb.jpg',
                    'num_of_imgs' => intval($row1['num_of_imgs']) ,
                    'color' => $color1,
                    'unit' => $row1['unit'],
                );
            }

            $data[] = array(
                'id' => $row['bestof_id'],
                'category' => $this
                    ->Api_model
                    ->singleselectbox('category', 'category_id', 61, 'category_name') ,
                'productdetails' => $data1,
            );
        }

        $responce = array(
            'success' => true,
            'data' => $data,
            'message' => 'subcategories retrieved successfully',
        );
        echo json_encode($responce);
    }

    /** offers upto **/
    function offersupto($para1 = '', $para2 = '')
    {

        $this
            ->db
            ->limit(5);

        $loop = $this
            ->db
            ->get_where('categories_wise_offer')
            ->result_array();

        foreach ($loop as $row)
        {

            $data1 = [];
            $this
                ->db
                ->order_by('rand()');
            $this
                ->db
                ->limit(10);
            $loop1 = $this
                ->db
                ->get_where('sub_category', array(
                'category' => $row['categoryid']
            ))->result_array();
            foreach ($loop1 as $row1)
            {

                $data1[] = array(
                    'id' => $row1['sub_category_id'],
                    'name' => $row1['sub_category_name'],
                    'category' => $row1['category'],
                    'image' => base_url() . 'uploads/sub_category_image/' . $row1['banner'],
                );
            }

            $data[] = array(
                'id' => $row['categories_wise_offer_id'],
                'category' => $this
                    ->Api_model
                    ->singleselectbox('category', 'category_id', $row['categoryid'], 'category_name') ,
                'offer' => $row['offer'],
                'title' => $row['title'],
                'short_description' => $row['short_description'],
                'image' => base_url() . 'uploads/categories_wise_offer_image/' . $row['image'],
                'subcategory' => $data1,
            );
        }

        $responce = array(
            'success' => true,
            'data' => $data,
            'message' => 'subcategories retrieved successfully',
        );
        echo json_encode($responce);
    }

    function categories($para1 = '', $para2 = '')
    {
        $this
            ->db
            ->order_by('category_id', 'asc');
        $loop = $this
            ->db
            ->get_where('category')
            ->result_array();
        foreach ($loop as $row)
        {
            $data[] = array(
                'id' => $row['category_id'],
                'name' => $row['category_name'],
                'image' => base_url() . 'uploads/category_image/' . $row['banner'],
            );
        }
        $responce = array(
            'success' => true,
            'data' => $data,
            'message' => 'Categories retrieved successfully',
        );
        echo json_encode($responce);
    }

    function slider($para1 = '', $para2 = '')
    {

        $this
            ->db
            ->order_by('slider_id', 'asc');
        $loop = $this
            ->db
            ->get_where('slider')
            ->result_array();
        foreach ($loop as $row)
        {
            $data[] = array(
                'id' => $row['slider_id'],
                'slider_text' => $row['slider_text'],
                'button_text' => $row['button_text'],
                'button_color' => 'red',
                'image' => base_url() . 'uploads/slider_image/' . $row['image'],
            );
        }

        $responce = array(
            'success' => true,
            'data' => $data,
            'message' => 'slider retrieved successfully',
        );
        echo json_encode($responce);
    }

    function trends($para1 = '', $para2 = '')
    {

        $this
            ->db
            ->order_by('trends_id', 'asc');
        $loop = $this
            ->db
            ->get_where('trends')
            ->result_array();
        foreach ($loop as $row)
        {
            $data[] = array(
                'id' => $row['trends_id'],
                'name' => $row['title'],
                'image' => base_url() . 'uploads/trends_image/' . $row['image'],
            );
        }
        $responce = array(
            'success' => true,
            'data' => $data,
            'message' => 'trends retrieved successfully',
        );
        echo json_encode($responce);
    }

    function category_ads($para1 = '', $para2 = '')
    {

        $this
            ->db
            ->order_by('categoryads_id', 'asc');
        $loop = $this
            ->db
            ->get_where('categoryads', array(
            'category' => $para1
        ))->result_array();
        foreach ($loop as $row)
        {
            $data[] = array(
                'id' => $row['categoryads_id'],
                'image' => base_url() . 'uploads/categoryads_image/' . $row['image'],
            );
        }
        $responce = array(
            'success' => true,
            'data' => $data,
            'message' => 'category_ads retrieved successfully',
        );
        echo json_encode($responce);
    }
    
    function vendor_forgot_password(){
        $content_data = file_get_contents("php://input");
        $get_data = json_decode($content_data);
        
        $email = $get_data->email;
        
        if($email != '' && $email != null)
        {
        
            $check_email = $this->db->get_where('vendor',array('email'=>$email))->result_array();
            
            $count_rows = count($check_email);
            
            if($count_rows > 0)
            {
                $random_otp = rand(1000,9999);
                $data['email'] = $email;
                $data['otp'] = $random_otp;
                $res = true;
                
                $message = "Your OTP is : $random_otp";
                
                $this->email->from('admin@gorunn.xyz', 'Reset Password');
                $this->email->to($email);
                $this->email->subject('Reset Password OTP');
                $this->email->message($message);
                $this->email->send();
                
            }else{
                $data = "Email does not exists";
                $res = false;
            }
            
            $responce = array(
                'success' => $res,
                'data' => $data
            );
            echo json_encode($responce);
        }
    }

    function resetPassword(){
     $email = $this->input->post('email');
     $password = $this->input->post('password');
     $encrypted_password = sha1($password);
     
     $check_email = $this->db->get_where('vendor',array('email'=>$email))->result_array();
            
     $count_rows = count($check_email);
     
     if($count_rows > 0)
     {
         $this->db->where('email', $email);
         $this->db->update('vendor', array('password'=>$encrypted_password));
         $data = "Password Updated Successfully";
         $res = true;
     }else{
         $data = "Email does not exists";
         $res = false;
     }
     
     $responce = array(
        'success' => $res,
         'data' => $data
    );
    echo json_encode($responce);
    }
    
    function driverAssign($para1 = ''){
        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $para1, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            $status = $this->input->post('status');
            $this->db->where('vendor_id', $para1);
            $this->db->update('vendor', array('driver_assign'=>$status));
            $data['status'] = $status;
            $res = true;
        }
        else{
            $data = "API token is Invalid";
            $res = false;
        }
        $responce = array(
                'success' => $res,
                 'data' => $data
            );
        echo json_encode($responce);
        
    }
    
    function autoAssign($para1 = ''){
        if ($this
            ->Api_model
            ->singleselectbox('vendor', 'vendor_id', $para1, 'token') == $this
            ->input
            ->get('api_token', true))
        {
            $status = $this->input->post('status');
            $this->db->where('vendor_id', $para1);
            $this->db->update('vendor', array('auto_assign'=>$status));
            $data['status'] = $status;
            $res = true;
        }
        else{
            $data = "API token is Invalid";
            $res = false;
        }
        $responce = array(
                'success' => $res,
                 'data' => $data
            );
        echo json_encode($responce);
        
    }
    
    function sendOTPOnEmail()
    {

        $email = $this->input->post('email');

        if ($email != '' && $email != null) {

            $check_email = $this->db->get_where('vendor', array('email' => $email))->result_array();

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

            $check_phone = $this->db->get_where('vendor', array('phone' => $phone))->result_array();

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
    
    
}
/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */

