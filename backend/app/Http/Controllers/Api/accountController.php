<?php
// app/Http/Controllers/Api/AccountController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ], [
            'current_password.required_with' => 'Current password is required to set a new password',
            'new_password.confirmed' => 'New password confirmation does not match',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check current password if changing password
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => [
                        'current_password' => ['Current password is incorrect']
                    ]
                ], 422);
            }
        }

        // Update only the fields that were provided
        $updateData = [];

        if ($request->filled('name')) {
            $updateData['full_name'] = $request->name;
        }

        if ($request->filled('email')) {
            $updateData['email'] = $request->email;
        }

        if ($request->filled('new_password')) {
            $updateData['password'] = Hash::make($request->new_password);
        }

        if (!empty($updateData)) {
            $user->update($updateData);

            return response()->json([
                'message' => 'Account updated successfully'
            ]);
        }

        return response()->json([
            'message' => 'No changes were made'
        ]);
    }
}
