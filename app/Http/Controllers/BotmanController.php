<?php

namespace App\Http\Controllers;

use BotMan\BotMan\BotMan;
use Illuminate\Http\Request;
use App\Models\ChatUser;
use Illuminate\Support\Facades\Session;

class BotManController extends Controller
{
    /**
     * Handle the incoming messages from the custom UI.
     */
    public function handle(Request $request)
    {
        // Get the user's message
        $message = $request->input('message');
        
        // Check the current conversation state from session
        $conversationState = Session::get('conversation_state', 'start');
        $tempData = Session::get('temp_data', []);
        
        // Prepare the response
        $response = '';
        
        // Process based on conversation state
        switch ($conversationState) {
            case 'start':
                if (strtolower($message) == 'hi') {
                    $response = 'Hello! What is your name?';
                    Session::put('conversation_state', 'waiting_for_name');
                } else {
                    $response = "Start a conversation by saying hi.";
                }
                break;
                
            case 'waiting_for_name':
                $name = $message;
                $existingUser = ChatUser::where('name', $name)->first();
                
                if ($existingUser) {
                    $response = 'Welcome back, ' . $name . '! Your stored email is: ' . $existingUser->email . 
                               '. Would you like to update your email? (yes/no)';
                    Session::put('temp_data', ['name' => $name]);
                    Session::put('conversation_state', 'existing_user_email_update');
                } else {
                    $response = 'Nice to meet you, ' . $name . '. Can you advise about your email address?';
                    Session::put('temp_data', ['name' => $name]);
                    Session::put('conversation_state', 'waiting_for_email');
                }
                break;
                
            case 'waiting_for_email':
                $email = $message;
                $name = $tempData['name'] ?? 'User';
                
                // Store in database
                $chatUser = new ChatUser();
                $chatUser->name = $name;
                $chatUser->email = $email;
                $chatUser->save();
                
                $response = 'Thank you! Your information has been saved. Email: ' . $email . 
                           '. Feel free to ask if you need anything else!';
                Session::put('conversation_state', 'start');
                Session::forget('temp_data');
                break;
                
            case 'existing_user_email_update':
                $answer = strtolower($message);
                $name = $tempData['name'] ?? 'User';
                $existingUser = ChatUser::where('name', $name)->first();
                
                if ($answer == 'yes') {
                    $response = 'Please provide your new email address:';
                    Session::put('conversation_state', 'updating_email');
                } else {
                    $response = 'Okay, your email remains: ' . $existingUser->email . 
                               '. Feel free to ask if you need anything else!';
                    Session::put('conversation_state', 'start');
                    Session::forget('temp_data');
                }
                break;
                
            case 'updating_email':
                $newEmail = $message;
                $name = $tempData['name'] ?? 'User';
                $existingUser = ChatUser::where('name', $name)->first();
                
                if ($existingUser) {
                    $existingUser->email = $newEmail;
                    $existingUser->save();
                    $response = 'Your email has been updated to: ' . $newEmail . 
                               '. Feel free to ask if you need anything else!';
                } else {
                    $response = 'Sorry, there was an error updating your email. Please try again.';
                }
                
                Session::put('conversation_state', 'start');
                Session::forget('temp_data');
                break;
                
            default:
                $response = "I'm not sure what to do next. Let's start over. Say 'hi' to begin.";
                Session::put('conversation_state', 'start');
                Session::forget('temp_data');
        }
        
        return response()->json(['message' => $response]);
    }
}