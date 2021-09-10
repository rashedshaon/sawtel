<?php namespace Bol\Lpg\Api;

use Validator;
use Carbon\Carbon;
use Bol\Lpg\Api\ApiController;
use Bol\Lpg\Models\Distribution;
use Bol\Lpg\Models\Customer;
use Bol\Lpg\Models\Stock;
use Bol\Lpg\Models\Seller;
use Bol\Lpg\Models\Product;

use RLuders\JWTAuth\Classes\JWTAuth;

class Distributions extends ApiController
{
    public function getCustomer(JWTAuth $auth)
    {
        if (!$user = $auth->user()) {
            return response()->json(['status' => 'error', 'msg' => 'User not found']);
        }

        if (!$user->hasAccess('bol.lpg.make_sales')) {
            return response()->json(['status' => 'error', 'msg' => 'User has no access']);
        }

        $data = (object) post();
        
        $rules = [
            'phone' => 'required|regex:/^[0-1]{2}[0-9]{9}$/',
        ];
  
        $validation = Validator::make(post(), $rules);

        if ($validation->fails()){ 
            return response()->json(["status" => "error", "msg" => $validation->messages()->all()]);
        }
  
        $customer = Customer::where('phone', $data->phone)->get()->first();

        if(!$customer)
        {
            return response()->json(["status" => "error", "data" => [], "msg" => "No Data found."]);
        }

        $data = [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'customer_type' => $customer->customer_type,
            'customer_type_name' => $customer->type_name,
        ];


        return response()->json(["status" => "ok", "data" => $data, "msg" => "Data successfully found."]);
    }

    public function makeSale(JWTAuth $auth)
    {
        if (!$user = $auth->user()) {
            return response()->json(['status' => 'error', 'msg' => 'User not found']);
        }

        // if (!$user->hasAccess('bol.lpg.make_sales')) {
        //     return response()->json(['status' => 'error', 'msg' => 'User has no access']);
        // }

        $data = (object) post();

        // dd($data);
        
        $rules = [
            'phone' => 'required|regex:/^[0-1]{2}[0-9]{9}$/',
            'lat_long' => 'required',
            'customer_type' => 'required',
            'in_area' => 'required',
        ];
  
        $validation = Validator::make(post(), $rules);
        if ($validation->fails()){ 
            return response()->json(["status" => "error", "msg" => $validation->messages()->all()]);
        }
  
        $customer = Customer::where('phone', $data->phone)->get()->first();

        $seller = Seller::where('login_id', $user->id)->get()->first();

        if(!$customer)
        {
            $customer = new Customer();
            $customer->name = $data->name;
            $customer->phone = $data->phone;
            $customer->customer_type = $data->customer_type;
            $customer->lat_long = $data->lat_long;
            $customer->address = $data->address;
            $customer->region_id = $seller->region_id;
            $customer->area_id = $seller->area_id;
            $customer->territory_id = $seller->territory_id;
            $customer->point_id = $seller->point_id;
            $customer->save();
        }

        $customer_id = $customer->id;

        

        // dd($data);
        $otp = rand(111111, 999999);
        $ids = [];
        foreach($data->products as $item)
        {
            if($item['quantity'])
            {
                $distribution = new Distribution();
                $distribution->seller_id = $user->id;
                $distribution->seller_type = $seller->type;
                $distribution->customer_id = $customer_id;
                $distribution->customer_type = $data->customer_type;
                $distribution->product_id = $item['id'];
                $distribution->quantity = $item['quantity'];
                $distribution->region_id = $seller->region_id;
                $distribution->area_id = $seller->area_id;
                $distribution->territory_id = $seller->territory_id;
                $distribution->point_id = $seller->point_id;
                $distribution->distributor_id = $seller->distributor_id;
                $distribution->in_area = $data->in_area;
                $distribution->otp = $otp;
                $distribution->save();

                //Stock update
                $stock = Stock::where('seller_id', $seller->distributor_id)->get()->first();

                if(!$stock)
                {
                    $stock = new Stock();
                    $stock->seller_id = $seller->distributor_id;
                    $stock->seller_type = 1;
                    $stock->qty_12 = 0;
                    $stock->qty_22 = 0;
                    $stock->empty_12 = 0;
                    $stock->empty_22 = 0;
                    $stock->save();
                }

                if($distribution->product->stock_code == 'package_12')
                {
                    $stock->decrement('qty_12', $item['quantity']);
                }
                elseif($distribution->product->stock_code == 'rifle_12')
                {
                    $stock->increment('empty_12', $item['quantity']);
                    $stock->decrement('qty_12', $item['quantity']);
                }
                elseif($distribution->product->stock_code == 'package_22')
                {
                    $stock->decrement('qty_22', $item['quantity']);
                }
                elseif($distribution->product->stock_code == 'rifle_22')
                {
                    $stock->increment('empty_22', $item['quantity']);
                    $stock->decrement('qty_22', $item['quantity']);
                }
                
                

                $ids[] = $distribution->id;
            }
        }

        if(count($ids))
        {
            $data = [
                "ids" => implode(',', $ids),
                "phone" => $customer->phone,
            ];

            $sms_content = "Please give this otp: $otp to Delivery Man";

            $this->send_message($data['phone'], $sms_content);

            return response()->json(["status" => "ok", "data" => $data, "msg" => "Data successfully saved."]);
        }
        else
        {
            return response()->json(["status" => "error", "data" => [], "msg" => "Data not saved."]);
        }
    }

