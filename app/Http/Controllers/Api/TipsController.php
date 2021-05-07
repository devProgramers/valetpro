<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DirectTip;
use App\Models\PoolTip;
use App\Models\Review;
use App\Models\User;
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
}
