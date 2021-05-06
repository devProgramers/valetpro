<?php

namespace App\Http\Controllers\Api;

use App\Events\VehicleStatus;
use App\Http\Controllers\Controller;
use App\Models\ValetManagerLocation;
use App\Models\ValetRequest;
use App\Models\VehicleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class VehicleRequestController extends Controller
{
    public function getTicket()
    {
        $user = Auth::user();
        $ticket = ValetRequest::where(['customer_id'=>$user->id,'status'=>3])->latest()->first();
        if (isset($ticket)){
            $tickets = VehicleRequest::where('valet_request_id',$ticket->id)->first();
            if (!isset($tickets)) {
                return Response::json([
                    'success' => true,
                    'request_details' => $ticket
                ], 200);
            }else{
                return Response::json([
                    'success' => false,
                    'msg'=> 'Request already assigned',
                ], 302);
            }
        }else{
            return Response::json([
                'success' => false,
                'msg'=> 'No request initiated yet',
            ], 302);
        }
    }

    public function requestVehicle(Request $request){
        $validator = Validator::make($request->all(), [
            'longitude'=>['required'],
            'latitude'=>['required'],
            'valet_request_id'=>['required','min:1'],
            'ready_at'=>['required']
        ]);

        if ($validator->fails()){
            return Response::json([
                'success' => false,
                'msg'=> $validator->messages(),
            ], 301);
        }

        $vehicle_request = new VehicleRequest;
        $vehicle_request->valet_request_id = $request->valet_request_id;
        $vehicle_request->longitude = $request->longitude;
        $vehicle_request->latitude = $request->latitude;
        $vehicle_request->ready_at = $request->ready_at;
        $vehicle_request->save();
        $vr = ValetRequest::find($request->valet_request_id);
        $id = $vr->valet_id;
        $msg = 'You have been requested for Vehicle. Ticket number:'.$vr->ticket_number.'.Car number'.$vr->number_plate;
        sendNotification($id,$msg);

        return Response::json([
            'success' => true,
            'msg' => 'Vehicle requested successfully'
        ], 200);
    }

    public function respondRequest($status,$id){
        $vehicle_request =  VehicleRequest::find($id);
        $vehicle_request->status = $status;
        $vehicle_request->save();
        $valet_request = ValetRequest::where('id',$vehicle_request->valet_request_id)->first();
        if ($status == 1){
            $uid = $valet_request->customer_id;
            $message = 'Valet has accepted your request for ticket:'.$valet_request->ticket_number.' and is on his way.';
            sendNotification($uid,$message);
            $uid = ValetManagerLocation::where('valet_manager_id',$valet_request->location_id)->first()->valet_manager_id;
            $message = 'Valet has accepted the vehicle request for ticket:'.$valet_request->ticket_number.' and is on his way.';
            sendNotification($uid,$message);
            $msg ='Request accepted successfully';
        }elseif ($status == 2){
            $uid = $valet_request->customer_id;
            $message = 'Valet has arrived to requested location for ticket:'.$valet_request->ticket_number;
            sendNotification($uid,$message);
            $uid = ValetManagerLocation::where('valet_manager_id',$valet_request->location_id)->first()->valet_manager_id;
            $message = 'Valet has arrived to requested location for ticket:'.$valet_request->ticket_number;
            sendNotification($uid,$message);
            $msg ='Arrived successfully';
        }elseif($status == 3){
            $uid = $valet_request->customer_id;
            $message = 'Valet has completed your request for ticket:'.$valet_request->ticket_number;
            sendNotification($uid,$message);
            $uid = ValetManagerLocation::where('valet_manager_id',$valet_request->location_id)->first()->valet_manager_id;
            $message = 'Valet has completed the request for ticket:'.$valet_request->ticket_number;
            sendNotification($uid,$message);
            $msg ='completed successfully';
            $valet_request->status = 5;
            $valet_request->save();
        }elseif($status == 4){
            $uid = $valet_request->customer_id;
            $message = 'Valet has canceled your request for ticket:'.$valet_request->ticket_number.' wait a while manager will look into it and assign some new valet soon.';
            sendNotification($uid,$message);
            $uid = ValetManagerLocation::where('valet_manager_id',$valet_request->location_id)->first()->valet_manager_id;
            $message = 'Valet has cancled the request for ticket:'.$valet_request->ticket_number.'.Please assign some new valet';
            sendNotification($uid,$message);
            $msg ='Canceled successfully';
        }elseif($status == 4){
            $uid = $valet_request->customer_id;
            $message = 'Valet has canceled your request for ticket:'.$valet_request->ticket_number.' wait a while manager will look into it and assign some new valet soon.';
            sendNotification($uid,$message);
            $uid = ValetManagerLocation::where('valet_manager_id',$valet_request->location_id)->first()->valet_manager_id;
            $message = 'Valet has cancled the request for ticket:'.$valet_request->ticket_number.'.Please assign some new valet';
            sendNotification($uid,$message);
            $msg ='Canceled successfully';
        }elseif($status == 5){
            $uid = $valet_request->customer_id;
            $message = 'Valet has canceled your request for ticket:'.$valet_request->ticket_number.' wait a while manager will look into it and assign some new valet soon.';
            sendNotification($uid,$message);
            $uid = ValetManagerLocation::where('valet_manager_id',$valet_request->location_id)->first()->valet_manager_id;
            $message = 'Valet has cancled the request for ticket:'.$valet_request->ticket_number.'.Please assign some new valet';
            sendNotification($uid,$message);
            $msg ='Canceled successfully';
        }elseif($status == 6){
            $uid = $valet_request->customer_id;
            $message = 'Valet has canceled your request for ticket:'.$valet_request->ticket_number.' wait a while manager will look into it and assign some new valet soon.';
            sendNotification($uid,$message);
            $uid = ValetManagerLocation::where('valet_manager_id',$valet_request->location_id)->first()->valet_manager_id;
            $message = 'Valet has cancled the request for ticket:'.$valet_request->ticket_number.'.Please assign some new valet';
            sendNotification($uid,$message);
            $msg ='Canceled successfully';
        }
        return Response::json([
            'success' => true,
            'msg'=> $msg,
        ], 200);
    }

    public function vehicleStatus($id){
        $vrequest = VehicleRequest::find($id);
        $data = new VehicleStatus($vrequest);
        broadcast($data);
        return Response::json([
            'success' => true,
            'status'=> $data->broadcastWith(),
        ], 200);
    }
}
