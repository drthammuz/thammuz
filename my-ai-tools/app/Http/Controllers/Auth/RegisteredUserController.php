<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    public function store(Request $request)
    {
        Log::info('Register request:', $request->all());

        // Custom validation error messages
        $messages = [
            'email.unique' => 'The email has already been taken.',
            'password.confirmed' => 'The passwords do not match.',
            // Add other custom messages here
        ];

        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        try {
            // Create a new user in the database
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->sendEmailVerificationNotification();
            // Optionally, perform any other action like sending a welcome email

            // Return a JSON response
            return response()->json(['message' => 'User registered successfully', 'user' => $user]);
        } catch (\Exception $e) {
            Log::error('Registration error:', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Server error occurred. Please try again.'], 500); // Internal Server Error
        }
    }
}
