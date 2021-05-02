<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordCode;
use App\Mail\ValetSignUp;
use App\Models\Role;
use App\Models\User;
use App\Models\Valet;
use App\Models\ValetManager;
use App\Models\ValetManagerLocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use File;

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
                $request->role_id==2?$request->pic->move(public_path('/profiles/managers/'),$profile_pic):$request->pic->move(public_path('/profiles/customers/'),$profile_pic);
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

                $user->pic = url('profiles/managers/'.$user->pic);
                return Response::json([
                    'success' => true,
                    'msg'=> 'Valet Manager Signed Up Successfully',
                    'user' => $user,
                ], 200);

            }elseif($request->role_id == 4){
                $user->save();
                $user->pic = url('profiles/customers/'.$user->pic);
                return Response::json([
                    'success' => true,
                    'msg'=> 'Customer Signed Up Successfully',
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
                $user->pic = url('profiles/managers/'.$user->pic);
                $user->role = Auth::user()->role;
            }elseif ($user->role_id == 3){
                $user['valet'] = Valet::where('user_id',$user->id)->get();
                $user->pic = url('profiles/valets/'.$user->pic);
                $user->role = Auth::user()->role;
            }elseif ($user->role_id == 4){
                $user->pic = url('profiles/customers/'.$user->pic);
                $user->role = Auth::user()->role;
            }
            $token = $user->createToken('auth-token')->plainTextToken;
            return response()->json([
                'success' => true,
                'user' => $user,
                'token' =>$token
            ], 200);
        } else {
            $user = User::where('email',$request->email)->first();
            if ($user){
                 $chk = Hash::check($request->password,$user->password);
                 if (!$chk){
                     $msg = 'Incorrect Password';
                 }
            }else{
                $msg = 'Email address not found';
            }
            return response()->json(['error' => 'UnAuthorized','msg'=>$msg], 203);
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


                $user->pic = url('profiles/valets/'.$user->pic);
                $data = array('email'=>$request->email,'password'=>$request->password,'name'=>$request->first_name);
                Mail::to($request->email)->send(new ValetSignUp($data));
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

    public function getLocations()
    {
        $user = Auth::user();
        $locations = ValetManagerLocation::where('valet_manager_id',$user->id)->get();

        return Response::json([
            'success' => true,
            'locations'=>$locations,
        ], 200);

    }

    public function edit($id)
    {
        $user = User::find($id);
        if ($user->role_id == 2){
            $user['manager'] = ValetManager::where('user_id',$user->id)->first();
            $user['manager']['locations'] = ValetManagerLocation::where('valet_manager_id',$user['manager']->id)->get();
            $user->pic = url('profiles/managers/'.$user->pic);
        }elseif ($user->role_id == 3){
           $user['valet'] = Valet::where('user_id',$user->id)->first();
           $user['valet']['location'] = ValetManagerLocation::where('id',$user['valet']->id)->first();
            $user->pic = url('profiles/valets/'.$user->pic);
        }elseif ($user->role_id == 4){
            $user->pic = url('profiles/customers/'.$user->pic);
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

    public function update(Request $request,$id)
    {
        $user = User::find($id);
        if ($user->role_id == 2){
            $validator = Validator::make($request->all(), [
                'first_name'=>['required'],
                'last_name'=>['required'],
                'phone'=>['required','min:1']]);

            if ($validator->fails()){
                return Response::json([
                    'success' => false,
                    'msg'=> $validator->messages(),
                ], 301);
            }else{

                if($request->hasFile('pic'))
                {
                    $file=public_path('/profiles/managers/'.$user->pic);
                    if (!empty($user->pic)) {

                        if (File::exists($file)) {
                            unlink($file);
                        }
                    }else{
                        $profile_pic=$user->pic;
                    }
                    $profile_pic = $request->file('pic');
                    $extension = $profile_pic->getClientOriginalExtension();
                    $profile_pic=time()."-profile.".$extension;
                    $user->role_id==2?$request->pic->move(public_path('/profiles/managers/'),$profile_pic):$request->pic->move(public_path('/profiles/valets/'),$profile_pic);
                }

                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->phone = $request->phone;
                $user->pic = $profile_pic ?? null;
                if (isset($request->password)){
                    $user->password = Hash::make($request->password);
                }
            }

                if ($user->role_id == 2){
                    $user->save();
                    $manager = ValetManager::where('user_id',$user->id)->first();
                    $manager->company_name = $request->company_name;
                    $manager->save();

                    if (is_array($request->location)){
                        foreach ($request->location as $locations){
                            $location = ValetManagerLocation::find($locations['id']);
                            if (isset($location)){
                                $location->name = $locations['name'];
                                $location->address = $locations['address'];
                                $location->longitude = $locations['longitude'];
                                $location->latitude = $locations['latitude'];
                                $location->save();
                            }else{
                                $location = new ValetManagerLocation;
                                $location->valet_manager_id = $manager->id;
                                $location->name = $locations['name'];
                                $location->address = $locations['address'];
                                $location->longitude = $locations['longitude'];
                                $location->latitude = $locations['latitude'];
                                $location->save();
                                }
                            }
                        }
                    }
                    $user->pic = url('profiles/managers/'.$user->pic);
        }elseif ($user->role_id == 3){
            $validator = Validator::make($request->all(), [
                'first_name'=>['required'],
                'last_name'=>['required'],
                'phone'=>['required','min:1'],
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
                    $file=public_path('/profiles/valets/'.$user->pic);
                    if (!empty($user->pic)) {

                        if (File::exists($file)) {
                            unlink($file);
                        }
                    }else{
                        $profile_pic=$user->pic;
                    }
                    $profile_pic = $request->file('pic');
                    $extension = $profile_pic->getClientOriginalExtension();
                    $profile_pic=time()."-profile.".$extension;
                    $request->pic->move(public_path('/profiles/valets/'),$profile_pic);
                }

                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->phone = $request->phone;
                $user->pic = $profile_pic ?? null;
                if (isset($request->password)){
                    $user->password = Hash::make($request->password);
                }
                    $user->save();
                    $valet = Valet::where('user_id',$user->id)->first();
                    $valet->valet_manager_location_id = $request->valet_manager_location_id;
                    $valet->save();
            }
            $user->pic = url('profiles/valets/'.$user->pic);
        }elseif ($user->role_id == 4){
            $validator = Validator::make($request->all(), [
                'first_name'=>['required'],
                'last_name'=>['required'],
                'phone'=>['required','min:1']]);

            if ($validator->fails()){
                return Response::json([
                    'success' => false,
                    'msg'=> $validator->messages(),
                ], 301);
            }else{
                if($request->hasFile('pic'))
                {
                    $file=public_path('/profiles/customers/'.$user->pic);
                    if (!empty($user->pic)) {

                        if (File::exists($file)) {
                            unlink($file);
                        }
                    }else{
                        $profile_pic=$user->pic;
                    }
                    $profile_pic = $request->file('pic');
                    $extension = $profile_pic->getClientOriginalExtension();
                    $profile_pic=time()."-profile.".$extension;
                    $request->pic->move(public_path('/profiles/customers/'),$profile_pic);
                }

                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->phone = $request->phone;
                $user->pic = $profile_pic ?? null;
                if (isset($request->password)){
                    $user->password = Hash::make($request->password);
                }
                $user->save();
            }
            $user->pic = url('profiles/customers/'.$user->pic);
        }else{
            return Response::json([
                'success' => false,
                'msg'=>'No such user found',
            ], 302);
        }

        return Response::json([
            'success' => true,
            'msg' => 'User successfully updated',
            'user'=>$user,
        ], 200);

    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'=>['required']
        ]);

        if ($validator->fails()){
            return Response::json([
                'success' => false,
                'msg'=> $validator->messages(),
            ], 301);
        }else{
            $email = $request->email;
            $user = User::where('email',$email)->first();
            if (isset($user)){
                $code = substr(md5(time()), 0, 6);
                $old = DB::table('password_resets')->where('email',$email)->first();
                if (isset($old)){
                    DB::table('password_resets')->update([
                        'token' => $code,
                        'created_at' => now()
                    ]);
                }else{
                    DB::table('password_resets')->insert([
                        'email' => $email,
                        'token' => $code,
                        'created_at' => now()
                    ]);
                }
                Mail::to($email)->send(new ResetPasswordCode($code));
                return Response::json([
                    'success' => true,
                    'msg'=>'A code is been send at your email please use that code to reset password',
                ], 200);
            }else{
                return Response::json([
                    'success' => false,
                    'msg'=>'No user with this email exists',
                ], 302);
            }
        }


    }

    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'=>['required'],
            'code'=>['required','min:6'],
            'password'=>['required','min:6']]);

        if ($validator->fails()){
            return Response::json([
                'success' => false,
                'msg'=> $validator->messages(),
            ], 301);
        }else{
            $email = $request->email;
            $code = $request->code;
            $password = Hash::make($request->password);
            $user = User::where('email',$email)->first();
            $old = DB::table('password_resets')->where('email',$email)->first();

            if (isset($user)){
                if (isset($old)){
                    $check = ($old->token) == $code;
                    if ($check){
                        $user->password = $password;
                        $user->save();
                        return Response::json([
                            'success' => true,
                            'msg'=>'Password successfully updated',
                        ], 200);
                    }else{
                        return Response::json([
                            'success' => false,
                            'msg'=>'Code is incorrect',
                        ], 302);
                    }
                }else{
                    return Response::json([
                        'success' => false,
                        'msg'=>'Please try again to reset password',
                    ], 302);
                }
            }else{
                return Response::json([
                    'success' => false,
                    'msg'=>'No user with this email exists',
                ], 302);
            }
        }


    }
}
