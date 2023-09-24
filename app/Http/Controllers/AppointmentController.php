<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;
use Illuminate\Http\Request;
use Google\Client as GoogleClient;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth('sanctum')->user();

        $userAccessToken = "";
        if($user->is_doctor) $userAccessToken =$user->google_access_token;  
        else{
            $doctor = User::where('id', $user->doctor_id)->first();
            $userAccessToken = $doctor->google_access_token;
        }
        // Initialize the Google API client
        $client = new GoogleClient();
        $client->setAccessToken($userAccessToken); // Use the access token obtained during authentication

        // Create a Google Calendar service instance
        $calendarService = new \Google\Service\Calendar($client);

        // List events from the user's primary calendar
        $calendarId = 'primary';
        $events = $calendarService->events->listEvents($calendarId);

        $arr = array();
        foreach ($events->getItems() as $event) {
            $jsob = [
                "event_id" => $event->id,
                "Summary"=>$event->summary,
                "description"=>$event->description,
                "start"=>$event->start->dateTime,
                "end"=>$event->end->dateTime,

            ]; 

            array_push($arr, $jsob);
        }

        // return events list as json
        return response()->json($arr, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'summary' => ['required'],
            'description' => ['required'],
            'start' => ['required'],
            'end' => ['required'],
            'timeZone' => ['required'],
        ]);

        $user = auth('sanctum')->user();
        // Initialize the Google API client
        $client = new GoogleClient();
        // $client->setAuthConfig("client_secret.json"); // Path to your OAuth 2.0 client secret file
        $client->setAccessToken($user->google_access_token); // Use the access token obtained during authentication
        // Create a Google Calendar service instance
        $calendarService =  new \Google\Service\Calendar($client);
        
        // Create a new event
        $event = new \Google\Service\Calendar\Event([
            'summary' => $request->summary,
            'description' => $request->description,
            'start' => [
                'dateTime' => $request->start,
                'timeZone' => $request->timeZone, // Adjust to the desired time zone
            ],
            'end' => [
                'dateTime' => $request->end,
                'timeZone' => $request->timeZone,
            ],
            ]);
            
            // Insert the event into the user's primary calendar
            $calendarId = 'primary';
            $event = $calendarService->events->insert($calendarId, $event);
            
            
        // Storing the event ID 
        $eventId = $event->getId();

        $appointment = Appointment::create([
            'user_id' => $user->id,
            'event_id'=> $eventId,
            'summary' => $request->summary,
            'description'=> $request->description,
            'start'=> $request->start,
            'end'=> $request->end
        ]);

        return response()->json($appointment);
    }

    
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {
        $request->validate([
            'summary' => ['required'],
            'description' => ['required'],
            'start' => ['required'],
            'end' => ['required'],
            'timeZone' => ['required'],
        ]);

        $appointment = Appointment::where('id', $id)->first();
        $user = auth('sanctum')->user();
        // Initialize the Google API client
        $client = new GoogleClient();
        // $client->setAuthConfig("client_secret.json"); // Path to your OAuth 2.0 client secret file
        
        $client->setAccessToken($user->google_access_token); // Use the access token obtained during authentication
        // Create a Google Calendar service instance
        $calendarService =  new \Google\Service\Calendar($client);
        

        // Fetch the event by its ID
        $eventId = $appointment->event_id; // Replace with the actual event ID
        $calendarId = 'primary';
        $event = $calendarService->events->get($calendarId, $eventId);

        // Modify the event properties as needed
        $event->setSummary($request->summary);
        $event->setDescription($request->description);
        $event->start = new \Google\Service\Calendar\EventDateTime();
        $event->start->dateTime = $request->start;
        $event->start->timeZone = $request->timeZone;
        $event->end = new \Google\Service\Calendar\EventDateTime();
        $event->end->dateTime = $request->end;   // End time
        $event->end->timeZone = $request->timeZone;   // End time

        // Update the event
        $updatedEvent = $calendarService->events->update($calendarId, $eventId, $event);

        // update on db
        $appointment = Appointment::where('id', $id)->update([
            'summary' => $request->summary,
            'description'=> $request->description,
            'start'=> $request->start,
            'end'=> $request->end
        ]);
        return response()->json(['message'=>'Event updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        $appointment = Appointment::where('id', $id)->first();
        $user = auth('sanctum')->user();
        // Initialize the Google API client
        $client = new GoogleClient();
        // $client->setAuthConfig("client_secret.json"); // Path to your OAuth 2.0 client secret file
        
        $client->setAccessToken($user->google_access_token); // Use the access token obtained during authentication
      
        // Create a Google Calendar service instance
        $calendarService =  new \Google\Service\Calendar($client);

        // Fetch the event by its ID (optional, but useful for error handling)
        $eventId = $appointment->event_id; // Replace with the actual event ID
        $calendarId = 'primary';
        $event = $calendarService->events->get($calendarId, $eventId);

        // Delete the event
        $calendarService->events->delete($calendarId, $eventId);

        // delete on db
        $appointment->delete();

        return response()->json(['message'=>'Event deleted successfully'], 200);
    }
}