    public function sendCustomerOtp($data = [])
    {
        $post = post();

        // dd($post);

        if(count($data))
        {
            $post = $data;
        }

        $rules = [
            'phone' => 'required|regex:/^[0-1]{2}[0-9]{9}$/',
            'ids' => 'required',
        ];
        
        $messages = [
            'phone.required' => "You must be enter a Phone Number.",
            'ids.required' => "Need comma separated distribution ids.",
            'phone.regex' => "Please enter a valid Phone Number.",
        ];

        $validation = Validator::make($post, $rules, $messages);
        
        if ($validation->fails())
        { 
            return response()->json(["status" => "error", "msg" => $validation->messages()->all()]);
        }

        $data = Distribution::whereIn('id', explode(',', $post['ids']));
        
        //Calculate resend otp time
        $now             = Carbon::now();
        $last_updated    = Carbon::parse($data->first()->updated_at);
        $duration        = $last_updated->diffInSeconds($now);
        $resend_duration = 60;

        if($resend_duration > $duration)
        {
            return response()->json(["status" => "error", "msg" => "You can resend after $resend_duration seconds."]);
        }

        $otp = rand(111111, 999999);
        Distribution::whereIn('id', explode(',', $post['ids']))->update(['otp'=>$otp]);

        $sms_content = "Please give this otp: $otp to Delivery Man";

        $this->send_message($post['phone'], $sms_content);


        return response()->json(["status" => "ok", "data" => $data, "msg" => "An OTP has been sent to customer phone ".$post['phone']]);
    }

    public function verifyCustomerOtp(JWTAuth $auth)
    {
        if (!$user = $auth->user()) {
            return response()->json(['status' => 'error', 'msg' => 'User not found']);
        }

        $post = post();

        $rules = [
            'otp' => 'required',
        ];

        $validation = Validator::make($post, $rules);
        
        if ($validation->fails())
        { 
            return response()->json(["status" => "error", "msg" => $validation->messages()->all()]);
        }

        $data = Distribution::where('seller_id', $user->id)->where('otp', $post['otp']);

        if($data->count())
        {
            $data->update(['verification'=>1]);
            return response()->json(["status" => "ok", "data" => [], "msg" => "OTP successfully verified"]);
        }
        else
        {
            return response()->json(["status" => "error", "msg" => "OTP can not verified."]);
        }
    }
}