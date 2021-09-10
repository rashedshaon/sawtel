<?php namespace Bol\Lpg\Api;

use Bol\Lpg\Api\ApiController;
use Bol\Lpg\Models\Distributor;

use RLuders\JWTAuth\Classes\JWTAuth;

class Distributors extends ApiController
{
    public function getDistributors(JWTAuth $auth)
    {
        if (!$user = $auth->user()) {
            return response()->json(['status' => 'error', 'msg' => 'User not found']);
        }

        if (!$user->hasAccess('bol.lpg.manage_distributors')) {
            return response()->json(['status' => 'error', 'msg' => 'User has no access']);
        }

        $search_string = get('search_string');

        $distributor = Distributor::where('is_activated', 1)
                        ->where(function($query) use ($search_string) {
                            $query->where('name','LIKE','%'.$search_string.'%')
                                ->orWhere('code','LIKE','%'.$search_string.'%')
                                ->orWhere('business_name','LIKE','%'.$search_string.'%')
                                ->orWhere('phone','LIKE','%'.$search_string.'%');
                        })
                        ->orderBy('created_at', 'desc')
                        ->paginate();
        $data = $distributor->map(function($data){
            return [
                "id" => $data->id,
                "code" => $data->code,
                "name" => $data->name,
                "business_name" => $data->business_name,
                "phone" => $data->phone,
                "lat_long" => $data->lat_long,
                "address" => $data->address,
                "region_id" => $data->region_id,
                "region_name" => $data->region->name,
                "area_id" => $data->area_id,
                "area_name" => $data->area->name,
                "territory_id" => $data->territory_id,
                "territory_name" => $data->territory->name,
                "point_id" => $data->point_id,
                "point_name" => $data->point->name,
                "point_lat_long" => $data->point->lat_long,
                "is_activated" => $data->is_activated,
                "created_by" => $data->created_user->name,
                "created_at" => $data->created_at->format('d-m-Y h:i:s A'),
                "updated_at" => $data->updated_at->format('d-m-Y h:i:s A'),
            ];
        });

        return response()->json(["status" => "ok", "data" => $data, "msg" => count($data)." data found", 'pagination' => $this->pagination($distributor)]);
    }
}