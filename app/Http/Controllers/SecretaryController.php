<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;

class SecretaryController extends Controller
{
   

    /**
     * Add a user with a secretary role.
     */
    public function addSecretary(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user= User::create([
            'name'=> $request->name,
            'email'=>$request->email,
            'password'=> bcrypt($request->password),
            
        ]);

        $user->is_doctor = false;
        $user->doctor_id = auth('sanctum')->user()->id;
        $user->save();

        return response()->json($user);
    }

    public function viewDoctorAppointments()
    {
        $user = auth('sanctum')->user();
        $doctorId = $user->doctor_id;

        $appointments = Appointment::where('user_id', $doctorId)->get();

        $arr = array();
        foreach($appointments as $appointment)
        {
            $jsob = [
                "id" => $appointment->id,
                "Summary"=>$appointment->summary,
                "description"=>$appointment->description,
                "start"=>$appointment->start,
                "end"=>$appointment->end,

            ]; 

            array_push($arr, $jsob);
        }

         // return appointments list as json
         return response()->json($arr, 200);

    }

  
}
