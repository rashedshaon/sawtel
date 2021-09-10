<?php namespace Bol\Lpg\Api;

use Bol\Lpg\Api\ApiController;
use Bol\Lpg\Models\Region;
use Bol\Lpg\Models\Area;
use Bol\Lpg\Models\Territory;
use Bol\Lpg\Models\DistributionPoint;

class Locations extends ApiController
{
    public function getRegions()
    {
        $data = Region::orderBy('name', 'asc')->get()
                        ->map(function($data){
                            return [
                                "id" => $data->id,
                                "name" => $data->name,
                                "created_at" => $data->created_at->format('d-m-Y h:i:s A'),
                                "updated_at" => $data->updated_at->format('d-m-Y h:i:s A'),
                            ];
                        });

        return response()->json(["status" => "ok", "data" => $data, "msg" => count($data)." data found"]);
    }

    public function getAreas($region_id)
    {
        $data = Area::where('region_id', $region_id)->orderBy('name', 'asc')->get()
                        ->map(function($data){
                            return [
                                "id" => $data->id,
                                "name" => $data->name,
                                "region_id" => $data->region_id,
                                "region_name" => $data->region->name,
                                "created_at" => $data->created_at->format('d-m-Y h:i:s A'),
                                "updated_at" => $data->updated_at->format('d-m-Y h:i:s A'),
                            ];
                        });

        return response()->json(["status" => "ok", "data" => $data, "msg" => count($data)." data found"]);
    }

    public function getTerritories($area_id)
    {
        $data = Territory::where('area_id', $area_id)->orderBy('name', 'asc')->get()
                        ->map(function($data){
                            return [
                                "id" => $data->id,
                                "name" => $data->name,
                                "region_id" => $data->region_id,
                                "region_name" => $data->region->name,
                                "area_id" => $data->area_id,
                                "area_name" => $data->area->name,
                                "created_at" => $data->created_at->format('d-m-Y h:i:s A'),
                                "updated_at" => $data->updated_at->format('d-m-Y h:i:s A'),
                            ];
                        });

        return response()->json(["status" => "ok", "data" => $data, "msg" => count($data)." data found"]);
    }

    public function getPoints($territory_id)
    {
        $data = DistributionPoint::where('territory_id', $territory_id)->orderBy('name', 'asc')->get()
                        ->map(function($data){
                            return [
                                "id" => $data->id,
                                "name" => $data->name,
                                "lat_long" => $data->lat_long,
                                "region_id" => $data->region_id,
                                "region_name" => $data->region->name,
                                "area_id" => $data->area_id,
                                "area_name" => $data->area->name,
                                "territory_id" => $data->territory_id,
                                "territory_name" => $data->territory->name,
                                "created_at" => $data->created_at->format('d-m-Y h:i:s A'),
                                "updated_at" => $data->updated_at->format('d-m-Y h:i:s A'),
                            ];
                        });

        return response()->json(["status" => "ok", "data" => $data, "msg" => count($data)." data found"]);
    }
}