<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DirectTip;
use App\Models\PoolTip;
use App\Models\Review;
use App\Models\User;
use App\Models\Valet;
use App\Models\ValetManager;
use App\Models\ValetManagerLocation;
use App\Models\ValetRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class TipsController extends Controller
{
    public function setTipType(Request $request)
    {
        $user = Auth::user();
        $valet_manager = ValetManager::where('user_id',$user->id)->first();
        $valet_manager->tips = $request->tip;
        $valet_manager->save();
        if ($request->tip){
            $msg = 'Tips set to pool.';
        }else{
            $msg = 'Tips set to individual.';
        }
        return Response::json([
            'success' => true,
            'msg'=> $msg
        ], 200);

    }

    public function tip(Request $request)
    {
        $customer = Auth::user();
        $valet_request = ValetRequest::find($request->valet_request_id);
        $valet = User::find($valet_request->valet_id);
        $location = ValetManagerLocation::find($valet_request->location_id);
        $valet_manager = ValetManager::where('user_id',$location->valet_manager_id)->first();
        if ($valet_manager->tips){
            $pool = new PoolTip;
            $pool->valet_manager_id = $valet_manager->id;
            $pool->location_id = $location->id;
            $pool->request_id = $valet_request->id;
            $pool->valet_id = $valet_request->valet_id;
            $pool->amount = $request->amount;
            $pool->save();
        }else{
            $tip = new DirectTip;
            $tip->valet_id = $valet->id;
            $tip->request_id = $valet_request->id;
            $tip->amount = $request->amount;
            $tip->save();
        }
        $this->addReview($valet->id,$customer->id,$valet_request->id,$request->rating,$request->comment);
        return Response::json([
            'success' => true,
            'msg'=>'Tip Send. Thank you!',
        ], 200);

    }

    private function addReview($valet_id, $customer_id, $valet_request_id, $rating, $comment)
    {
        $review = new Review;
        $review->valet_id =$valet_id;
        $review->customer_id = $customer_id;
        $review->request_id = $valet_request_id;
        $review->rating = $rating;
        $review->comment = $comment;
        $review->save();
    }

    public function getTotalTips()
    {
        $manager = Auth::user();
        $tips = PoolTip::where(['valet_manager_id'=>$manager->id,'status'=>0])->sum('amount');
        $valets = Valet::where('valet_manager_id',$manager->id)->with('user')->get();
        return Response::json([
            'success' => true,
            'tips'=> $tips,
            'valets'=> $valets
        ], 200);

    }
}
