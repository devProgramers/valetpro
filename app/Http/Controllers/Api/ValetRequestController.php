<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PoolTip;
use App\Models\User;
use App\Models\Valet;
use App\Models\ValetManagerLocation;
use App\Models\ValetRequest;
use App\Models\VehicleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Events\ValetStatus;

class ValetRequestController extends Controller
{
    public function locations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'longitude'=>['required'],
            'latitude'=>['required']
        ]);

        if ($validator->fails()){
            return Response::json([
                'success' => false,
                'msg'=> $validator->messages(),
            ], 301);
        }
        $longitude = $request->longitude;
        $latitude = $request->latitude;
        $radius = 500;
        $locations = ValetManagerLocation::where(\DB::raw("
            IFNULL( ( 3959 * acos ( cos ( radians(" . $latitude . ") )
            * cos( radians( valet_manager_locations.latitude ) ) * cos( radians( valet_manager_locations.longitude )
            - radians(" . $longitude . ") )
            + sin ( radians(" . $latitude . ") )
            * sin( radians( latitude ) ) ) )
            * 1.609344 ,0)
            "),"<=",$radius)->get();

        if (sizeof($locations)){
            return Response::json([
                'success' => true,
                'locations'=> $locations,
            ], 200);
        }else{
            return Response::json([
                'success' => false,
                'msg'=> 'No nearby valets found',
            ], 302);
        }
    }

    public function requestValet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'longitude'=>['required'],
            'latitude'=>['required'],
            'location_id'=>['required','min:1'],
            'number_plate'=>['required']
        ]);

        if ($validator->fails()){
            return Response::json([
                'success' => false,
                'msg'=> $validator->messages(),
            ], 301);
        }
        $valet_request = new ValetRequest;
        $valet_request->ticket_number = rand(100000000,999999999);
        $valet_request->customer_id = Auth::user()->id;
        $valet_request->longitude = $request->longitude;
        $valet_request->latitude = $request->latitude;
        $valet_request->location_id = $request->location_id;
        $valet_request->number_plate = $request->number_plate;
        $valet_request->save();
        if ($valet_request){
            $id = ValetManagerLocation::find($request->location_id)->valet_manager_id;
            $message = 'A new valet request.Ticket Number:'.$valet_request->ticket_number;
            sendNotification($id,$message);
            return Response::json([
                'success' => true,
                'request'=> $valet_request,
            ], 200);
        }else{
            return Response::json([
                'success' => false,
                'msg'=> 'Something went wrong! Please try again',
            ], 302);
        }
    }

    public function getValetsList(){
        $manager = Auth::user();
        $locations = ValetManagerLocation::where('valet_manager_id',$manager->id)->get()->pluck('id');
        $requestCount = ValetRequest::whereIn('location_id',$locations)->where('status',0)->count();
        $valets = Valet::where('valet_manager_id',$manager->id)->with('user')->get();
        $tips = PoolTip::where('valet_manager_id',$manager->id)->whereDay('created_at', now()->day)->sum('amount');
        return Response::json([
            'success' => true,
            'requestCount' => $requestCount,
            'tips' => $tips,
            'valets'=> $valets,
        ], 200);
    }

    public function assignValet(Request $request,$id){
        $valet_request =ValetRequest::find($id);
        $valet_request->valet_id =$request->valet_id;
        $valet_request->status = 1;
        $valet_request->save();
        $uid = $request->valet_id;
        $message = 'A new request is been assigned to you.Ticket number:'.$valet_request->ticket_number;
        sendNotification($uid,$message);
        $uid = $valet_request->customer_id;
        $user = User::find($request->valet_id);
        $message = 'A Valet '.$user->name.' is assigned to your request.Ticket number:'.$valet_request->ticket_number;
        sendNotification($uid,$message);
        return Response::json([
            'success' => true,
            'msg'=> 'Valet assigned successfully',
        ], 200);
    }

    public function assingedRequests(){
        $user = Auth::user();
        $assingedRequests = ValetRequest::where(['status'=>1,'valet_id'=>$user->id])->get();

        return Response::json([
            'success' => true,
            'assingedRequests'=> $assingedRequests,
        ], 200);
    }

    public function respondRequest($status,$id){
        $valet_request = ValetRequest::find($id);
        $valet_request->status = $status;
        $valet_request->save();
        if ($status == 2){
            $uid = $valet_request->customer_id;
            $message = 'Valet has accepted your request for ticket:'.$valet_request->ticket_number.' and is on his way.';
            sendNotification($uid,$message);
            $uid = ValetManagerLocation::where('valet_manager_id',$valet_request->location_id)->first()->valet_manager_id;
            $message = 'Valet has accepted the request for ticket:'.$valet_request->ticket_number.' and is on his way.';
            sendNotification($uid,$message);
        }elseif($status == 4){
            $uid = $valet_request->customer_id;
            $message = 'Valet has canceled your request for ticket:'.$valet_request->ticket_number.' wait a while manager will assign some new valet soon.';
            sendNotification($uid,$message);
            $uid = ValetManagerLocation::where('valet_manager_id',$valet_request->location_id)->first()->valet_manager_id;
            $message = 'Valet has cancled the request for ticket:'.$valet_request->ticket_number.'.Please assign some new valet';
            sendNotification($uid,$message);
        }elseif($status == 5){
            $uid = $valet_request->valet_id ;
            $message = 'Customer has canceled your request for ticket:'.$valet_request->ticket_number;
            sendNotification($uid,$message);
            $uid = ValetManagerLocation::where('valet_manager_id',$valet_request->location_id)->first()->valet_manager_id;
            $message = 'Customer has canceled the request for ticket:'.$valet_request->ticket_number;
            sendNotification($uid,$message);
        }
        return Response::json([
            'success' => true,
            'msg'=> 'Request responded successfully',
        ], 200);
    }

    public function completeRequest($id){
        $user = Auth::user();
        $assingedRequest = ValetRequest::find($id);
        $assingedRequest->status = 4;
        $assingedRequest->save();
        $uid = $assingedRequest->customer_id;
        $message = 'Valet has completed your request for ticket:'.$assingedRequest->ticket_number;
        sendNotification($uid,$message);
        $uid = ValetManagerLocation::where('valet_manager_id',$assingedRequest->location_id)->first()->valet_manager_id;
        $message = 'Valet has completed the request for ticket:'.$assingedRequest->ticket_number;
        sendNotification($uid,$message);
        return Response::json([
            'success' => true,
            'msg'=> 'Request completed',
        ], 200);
    }
    public function requestList(){
        $user = Auth::user();
        $locations = ValetManagerLocation::where('valet_manager_id',$user->id)->pluck('id');
        $pendingValets = ValetRequest::where('status',0)->whereIn('location_id',$locations)->get();
        $pendings = ValetRequest::where('status',3)->whereIn('location_id',$locations)->pluck('id');
        $pendingVehicles = VehicleRequest::where('status',0)->whereIn('valet_request_id',$pendings)->with('valetRequest')->get();
        $settledRequests = VehicleRequest::where('status',5)->whereIn('valet_request_id',$pendings)->with('valetRequest')->get();
        return Response::json([
            'success' => true,
            'pendingRequests'=>$pendingValets,
            'pendingVehicles'=>$pendingVehicles,
            'settledRequests' => $settledRequests
        ], 200);
    }
    public function singleRequest($id){
        $manager = Auth::user();
        $valetRequest = ValetRequest::where('id',$id)->with('location')->first();
        $valets = Valet::where('valet_manager_id',$manager->id)->with('user')->get();
        return Response::json([
            'success' => true,
            'request'=>$valetRequest,
            'valets'=>$valets
        ], 200);
    }
    public function valetStatus($id){
        $vrequest = ValetRequest::find($id);
        $data = new ValetStatus($vrequest);
        broadcast($data);
        return Response::json([
            'success' => true,
            'status'=> $data->broadcastWith(),
        ], 200);
    }
}
