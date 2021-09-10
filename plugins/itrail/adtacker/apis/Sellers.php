<?php namespace Bol\Lpg\Api;

use Validator;
use Bol\Lpg\Api\ApiController;
use Bol\Lpg\Models\Seller;

use RLuders\JWTAuth\Classes\JWTAuth;

class Sellers extends ApiController
{
    public function createSeller(JWTAuth $auth)
    {
        if (!$user = $auth->user()) {
            return response()->json(['status' => 'error', 'msg' => 'User not found']);
        }

        if (!$user->hasAccess('bol.lpg.manage_sellers')) {
            return response()->json(['status' => 'error', 'msg' => 'User has no access']);
        }

        $data = (object) post();
        
        $rules = [
            'name' => 'required',
            'phone' => 'required|regex:/^[0-1]{2}[0-9]{9}$/|unique:bol_lpg_sellers',
            'region_id' => 'required',
            'area_id' => 'required',
            'territory_id' => 'required',
            'point_id' => 'required',
            'distributor_id' => 'required',
        ];
  
        $validation = Validator::make(post(), $rules);
        if ($validation->fails()){ 
            return response()->json(["status" => "error", "msg" => $validation->messages()->all()]);
        }
  
        $seller = new Seller();

        $seller->name = $data->name;
        $seller->phone = $data->phone;
        $seller->lat_long = $data->lat_long;
        $seller->address = $data->address;
        $seller->type = $data->type;
        $seller->region_id = $data->region_id;
        $seller->area_id = $data->area_id;
        $seller->territory_id = $data->territory_id;
        $seller->point_id = $data->point_id;
        $seller->distributor_id = $data->distributor_id;
        $seller->save();

        return response()->json(["status" => "ok", "data" => [], "msg" => "Data successfully created."]);
    }

    public function activateDeactivateSeller(JWTAuth $auth)
    {
        if (!$user = $auth->user()) {
            return response()->json(['status' => 'error', 'msg' => 'User not found']);
        }

        if (!$user->hasAccess('bol.lpg.manage_sellers')) {
            return response()->json(['status' => 'error', 'msg' => 'User has no access']);
        }

        $data = (object) post();
        
        $rules = [
            'seller_id' => 'required',
        ];
  
        $validation = Validator::make(post(), $rules);
        if ($validation->fails()){ 
            return response()->json(["status" => "error", "msg" => $validation->messages()->all()]);
        }
  
        $seller = Seller::find($data->seller_id);
        $seller->is_activated = $seller->is_activated ? 0 : 1;
        $seller->save();

        return response()->json(["status" => "ok", "data" => [], "msg" => "Data successfully updated."]);
    }

    public function getSellerType()
    {
        $data = [
            ["id" => 0, "value" => "DR"],
            ["id" => 1, "value" => "SR"],
            ["id" => 2, "value" => "Non DSD"],
        ];
        return response()->json(["status" => "ok", "data" => $data, "msg" => "Data successfully fetched."]);
    }

    public function getSellers(JWTAuth $auth)
    {
        if (!$user = $auth->user()) {
            return response()->json(['status' => 'error', 'msg' => 'User not found']);
        }

        if (!$user->hasAccess('bol.lpg.manage_sellers')) {
            return response()->json(['status' => 'error', 'msg' => 'User has no access']);
        }

        $search_string = get('search_string');

        $seller = Seller::where(function($query) use ($search_string) {
                                    $query->where('name','LIKE','%'.$search_string.'%')
                                        ->orWhere('phone','LIKE','%'.$search_string.'%');
                                });

        if($user->role->code != 'admin' || $user->role->code != 'developer')
        {
            $seller = $seller->where('created_by', $user->id);
        }

        $seller = $seller->orderBy('created_at', 'desc')
                                                        ->paginate();

        $data = $seller->map(function($data){
            return [
                "id" => $data->id,
                "name" => $data->name,
                "phone" => $data->phone,
                "lat_long" => $data->lat_long,
                "address" => $data->address,
                "region_id" => $data->region_id,
                "region_name" => $data->region->name ?? '',
                "area_id" => $data->area_id,
                "area_name" => $data->area->name ?? '',
                "territory_id" => $data->territory_id,
                "territory_name" => $data->territory->name ?? '',
                "point_id" => $data->point_id,
                "point_name" => $data->point->name ?? '',
                "point_lat_long" => $data->point->lat_long ?? '',
                "activated_at" => $data->activated_at,
                "last_login" => $data->last_login ? $data->last_login->format('d-m-Y h:i:s A') : '',
                "created_at" => $data->created_at->format('d-m-Y h:i:s A'),
                "updated_at" => $data->updated_at->format('d-m-Y h:i:s A'),
                "created_by" => $data->created_user->name,
                "is_activated" => $data->is_activated,
            ];
        });

        return response()->json(["status" => "ok", "data" => $data, "msg" => count($data)." data found", 'pagination' => $this->pagination($seller)]);
    }
}