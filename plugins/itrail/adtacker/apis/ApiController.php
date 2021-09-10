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
}