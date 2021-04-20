<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Valet;
use App\Models\ValetManager;
use App\Models\ValetManagerLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class userController extends Controller
{
    public function getRoles()
    {
        $roles = Role::all();
        foreach ($roles as $role){
            $role->name = ucwords($role->name);
        }
        return Response::json([
            'success' => true,
            'roles'=>$roles,
        ], 200);
    }

    public function signUp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'=>['required'],
            'last_name'=>['required'],
            'role_id'=>['required','min:1'],
            'email'=>['required','unique:users'],
            'phone'=>['required','min:1'],
            'password'=>['required','min:6']]);

        if ($validator->fails()){
            return Response::json([
                'success' => false,
                'msg'=> $validator->messages(),
            ], 301);
        }else{

            if($request->hasFile('pic'))
            {
                $profile_pic = $request->file('pic');
                $extension = $profile_pic->getClientOriginalExtension();
                $profile_pic=time()."-profile.".$extension;
                $request->role_id==2?$request->pic->move(public_path('/profiles/managers/'),$profile_pic):$request->pic->move(public_path('/profiles/valets/'),$profile_pic);
            }

            $user =  new User;
            $user->role_id = $request->role_id;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->pic = $profile_pic ?? null;
            $user->password = Hash::make($request->password);

            if ($request->role_id == 2){
                $user->save();
                $manager = new ValetManager;
                $manager->user_id = $user->id;
                $manager->company_name = $request->company_name;
                $manager->save();

                if (is_array($request->location)){
                    foreach ($request->location as $locations){
                        $location = new ValetManagerLocation;
                        $location->valet_manager_id = $manager->id;
                        $location->name = $locations['name'];
                        $location->address = $locations['address'];
                        $location->longitude = $locations['longitude'];
                        $location->latitude = $locations['latitude'];
                        $location->save();
                    }
                }
                return Response::json([
                    'success' => true,
                    'msg'=> 'Valet Manager Signed Up Successfully',
                    'user' => $user,
                ], 200);

            }else{
                return Response::json([
                    'success' => false,
                    'msg'=> 'No Such Role exits',
                ], 302);
            }
        }
    }

    public function signIn(Request $request){
        $credentials = $request->only('email', 'password');
        if (auth()->attempt($credentials)) {
            $user = Auth::user();
            if ($user->role_id == 2){
                $user['manager'] = ValetManager::where('user_id',$user->id)->with('locations')->get();
            }elseif ($user->role_id == 3){
                $user['valet'] = Valet::where('user_id',$user->id)->get();
            }
            $token = $user->createToken('auth-token')->plainTextToken;
            return response()->json([
                'success' => true,
                'user' => $user,
                'token' =>$token
            ], 200);
        } else {
            return response()->json(['error' => 'UnAuthorized'], 203);
        }
    }

    public function signUpValet(Request $request){
        $validator = Validator::make($request->all(), [
            'first_name'=>['required'],
            'last_name'=>['required'],
            'role_id'=>['required','min:1'],
            'email'=>['required','unique:users'],
            'phone'=>['required','min:1'],
            'password'=>['required','min:6'],
            'valet_manager_location_id'=>['required','min:1']
        ]);

        if ($validator->fails()){
            return Response::json([
                'success' => false,
                'msg'=> $validator->messages(),
            ], 301);
        }else{
            if($request->hasFile('pic'))
            {
                $profile_pic = $request->file('pic');
                $extension = $profile_pic->getClientOriginalExtension();
                $profile_pic=time()."-profile.".$extension;
                $request->pic->move(public_path('/profiles/valets/'),$profile_pic);
            }

            $user =  new User;
            $user->role_id = $request->role_id;
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->pic = $profile_pic ?? null;
            $user->password = Hash::make($request->password);
            $manager = Auth::user();
            $manager = isset($manager)?$manager->role_id:0;
            if ($manager == 2 || $manager == 1){
                $user->save();
                $valet = new Valet;
                $valet->user_id = $user->id;
                $valet->valet_manager_id = Auth::user()->id;
                $valet->valet_manager_location_id = $request->valet_manager_location_id;
                $valet->save();


                return Response::json([
                    'success' => true,
                    'msg'=> 'Valet Successfully Registered',
                    'user' => $user,
                ], 200);
            }else{
                return Response::json([
                    'success' => false,
                    'msg'=> 'UnAuthorized to create a valet',
                ], 302);
            }
        }
    }

    public function edit($id)
    {
        $user = User::find($id);
        if ($user->role_id == 2){
            $user['manager'] = ValetManager::where('user_id',$user->id)->first();
            $user['manager']['locations'] = ValetManagerLocation::where('valet_manager_id',$user['manager']->id)->get();
        }elseif ($user->role_id == 3){
           $user['valet'] = Valet::where('user_id',$user->id)->first();
           $user['valet']['location'] = ValetManagerLocation::where('id',$user['valet']->id)->first();
        }else{
            return Response::json([
                'success' => false,
                'msg'=>'No such user found',
            ], 302);
        }

        return Response::json([
            'success' => true,
            'user'=>$user,
        ], 200);

    }

    public function update($id)
    {
        $user = User::find($id);
        if ($user->role_id == 2){

        }elseif ($user->role_id == 3){

        }else{
            return Response::json([
                'success' => false,
                'msg'=>'No such user found',
            ], 302);
        }

        return Response::json([
            'success' => true,
            'user'=>$user,
        ], 200);

    }
}