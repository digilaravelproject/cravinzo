<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\Zone;
use App\Models\Incentive;
use App\Models\RestaurantFlatFee;
use App\Exports\ZoneExport;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\LineString;

class ZoneController extends Controller
{
    public function index(Request $request)
    {
        $key = explode(' ', $request['search'] ?? null );
        $zones = Zone::withCount(['restaurants','deliverymen'])
        ->when(isset($key), function($query)use($key){
            $query->where( function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
            });
        })
        ->latest()->paginate(config('default_pagination'));
        return view('admin-views.zone.index', compact('zones'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:zones|max:191',
            'display_name' => 'nullable|unique:zones|max:255',
            'coordinates' => 'required',
        ]);

        if($request->name[array_search('default', $request->lang)] == '' ){
            $validator->getMessageBag()->add('title', translate('messages.default_Business_zone_name_is_required'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
            }

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }
        $value = $request->coordinates;
        foreach(explode('),(',trim($value,'()')) as $index=>$single_array){
            if($index == 0)
            {
                $lastcord = explode(',',$single_array);
            }
            $coords = explode(',',$single_array);
            $polygon[] = new Point($coords[0], $coords[1]);
        }
        $zone_id=Zone::all()->count() + 1;
        $polygon[] = new Point($lastcord[0], $lastcord[1]);
        $zone = new Zone();
        $zone->name = $request->name[array_search('default', $request->lang)];
        $zone->display_name = $request->display_name[array_search('default', $request->lang)];
        $zone->coordinates = new Polygon([new LineString($polygon)]);
        $zone->restaurant_wise_topic =  'zone_'.$zone_id.'_restaurant';
        $zone->customer_wise_topic = 'zone_'.$zone_id.'_customer';
        $zone->deliveryman_wise_topic = 'zone_'.$zone_id.'_delivery_man';
        $zone->per_km_shipping_charge = $request->per_km_delivery_charge ?? 0;
        $zone->minimum_shipping_charge = $request->minimum_delivery_charge ?? 0;
        $zone->maximum_shipping_charge = $request->maximum_shipping_charge ??null ;
        $zone->max_cod_order_amount = $request->max_cod_order_amount ?? null;
        $zone->Restaurant_perKm_Rate = $request->Restaurant_perKm_Rate ?? null;
        $zone->Cust_Per_Km_Charge = $request->Cust_Per_Km_Charge ?? null;
        $zone->save();

        Helpers::add_or_update_translations(request: $request, key_data:'name' , name_field:'name' , model_name: 'Zone' ,data_id: $zone->id,data_value: $zone->name);
        Helpers::add_or_update_translations(request: $request, key_data:'display_name' , name_field:'display_name' , model_name: 'Zone' ,data_id: $zone->id,data_value: $zone->display_name);

        $new_data=1;
        $zones = Zone::withCount(['restaurants','deliverymen'])->latest()->paginate(config('default_pagination'));
        return response()->json([
            'view'=>view('admin-views.zone.partials._table',compact('zones','new_data'))->render(),
            'total'=>$zones->count()
        ]);
    }

    public function edit($id)
    {
        if(env('APP_MODE')=='demo' && $id == 1)
        {
            Toastr::warning(translate('messages.you_can_not_edit_this_zone_please_add_a_new_zone_to_edit'));
            return back();
        }
        $zone=Zone::selectRaw("*,ST_AsText(ST_Centroid(`coordinates`)) as center")->withoutGlobalScope('translate')->with('translations')->findOrFail($id);
        $area = json_decode($zone->coordinates[0]->toJson(),true);
        return view('admin-views.zone.edit', compact(['zone','area']));
    }
    public function latest_zone_settings()
    {
        if(env('APP_MODE')=='demo')
        {
            Toastr::warning(translate('messages.you_can_not_edit_this_zone_please_add_a_new_zone_to_edit'));
            return back();
        }
        $zone=Zone::with(['incentives','restaurantFlatFees'])->selectRaw("*,ST_AsText(ST_Centroid(`coordinates`)) as center")->latest()->first();
        $base_payout = $zone->restaurantFlatFees->first()?->base_payout ?? null;
        return view('admin-views.zone.settings', compact('zone', 'base_payout'));
    }
    public function zone_settings($id)
    {
        if(env('APP_MODE')=='demo' && $id == 1)
        {
            Toastr::warning(translate('messages.you_can_not_edit_this_zone_please_add_a_new_zone_to_edit'));
            return back();
        }
        $zone=Zone::with(['incentives','restaurantFlatFees'])->selectRaw("*,ST_AsText(ST_Centroid(`coordinates`)) as center")->findOrFail($id);
        $base_payout = $zone->restaurantFlatFees->first()?->base_payout ?? null;
        return view('admin-views.zone.settings', compact('zone', 'base_payout'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|max:191|unique:zones,name,'.$id,
            'display_name' => 'nullable|max:255|unique:zones,display_name,'.$id,
            'coordinates' => 'required',
        ]);

        if($request->name[array_search('default', $request->lang)] == '' ){
                    Toastr::error(translate('default_Business_zone_name_is_required'));
            return back();

                }

        $value = $request->coordinates;
        foreach(explode('),(',trim($value,'()')) as $index=>$single_array){
            if($index == 0)
            {
                $lastcord = explode(',',$single_array);
            }
            $coords = explode(',',$single_array);
            $polygon[] = new Point((float)$coords[0], (float)$coords[1]);
        }
        $polygon[] = new Point((float)$lastcord[0], (float)$lastcord[1]);
        $zone=Zone::findOrFail($id);
        $zone->name = $request->name[array_search('default', $request->lang)];
        $zone->display_name = $request->display_name[array_search('default', $request->lang)];
        $zone->restaurant_wise_topic =  'zone_'.$id.'_restaurant';
        $zone->customer_wise_topic = 'zone_'.$id.'_customer';
        $zone->deliveryman_wise_topic = 'zone_'.$id.'_delivery_man';
        $zone->save();

        Helpers::add_or_update_translations(request: $request, key_data:'name' , name_field:'name' , model_name: 'Zone' ,data_id: $zone->id,data_value: $zone->name);
        Helpers::add_or_update_translations(request: $request, key_data:'display_name' , name_field:'display_name' , model_name: 'Zone' ,data_id: $zone->id,data_value: $zone->display_name);


        try {
            $zone->coordinates = new Polygon([new LineString($polygon)]);
            $zone->save();
        } catch (\Exception $exception) {

        }

        Toastr::success(translate('messages.zone_updated_successfully'));
        return redirect()->route('admin.zone.home');
    }

