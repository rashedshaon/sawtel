<?php namespace Bol\Lpg\Api;

use Bol\Lpg\Api\ApiController;
use Bol\Lpg\Models\Product;

class Products extends ApiController
{
    public function getProducts()
    {
        $data = Product::orderBy('name', 'asc')->get()
                        ->map(function($data){
                            return [
                                "id" => $data->id,
                                "name" => $data->name,
                                "size" => $data->size,
                                "photo" => $data->getPhoto(),
                                "quantity" => 0,
                                "isSelect" => 0,
                                "created_at" => $data->created_at->format('d-m-Y h:i:s A'),
                                "updated_at" => $data->updated_at->format('d-m-Y h:i:s A'),
                            ];
                        });

        return response()->json(["status" => "ok", "data" => $data, "msg" => count($data)." data found"]);
    }
}