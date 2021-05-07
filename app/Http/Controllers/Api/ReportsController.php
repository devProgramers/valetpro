<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DirectTip;
use App\Models\PoolTip;
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
        $directTips = DirectTip::whereIn('valet_id',$valets)->whereBetween('created_at',[$startDate,$endDate])->get();
        $poolTips = PoolTip::where('valet_id'.$valets)->whereBetween('created_at',[$startDate,$endDate])->get();
        return Response::json([
            'success' => true,
            'msg'=> ''
        ], 200);

    }
}
