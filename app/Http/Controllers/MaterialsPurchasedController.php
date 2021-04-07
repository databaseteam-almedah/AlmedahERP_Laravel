<?php

namespace App\Http\Controllers;

use App\Models\MaterialPurchased;
use App\Models\MaterialRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DB;
use Exception;
use Illuminate\Support\Facades\Validator;


class MaterialsPurchasedController extends Controller
{
    //
    public function index()
    {
        $materials_purchased = MaterialPurchased::all();
        return view('modules.buying.purchaseorder', ['materials_purchased' => $materials_purchased]);
    }

    public function openOrderForm()
    {
        $material_requests = MaterialRequest::all();
        return view('modules.buying.newpurchaseorder', ['material_requests' => $material_requests]);
    }

    public function view($index)
    {
        $purchase_order = MaterialPurchased::find($index);
        $material_requests = MaterialRequest::all();
        $items_purchased = $purchase_order->itemsPurchased();
        $req_date = $items_purchased[0]['req_date'];
        $supplier = $items_purchased[0]['supplier'];
        return view(
            'modules.buying.purchaseorderinfo',
            [
                'purchase_order' => $purchase_order,
                'material_requests' => $material_requests,
                'items_purchased' => $items_purchased,
                'req_date' => $req_date,
                'supplier' => $supplier
            ]
        );
    }

    //function getMaterials(Request $request) {
    //    try {
    //        $materialsByID = array();
    //        $ids = $request->input('req_ids');
    //        $request_ids = json_decode($ids);
    //        foreach($request_ids as $request_id) {
    //            $materialsByID[] = MaterialPurchased::where('request_id', $request_id)->get()->itemsPurchased();
    //        }
    //        return ['purchases' => $materialsByID];
    //    } catch (Exception $e) {
    //        return $e;
    //    }
    //}

    public function store(Request $request)
    {
        try {
            $data = new MaterialPurchased();

            $form_data = $request->input();

            $lastPurchase = MaterialPurchased::orderby('id', 'desc')->first();
            $nextId = ($lastPurchase) ? MaterialPurchased::orderby('id', 'desc')->first()->id + 1 : 1;
            //$nextId = MaterialPurchased::orderby('id', 'desc')->first()->id + $to_add;

            $to_append = 0;
            $digit_flag = 1;
            while ($nextId >= $digit_flag) {
                ++$to_append;
                $digit_flag *= 10;
            }

            $purchase_id = "PUR-ORD-" . Carbon::now()->year . '-' . str_pad($nextId, 5 - $to_append, '0', STR_PAD_LEFT);

            $data->purchase_id = $purchase_id;

            //Commented for now; needs supplier quotation module for it to fully work.
            //$data->supp_quotation_id =

            $data->items_list_purchased = json_encode($form_data['materials_purchased']);
            $data->purchase_date = $form_data['purchase_date'];
            $data->total_cost = $form_data['total_price'];

            $data->save();
        } catch (Exception $e) {
            return $e;
        }
    }

    public function update(Request $request)
    {
        try {
            $form_data = $request->input();

            $data = MaterialPurchased::where('purchase_id', $form_data['purchase_id'])->first();

            $data->items_list_purchased = json_encode($form_data['materials_purchased']);
            $data->purchase_date = $form_data['purchase_date'];
            $data->total_cost = $form_data['total_price'];

            $data->save();

        } catch (Exception $e) {
            return $e;
        }
    }

    public function updateStatus($purchase_id)
    {
        try {
            $data = MaterialPurchased::where('purchase_id', $purchase_id)->first();
            $data->mp_status = "To Receive and Bill";
            $data->save();
        } catch (Exception $e) {
        }
    }
}