    public function zone_settings_update(Request $request, $id){
        $request->validate([
            'per_km_delivery_charge'=>'required|numeric|between:0.001,999999999999.99',
            'minimum_delivery_charge'=>'required|numeric|between:0.001,999999999999.99',
            'maximum_shipping_charge' => 'nullable|numeric|between:0,999999999999.99|gt:minimum_delivery_charge',
            'max_cod_order_amount' => 'nullable|numeric|between:0,999999999999.99',
            'increased_delivery_fee' => 'nullable|numeric|between:0,999999999.99|required_if:increased_delivery_fee_status,1',
            'Restaurant_perKm_Rate' => 'nullable|numeric|between:0,999999999999.99',
            'Cust_Per_Km_Charge' => 'nullable|numeric|between:0,999999999999.99',
            ], [
                'increased_delivery_fee.required_if' => translate('messages.increased_delivery_fee_is_required')
            ]);


        $zone=Zone::findOrFail($id);
        $zone->restaurant_wise_topic =  'zone_'.$id.'_restaurant';
        $zone->customer_wise_topic = 'zone_'.$id.'_customer';
        $zone->deliveryman_wise_topic = 'zone_'.$id.'_delivery_man';
        $zone->per_km_shipping_charge = $request->per_km_delivery_charge;
        $zone->minimum_shipping_charge = $request->minimum_delivery_charge;
        $zone->maximum_shipping_charge = $request->maximum_shipping_charge ?? null;
        $zone->max_cod_order_amount = $request->max_cod_order_amount ?? null;
        $zone->increased_delivery_fee = $request->increased_delivery_fee ?? 0;
        $zone->increased_delivery_fee_status = $request->increased_delivery_fee_status ?? 0;
        $zone->increase_delivery_charge_message = $request->increase_delivery_charge_message ?? null;
        $zone->Restaurant_perKm_Rate = $request->Restaurant_perKm_Rate ?? null;
        $zone->Cust_Per_Km_Charge = $request->Cust_Per_Km_Charge ?? null;
        $zone->save();
        // Handle restaurant flat fees
        // Expect arrays: restaurant_flat_fee_from[], restaurant_flat_fee_to[], restaurant_flat_fee[]
        if($request->has('restaurant_flat_fee')){
            // Get existing base_payout before deleting
            $existing_base_payout = RestaurantFlatFee::where('zone_id', $id)->first()?->base_payout;
            
            // clear existing for the zone
            RestaurantFlatFee::where('zone_id', $id)->delete();
            $froms = $request->input('restaurant_flat_fee_from', []);
            $tos = $request->input('restaurant_flat_fee_to', []);
            $fees = $request->input('restaurant_flat_fee', []);
            $count = max(count($froms), count($tos), count($fees));
            for($i=0;$i<$count;$i++){
                $from = isset($froms[$i]) && $froms[$i] !== '' ? $froms[$i] : null;
                $to = isset($tos[$i]) && $tos[$i] !== '' ? $tos[$i] : null;
                $fee = isset($fees[$i]) && $fees[$i] !== '' ? $fees[$i] : null;
                if($from === null && $to === null && $fee === null) continue;
                RestaurantFlatFee::create([
                    'zone_id' => $id,
                    'flat_fee_from' => $from,
                    'flat_fee_to' => $to,
                    'flat_fee' => $fee,
                    'base_payout' => $existing_base_payout,
                ]);
            }
        }
        Toastr::success(translate('messages.zone_settings_updated_successfully'));
        return back();
    }

