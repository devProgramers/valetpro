<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class AdminController extends Controller
{
    public function users()
    {
        $all = User::whereNotIn('role_id', [1])->get();
        $customers = User::where('role_id',4)->get();
        $valets = User::where('role_id',3)->get();
        $valet_managers = User::where('role_id',2)->get();

        if (isset($all)){
            return Response::json([
                'success' => true,
                'all'=> $all,
                'customers'=> $customers,
                'valets'=> $valets,
                'valet_managers'=> $valet_managers,
            ], 200);
        }else{
            return Response::json([
                'success' => false,
                'msg'=> 'No users found',
            ], 302);
        }
    }

    public function ratingReport(Request $request)
    {
        $from = date($request->from);
        $to = date($request->to);
            $reviews = Review::whereBetween('created_at',[$from,$to])->with('valet','customer')->get();
        if (isset($reviews)){
            return Response::json([
                'success' => true,
                'reviews'=> $reviews,
            ], 200);
        }else{
            return Response::json([
                'success' => false,
                'msg'=> 'No reviews found',
            ], 302);
        }
    }
}
