<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthenticationController extends Controller
{
    
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user= User::create([
            'name'=> $request->name,
            'email'=>$request->email,
            'password'=> bcrypt($request->password)
        ]);

        return response()->json($user);

    }

     

    public function login(Request $request)
    {
         $request->validate([    
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'The given data was invalid.',
            ], 400);
            
        }

        $user = User::where('email', $request->email)->first();
        $authToken = $user->createToken('auth-token')->plainTextToken;
 
        return response()->json(['access-token' => $authToken]);

    }

   
    public function logout(Request $request)
    {
       
        
        $flag = auth('sanctum')->user()->currentAccessToken()->delete();
 
        if($flag)
        return response('Success', 200);
        else
        return response('Bad_request', 400);
    }

   
}
