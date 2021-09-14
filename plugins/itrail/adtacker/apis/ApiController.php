<?php namespace ItRail\AdTacker\Apis;

use Backend\Classes\Controller;
use ItRail\AdTacker\Models\Product;
use ItRail\AdTacker\Models\Slide;
use ItRail\AdTacker\Models\Transaction;
use ItRail\AdTacker\Models\WithdrawRequest;
use ItRail\AdTacker\Models\Bank;
use ItRail\AdTacker\Models\Order;


/**
 * 200: OK. The standard success code and default option.
 * 201: Object created. Useful for the store actions.
 * 204: No content. When an action was executed successfully, but there is no content to return.
 * 206: Partial content. Useful when you have to return a paginated list of resources.
 * 400: Bad request. The standard option for requests that fail to pass validation.
 * 401: Unauthorized. The user needs to be authenticated.
 * 403: Forbidden. The user is authenticated, but does not have the permissions to perform an action.
 * 404: Not found. This will be returned automatically by Laravel when the resource is not found.
 * 500: Internal server error. Ideally you're not going to be explicitly returning this, but if something unexpected breaks, this is what your user is going to receive.
 * 503: Service unavailable. Pretty self explanatory, but also another code that is not going to be returned explicitly by the application.
 */

class ApiController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT');
        // header('Access-Control-Allow-Headers: Accept,Accept-Language,Content-Language,Content-Type');
        header('Access-Control-Allow-Headers: Origin, Content-Type, Accept,Authorization, X-Request-With');
        header('Access-Control-Allow-Credentials: true');
    }

    public function marge_errors($array) { 
        if (!is_array($array)) { 
          return FALSE; 
        } 
        $result = array(); 
        foreach ($array as $key => $value) { 
          if (is_array($value)) { 
            $result = array_merge($result, $this->marge_errors($value)); 
          } 
          else { 
            $result[$key] = $value; 
          } 
        } 
        return $result; 
    }



    function send_message($cell_no, $sms_content)
    {
        $params = array(
        'phone_number' => $cell_no,
        'message' => $sms_content
        );
        
        $url = 'https://hrd.bol-online.com/sms/sms.php';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,$params);
        curl_setopt_array($curl, array(CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => $url, CURLOPT_USERAGENT => '-k ', CURLOPT_SSL_VERIFYPEER => 0 ));
        $resp = curl_exec($curl);
        
        return $resp;
    }

    public function sub_words( $str, $max = 24, $char = ' ', $end = '' ) 
    {
        $str = trim( $str ) ;
        $str = $str . $char ;
        $len = strlen( $str ) ;
        $words = '' ;
        $w = '' ;
        $c = 0 ;
        for ( $i = 0; $i < $len; $i++ )
            if ( $str[$i] != $char )
                $w = $w . $str[$i] ;
            else
                if ( ( $w != $char ) and ( $w != '' ) ) {
                    $words .= $w . $char ;
                    $c++ ;
                    if ( $c >= $max ) {
                        break ;
                    }
                    $w = '' ;
                }
        if ( $i+1 >= $len ) {
            $end = '' ;
        }
        return trim( $words ) . $end ;
    }

    public function pagination($data)
    {
       return [
            'has_more_pages'    => $data->hasMorePages(),
            'count'             => $data->count(),
            'current_page'      => $data->currentPage(),
            'per_page'          => $data->perPage(),
            'previous_page'     => $data->currentPage()-1,
            'next_page'         => $data->currentPage()+1,
            'last_page'         => $data->lastPage(),
            'total'             => $data->total(),
       ];
    }

    public function forgetPassword()
    {
        $post = post();
        $rules = [
            'login' => 'required',
        ];
        
        $messages = [
            'login.required' => "You must be enter a user name",
        ];

        $validation = Validator::make($post, $rules, $messages);
        
        if ($validation->fails())
        { 
            return response()->json(["status" => "error", "msg" => $validation->messages()->all()]);
        }

        $data = User::where('login', $post['login'])->first();
        
        if($data)
        {
            $params = [
                "user" => "",
                "password" => "",
            ];

            // Send to address using no name
            Mail::sendTo('admin@domain.tld', 'itrail.adtracker::mail.reset_password', $params);
            return response()->json(["status" => "ok", "data" => [], "msg" => "A new password sent to your email ".$data->email]);
        }

        return response()->json(["status" => "error", "data" => [], "msg" => "No data found with this user name."]);
    }

    public function getBalance()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $data = Transaction::where('user_id', $user->id)->sum('amount');
        return response()->json(["status" => "ok", "data" => $data, "msg" => "Data Successfully Found"]);
    }

    public function homeData()
    {
        $data = [
            "slides" => Slide::isPublished()->orderBy('sort_order', 'asc')->get()
                        ->map(function($data){
                            return [
                                "id" => $data->id,
                                "name" => $data->name,
                                "photo" => $data->getPhoto(600,380),
                                "created_at" => $data->created_at->format('d-m-Y h:i:s A'),
                                "updated_at" => $data->updated_at->format('d-m-Y h:i:s A'),
                            ];
                        }),
            "products" => Product::isPublished()->isFeatured()->orderBy('updated_at', 'desc')->get()
                            ->map(function($data){
                                return [
                                    "id" => $data->id,
                                    "name" => $data->name,
                                    "photo" => $data->getPhoto(300,340),
                                    "price" => $data->price,
                                    "price_label" => $data->price_label,
                                    "description" => $data->description,
                                    "created_at" => $data->created_at->format('d-m-Y h:i:s A'),
                                    "updated_at" => $data->updated_at->format('d-m-Y h:i:s A'),
                                ];
                            }),
        ];
        return response()->json(["status" => "ok", "data" => $data, "msg" => "Data Successfully Found"]);
    }

    public function shopProducts()
    {
        $products =  Product::isPublished()->orderBy('created_at', 'desc')->paginate();
        $data = $products->map(function($data){
                                return [
                                    "id" => $data->id,
                                    "name" => $data->name,
                                    "photo" => $data->getPhoto(),
                                    "price" => $data->price,
                                    "price_label" => $data->price_label,
                                    "description" => $data->description,
                                    "created_at" => $data->created_at->format('d-m-Y h:i:s A'),
                                    "updated_at" => $data->updated_at->format('d-m-Y h:i:s A'),
                                ];
                            });

        return response()->json(["status" => "ok", "data" => $data, "msg" => count($data)." data found", 'pagination' => $this->pagination($products)]);
    }

    public function todaysIncome()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $type_id = Settings::get('daily_income_status');
        $daily_income_point = Settings::get('daily_income_point');
        $transaction = Transaction::where('user_id', $user->id)->where('type_id', $type_id)->whereDate('created_at', Carbon::today())->get()->first();

        if($transaction)
        {
            return response()->json(["status" => "ok", "data" => $daily_income_point, "msg" => "Successfully Collected Today"]);
        }
        
        return response()->json(["status" => "error", "data" => $daily_income_point, "msg" => "Still not collected"]);
    }

    public function submitIncome()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $type_id = Settings::get('daily_income_status');
        $daily_income_point = Settings::get('daily_income_point');
        

        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->type_id = $type_id;
        $transaction->amount  = $daily_income_point;
        $transaction->save();
        
        return response()->json(["status" => "ok", "data" => $daily_income_point, "msg" => "Successfully Collected Today"]);
    }

    public function getBanks()
    {
        $data = Bank::orderBy('name', 'asc')->get();

        return response()->json(["status" => "ok", "data" => $data, "msg" => "Successfully Found Data"]);
    }

    public function withdrawRequests()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $withdraw_requests =  WithdrawRequest::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate();
        $data = $withdraw_requests->map(function($data){
                                return [
                                    "id" => $data->id,
                                    "amount" => $data->amount,
                                    "bank" => $data->bank->name,
                                    "account_no" => $data->account_no,
                                    "status" => $data->status,
                                    "status_color" => $data->status_color,
                                    "created_at" => $data->created_at->format('d-m-Y h:i A'),
                                    "updated_at" => $data->updated_at->format('d-m-Y h:i A'),
                                ];
                            });

        return response()->json(["status" => "ok", "data" => $data, "msg" => count($data)." data found", 'pagination' => $this->pagination($withdraw_requests)]);
    }

    public function submitWithdrawRequests()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $post = post();
        $rules = [
            'bank_id' => 'required',
            'account_no' => 'required',
            'amount' => 'required',
        ];
        
        $messages = [
            'bank_id.required' => "Please select a bank.",
            'account_no.required' => "Please give account number.",
            'amount.required' => "Please type amount.",
        ];

        $validation = Validator::make($post, $rules, $messages);
        
        if ($validation->fails())
        { 
            return response()->json(["status" => "error", "msg" => $validation->messages()->all()]);
        }

        $current_balance = Transaction::where('user_id', $user->id)->sum('amount');
        $minimum_balance = Settings::get('minimum_balance_to_withdraw');

        if($current_balance < $minimum_balance)
        {
            return response()->json(["status" => "error", "data" => [], "msg" => "Balance should be over $minimum_balance to withdraw."]);
        }

        $withdraw_requests = new WithdrawRequest();
        $withdraw_requests->user_id = $user->id;
        $withdraw_requests->bank_id = $post['bank_id'];
        $withdraw_requests->account_no = $post['account_no'];
        $withdraw_requests->amount = $post['amount'];
        $withdraw_requests->save();

        return response()->json(["status" => "ok", "data" => [], "msg" => "Request Successfully Submitted"]);
    }

    public function submitOrder()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $post = post();

        $status_id = Settings::get('default_order_status');

        $order = new Order();
        $order->code = date('Hdmyms');
        $order->user_id = $user->id;
        $order->address = $post['address'];
        $order->status_id = $status_id;
        $order->save();

        foreach($post['items'] as $item)
        {
            $order_item = new OrderItem();
            $order_item->order_id = $order->id;
            $order_item->product_id = $item['product_id'];
            $order_item->name = $item['name'];
            $order_item->quantity = $item['quantity'];
            $order_item->price = $item['price'];
            $order_item->save();
        }

        return response()->json(["status" => "ok", "data" => [], "msg" => "Request Successfully Submitted"]);
    }

    public function getOrders()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $orders =  Order::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate();
        $data = $orders->map(function($data){
                                return [
                                    "id" => $data->id,
                                    "code" => $data->code,
                                    "total_price" => $data->total_price,
                                    "total_price_label" => $data->total_price_label,
                                    "total_quantity" => $data->total_quantity,
                                    "address" => $data->address->name,
                                    "status" => $data->status->name,
                                    "status_color" => $data->status->color,
                                    "items" => $data->items()->get()->map(function($data){
                                        return [
                                            "id" => $data->id,
                                            "name" => $data->name,
                                            "photo" => $data->getPhoto(),
                                            "quantity" => $data->quantity,
                                            "price" => $data->price,
                                            "price_label" => $data->price_label,
                                        ];
                                    }),
                                    "created_at" => $data->created_at->format('d-m-Y h:i A'),
                                    "updated_at" => $data->updated_at->format('d-m-Y h:i A'),
                                ];
                            });

        return response()->json(["status" => "ok", "data" => $data, "msg" => count($data)." data found", 'pagination' => $this->pagination($orders)]);
    }

    public function transactionSummery()
    {
        $user = JWTAuth::parseToken()->authenticate();
        
        $data = [
            "income" => Transaction::select('*', DB::raw("count(*) as count, sum('amount') as total_amount"))
                        ->where('user_id', $user->id)
                        ->where('amount', '>', 0)
                        ->groupBy('type_id')->get()
                        ->map(function($data){
                            return [
                                "id" => $data->id,
                                "name" => $data->type->name,
                                "total_amount" => $data->total_amount,
                                "count" => $data->count,
                                "created_at" => $data->created_at->format('d-m-Y h:i:s A'),
                                "updated_at" => $data->updated_at->format('d-m-Y h:i:s A'),
                            ];
                        }),
            "expense" => Transaction::select('*', DB::raw("count(*) as count, sum('amount') as total_amount"))
                        ->where('user_id', $user->id)
                        ->where('amount', '<', 0)
                        ->groupBy('type_id')->get()
                        ->map(function($data){
                            return [
                                "id" => $data->id,
                                "name" => $data->type->name,
                                "total_amount" => $data->total_amount,
                                "count" => $data->count,
                                "created_at" => $data->created_at->format('d-m-Y h:i:s A'),
                                "updated_at" => $data->updated_at->format('d-m-Y h:i:s A'),
                            ];
                        }),
        ];
        return response()->json(["status" => "ok", "data" => $data, "msg" => "Data Successfully Found"]);
    }

    public function transactionDetails()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $transactions =  Transaction::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate();
        $data = $transactions->map(function($data){
                                return [
                                    "id" => $data->id,
                                    "name" => $data->type->name,
                                    "amount" => $data->amount,
                                    "created_at" => $data->created_at->format('d-m-Y h:i A'),
                                    "updated_at" => $data->updated_at->format('d-m-Y h:i A'),
                                ];
                            });

        return response()->json(["status" => "ok", "data" => $data, "msg" => count($data)." data found", 'pagination' => $this->pagination($transactions)]);
    }

    public function accounts()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $accounts =  User::where('phone', $user->phone)->orderBy('created_at', 'desc')->paginate();
        $data = $accounts->map(function($data){
                                return [
                                    "id" => $data->id,
                                    "name" => $data->name,
                                    "login" => $data->login,
                                    "referrer" => $data->referrer->name,
                                    "created_at" => $data->created_at->format('d-m-Y h:i A'),
                                    "updated_at" => $data->updated_at->format('d-m-Y h:i A'),
                                ];
                            });

        return response()->json(["status" => "ok", "data" => $data, "msg" => count($data)." data found", 'pagination' => $this->pagination($accounts)]);
    }

    public function updateProfile()
    {
        
    }

    public function updatePhoto()
    {
        
    }

    public function sendFund()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $post = post();
        $rules = [
            'login' => 'required',
            'amount' => 'required',
        ];
        
        $messages = [
            'login.required' => "Please type a user name.",
            'amount.required' => "Please type an amount.",
        ];

        $validation = Validator::make($post, $rules, $messages);
        
        if ($validation->fails())
        { 
            return response()->json(["status" => "error", "msg" => $validation->messages()->all()]);
        }

        $receiver = User::where('login', $post['login'])->get()->first();

        if(!$receiver)
        {
            return response()->json(["status" => "error", "data" => [], "msg" => "User not found to send money."]);
        }

        $minimum_balance = Settings::get('minimum_balance_to_transfer');
        $current_balance = Transaction::where('user_id', $user->id)->sum('amount');

        if($current_balance < $minimum_balance)
        {
            return response()->json(["status" => "error", "data" => [], "msg" => "Balance should be over $minimum_balance to withdraw."]);
        }

        $fund_transfer_id = Settings::get('fund_transfer_transaction_status');
        $fund_receive_id = Settings::get('fund_receive_transaction_status');

        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->type_id = $fund_transfer_id;
        $transaction->amount = $post['amount'];
        $transaction->save();

        $transaction = new Transaction();
        $transaction->user_id = $receiver->id;
        $transaction->type_id = $fund_receive_id;
        $transaction->amount = $post['amount'];
        $transaction->save();

        return response()->json(["status" => "ok", "data" => [], "msg" => "Fund Successfully Sent"]);
    }

    public function aboutUs()
    {
        $data = Settings::get('about_us');
        return response()->json(["status" => "ok", "data" => $data, "msg" => "Data Successfully Found"]);
    }
}