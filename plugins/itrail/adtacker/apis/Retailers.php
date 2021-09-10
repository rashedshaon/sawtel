<?php namespace Bol\Lpg\Api;

use Validator;
use Bol\Lpg\Api\ApiController;
use Bol\Lpg\Models\Retailer;

use RLuders\JWTAuth\Classes\JWTAuth;

class Retailers extends ApiController
{
    public function createRetailer(JWTAuth $auth)
    {
        if (!$user = $auth->user()) {
            return response()->json(['status' => 'error', 'msg' => 'User not found']);
        }

        if (!$user->hasAccess('bol.lpg.manage_retailers')) {
            return response()->json(['status' => 'error', 'msg' => 'User has no access']);
        }

        $data = (object) post();
        
        $rules = [
            'name' => 'required',
            'phone' => 'required|regex:/^[0-1]{2}[0-9]{9}$/|unique:backend_users',
            'area_id' => 'required',
            'territory_id' => 'required',
        ];
  
        $validation = Validator::make(post(), $rules);
        if ($validation->fails()){ 
            return response()->json(["status" => "error", "msg" => $validation->messages()->all()]);
        }
  
        $retailer = new Retailer();

        $retailer->name = $data->name;
        $retailer->business_name = $data->business_name;
        $retailer->phone = $data->phone;
        $retailer->lat_long = $data->lat_long;
        $retailer->address = $data->address;
        $retailer->area_id = $data->area_id;
        $retailer->territory_id = $data->territory_id;
        $retailer->save();

        return response()->json(["status" => "ok", "data" => [], "msg" => "Data successfully created."]);
    }

    public function activateDeactivateRetailer(JWTAuth $auth)
    {
        if (!$user = $auth->user()) {
            return response()->json(['status' => 'error', 'msg' => 'User not found']);
        }

        if (!$user->hasAccess('bol.lpg.manage_retailers')) {
            return response()->json(['status' => 'error', 'msg' => 'User has no access']);
        }

        $data = (object) post();
        
        $rules = [
            'retailer_id' => 'required',
        ];
  
        $validation = Validator::make(post(), $rules);
        if ($validation->fails()){ 
            return response()->json(["status" => "error", "msg" => $validation->messages()->all()]);
        }
  
        $retailer = Retailer::find($data->retailer_id);
        $retailer->is_activated = $retailer->is_activated ? 0 : 1;
        $retailer->save();

        return response()->json(["status" => "ok", "data" => [], "msg" => "Data successfully updated."]);
    }

    public function getRetailers(JWTAuth $auth)
    {
        if (!$user = $auth->user()) {
            return response()->json(['status' => 'error', 'msg' => 'User not found']);
        }

        if (!$user->hasAccess('bol.lpg.manage_retailers')) {
            return response()->json(['status' => 'error', 'msg' => 'User has no access']);
        }

        $search_string = get('search_string');

        $retailer = Retailer::where('role_id', self::RETAILER_ID)
                                ->where(function($query) use ($search_string) {
                                    $query->where('name','LIKE','%'.$search_string.'%')
                                        ->orWhere('business_name','LIKE','%'.$search_string.'%')
                                        ->orWhere('phone','LIKE','%'.$search_string.'%');
                                });
                                
        if($user->role->code != 'admin' || $user->role->code != 'developer')
        {
            $retailer = $retailer->where('created_by', $user->id);
        }

        $retailer = $retailer->orderBy('created_at', 'desc')
                            ->paginate();

        $data = $retailer->map(function($data){
            return [
                "id" => $data->id,
                "name" => $data->name,
                "business_name" => $data->business_name,
                "phone" => $data->phone,
                "lat_long" => $data->lat_long,
                "address" => $data->address,
                "area_id" => $data->area_id,
                "area_name" => $data->area->name ?? '',
                "area_lat_long" => $data->area->lat_long ?? '',
                "territory_id" => $data->territory_id,
                "territory_name" => $data->territory->name ?? '',
                "territory_lat_long" => $data->territory->lat_long ?? '',
                "activated_at" => $data->activated_at,
                "last_login" => $data->last_login ? $data->last_login->format('d-m-Y h:i:s A') : '',
                "created_at" => $data->created_at->format('d-m-Y h:i:s A'),
                "updated_at" => $data->updated_at->format('d-m-Y h:i:s A'),
                "created_by" => $data->created_user->name,
                "is_activated" => $data->is_activated,
            ];
        });

        return response()->json(["status" => "ok", "data" => $data, "msg" => count($data)." data found", 'pagination' => $this->pagination($retailer)]);
    }
}