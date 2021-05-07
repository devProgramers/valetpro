<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DirectTip;
use App\Models\PoolTip;
use App\Models\Review;
use App\Models\Valet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class ReportsController extends Controller
{
    public function tipsReport(Request $request)
    {
        $manager = Auth::user();
        $startDate = $request->start_date;
        $endDate = $request->end_data;
        $valets = Valet::where('valet_manager_id',$manager->id)->get()->pluck('user_id');
        $directTips = DirectTip::whereIn('valet_id',$valets)->whereBetween('created_at',[$startDate,$endDate])->with('valets')->get();
        $poolTips = PoolTip::whereIn('valet_id',$valets)->whereBetween('created_at',[$startDate,$endDate])->with('valets')->get();
        $directTipsTotal = $directTips->sum('amount');
        $poolTipsTotal = $poolTips->sum('amount');
        $totalRevenew = $directTipsTotal + $poolTipsTotal;
        return Response::json([
            'success' => true,
            'directTipsTotal' => $directTipsTotal,
            'poolTipsTotal' => $poolTipsTotal,
            'totalRevenew' => $totalRevenew,
            'directTips'=> $directTips,
            'poolTips'=> $poolTips
        ], 200);

    }
    public function ratingReport(Request $request)
    {
        $manager = Auth::user();
        $startDate = $request->start_date;
        $endDate = $request->end_data;
        $valets = Valet::where('valet_manager_id',$manager->id)->get()->pluck('user_id');
        $ratings = Review::whereIn('valet_id',$valets)->with('valets')->get();
        return Response::json([
            'success' => true,
            'ratings' => $ratings,
        ], 200);

    }
}