    public function update_base_payout(Request $request, $id){
        $request->validate([
            'base_payout'=>'required|numeric|between:0.001,999999999999.99',
        ]);

        $zone = Zone::findOrFail($id);
        
        // Update all restaurant flat fee records for this zone with the base payout
        RestaurantFlatFee::where('zone_id', $id)->update([
            'base_payout' => $request->base_payout
        ]);
        
        Toastr::success(translate('messages.base_payout_updated_successfully'));
        return back();
    }

    public function destroy(Zone $zone)
    {
        if(env('APP_MODE')=='demo' && $zone->id == 1)
        {
            Toastr::warning(translate('messages.you_can_not_delete_this_zone_please_add_a_new_zone_to_delete'));
            return back();
        }
        $zone->delete();
        Toastr::success(translate('messages.zone_deleted_successfully'));
        return back();
    }

    public function status(Request $request)
    {
        if(env('APP_MODE')=='demo' && $request->id == 1)
        {
            Toastr::warning('Sorry!You can not inactive this zone!');
            return back();
        }
        $zone = Zone::findOrFail($request->id);
        if($zone->is_default && $request->status == 0){
            Toastr::warning('Sorry! This zone is set as default.You can not inactive this zone!');
            return back();
        }
        $zone->status = $request->status;
        $zone->save();
        Toastr::success(translate('messages.zone_status_updated'));
        return back();
    }

    public function defaultStatus(Request $request){
        $zone = Zone::findOrFail($request->id);
        $zone->is_default = 1;
        $zone->status = 1;
        $zone->save();
        Zone::where('id', '!=', $request->id)->update(['is_default' => 0]);
        Toastr::success(translate('messages.zone_default_status_updated'));
        return back();
    }

    public function get_coordinates($id){
        $zone=Zone::withoutGlobalScopes()->selectRaw("*,ST_AsText(ST_Centroid(`coordinates`)) as center")->findOrFail($id);
        $area = json_decode($zone->coordinates[0]->toJson(),true);
        $data = Helpers::format_coordiantes($area['coordinates']);
        $center = (object)['lat'=>(float)trim(explode(' ',$zone->center)[1], 'POINT()'), 'lng'=>(float)trim(explode(' ',$zone->center)[0], 'POINT()')];
        return response()->json(['coordinates'=>$data, 'center'=>$center]);
    }

    public function zone_filter($id)
    {
        if($id == 'all')
        {
            if(session()->has('zone_id')){
                session()->forget('zone_id');
            }
        }
        else{
            session()->put('zone_id', $id);
        }

        return back();
    }

    public function get_all_zone_cordinates($id = 0)
    {
        $zones = Zone::where('id', '<>', $id)->active()->get();
        $data = [];
        foreach($zones as $zone)
        {
            $area = json_decode($zone->coordinates[0]->toJson(),true);
            $data[] = Helpers::format_coordiantes($area['coordinates']);
        }
        return response()->json($data,200);
    }

    public function export_zones(Request $request, $type){

        $key = explode(' ', $request['search']);
        $collection = Zone::withCount(['restaurants','deliverymen'])
        ->when(isset($key) , function($q) use($key){
            $q->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "%{$value}%");
                }
            });
        })
        ->get();
        $data=[
            'data' =>$collection,
            'search' =>$request['search'] ?? null,
        ];
        if($type == 'csv'){
            return Excel::download(new ZoneExport($data), 'Zone.csv');
        }
        return Excel::download(new ZoneExport($data), 'Zone.xlsx');
    }

    public function store_incentive(Request $request, $zone_id)
    {
        $request->validate([
            'earning' => [
                'required',
                'numeric',
                'between:1,999999999999.99',
                Rule::unique('incentives', 'earning')->where('zone_id', $zone_id)
            ],
            'incentive' => 'required|numeric|between:1,999999999999.99'
        ],[
            'earning.unique' => translate('This_earning_amount_already_exists')
        ]);

        $incentive = new Incentive();
        $incentive->earning = $request->earning;
        $incentive->incentive = $request->incentive;
        $incentive->zone_id = $zone_id;
        $incentive->save();
        Toastr::success(translate('messages.incentive_inserted_successfully'));
        return back();
    }

    public function destroy_incentive(Request $request, $id)
    {
        $incentive = Incentive::findOrFail($id);
        $incentive?->delete();
        Toastr::success(translate('messages.incentive_deleted_successfully'));
        return back();
    }
}
