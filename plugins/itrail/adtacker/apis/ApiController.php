<?php namespace ItRail\AdTacker\Apis;

use Backend\Classes\Controller;


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
    const DISTRIBUTOR_ID = 4;
    const RETAILER_ID = 5;
    const SELLER_ID = 6;

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
        $user = [];
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
                                "photo" => $data->getPhoto(),
                                "created_at" => $data->created_at->format('d-m-Y h:i:s A'),
                                "updated_at" => $data->updated_at->format('d-m-Y h:i:s A'),
                            ];
                        }),
            "products" => Product::isPublished()->isFeatured()->orderBy('update_at', 'desc')->get()
                            ->map(function($data){
                                return [
                                    "id" => $data->id,
                                    "name" => $data->name,
                                    "photo" => $data->getPhoto(),
                                    "price" => $data->price,
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
        $products =  Product::isPublished()->isFeatured()->orderBy('created_at', 'desc')->paginate();
        $data = $products->map(function($data){
                                return [
                                    "id" => $data->id,
                                    "name" => $data->name,
                                    "photo" => $data->getPhoto(),
                                    "price" => $data->price,
                                    "description" => $data->description,
                                    "created_at" => $data->created_at->format('d-m-Y h:i:s A'),
                                    "updated_at" => $data->updated_at->format('d-m-Y h:i:s A'),
                                ];
                            });

        return response()->json(["status" => "ok", "data" => $data, "msg" => count($data)." data found", 'pagination' => $this->pagination($products)]);
    }

    public function todaysIncome()
    {
        $user = [];
        $type_id = Settings::get('daily_income_status');
        $transaction = Transaction::where('user_id', $user->id)->where('type_id', $type_id)->whereDate('created_at', Carbon::today())->get()->first();
        if($transaction)
        {
            return response()->json(["status" => "ok", "data" => $transaction->amount, "msg" => "Successfully Collected Today"]);
        }
        
        return response()->json(["status" => "error", "data" => $transaction->amount, "msg" => "Still not collected"]);
    }

    public function submitIncome()
    {
        $user = [];
        $type_id = Settings::get('daily_income_status');
        $amount = Settings::get('daily_income_point');
        

        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->type_id = $type_id;
        $transaction->amount  = $amount;
        $transaction->save();
        
        return response()->json(["status" => "ok", "data" => $transaction->amount, "msg" => "Successfully Collected Today"]);
    }

    public function getBanks()
    {
        $data = Bank::orderBy('name', 'asc')->get();

        return response()->json(["status" => "ok", "data" => $data, "msg" => "Successfully Found Data"]);
    }

    public function withdrawRequests()
    {
        $user = [];
        $withdraw_requests =  WithdrawRequest::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate();
        $data = $products->map(function($data){
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
        $user = [];

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

        if($current_balance >= $minimum_balance)
        {
            $withdraw_requests = new WithdrawRequest();
            $withdraw_requests->user_id = $user->id;
            $withdraw_requests->bank_id = $post['bank_id'];
            $withdraw_requests->account_no = $post['account_no'];
            $withdraw_requests->amount = $post['amount'];
            $withdraw_requests->save();

            return response()->json(["status" => "ok", "data" => [], "msg" => "Request Successfully Submitted"]);
        }

        return response()->json(["status" => "error", "data" => [], "msg" => "Not enough money to withdraw."]);
    }

    public function submitOrder()
    {
        $user = [];
        
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
        
    }

    public function transactionSummery()
    {
        
    }

    public function transactionDetails()
    {
        
    }
    public function accounts()
    {
        
    }

    public function updateProfile()
    {
        
    }

    public function updatePhoto()
    {
        
    }

    public function sendFund()
    {
        
    }

    public function aboutUs()
    {
        
    }
}