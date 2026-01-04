<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\TomecoEnforcer;
use Illuminate\Validation\ValidationException;

class EnforcerAuthController extends Controller
{
    /**
     * Login enforcer via API (for mobile app)
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            // Find enforcer by username
            $enforcer = TomecoEnforcer::where('username', $request->username)->first();

            // Check if enforcer exists and password is correct
            if (!$enforcer || !Hash::check($request->password, $enforcer->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid username or password.',
                ], 401);
            }

            // Create a token for the enforcer
            try {
                $tokenResult = $enforcer->createToken('mobile-app');
                $token = $tokenResult->plainTextToken;
                
                // Debug: Log token info
                \Log::info('Login - Token created', [
                    'token_length' => strlen($token),
                    'token_preview' => substr($token, 0, 30) . '...',
                    'token_format' => substr($token, 0, 2), // Should be like "1|"
                    'token_result_type' => get_class($tokenResult),
                    'has_plainTextToken' => property_exists($tokenResult, 'plainTextToken'),
                ]);
                
                // Validate token format
                if (empty($token) || !str_contains($token, '|')) {
                    \Log::error('Login - Invalid token format', [
                        'token' => $token,
                        'token_length' => strlen($token),
                    ]);
                    throw new \Exception('Token creation failed: Invalid token format');
                }
            } catch (\Exception $tokenError) {
                \Log::error('Login - Token creation error', [
                    'error' => $tokenError->getMessage(),
                    'trace' => $tokenError->getTraceAsString(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create authentication token. Please try again.',
                    'error' => config('app.debug') ? $tokenError->getMessage() : null,
                ], 500);
            }

            // Get the full URL for the profile picture
            $profilePictureUrl = null;
            if ($enforcer->profile_picture) {
                if (filter_var($enforcer->profile_picture, FILTER_VALIDATE_URL)) {
                    $profilePictureUrl = $enforcer->profile_picture;
                } else {
                    $storagePath = str_starts_with($enforcer->profile_picture, '/') 
                        ? $enforcer->profile_picture 
                        : Storage::url($enforcer->profile_picture);
                    // Convert to full URL
                    $profilePictureUrl = url($storagePath);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'enforcer' => [
                        'id' => $enforcer->id,
                        'fullname' => $enforcer->fullname,
                        'username' => $enforcer->username,
                        'id_number' => $enforcer->id_number,
                        'gender' => $enforcer->gender,
                        'dob' => $enforcer->dob ? $enforcer->dob->toDateString() : null,
                        'contact_number' => $enforcer->contact_number,
                        'address' => $enforcer->address,
                        'profile_picture' => $profilePictureUrl,
                        'created_at' => $enforcer->created_at ? $enforcer->created_at->toDateTimeString() : null,
                        'updated_at' => $enforcer->updated_at ? $enforcer->updated_at->toDateTimeString() : null,
                    ],
                    'token' => $token,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login.',
            ], 500);
        }
    }

    /**
     * Get authenticated enforcer profile
     */
    public function profile(Request $request)
    {
        $enforcer = $request->user();
        
        // Get the full URL for the profile picture
        $profilePictureUrl = null;
        if ($enforcer->profile_picture) {
            if (filter_var($enforcer->profile_picture, FILTER_VALIDATE_URL)) {
                $profilePictureUrl = $enforcer->profile_picture;
            } else {
                $storagePath = str_starts_with($enforcer->profile_picture, '/') 
                    ? $enforcer->profile_picture 
                    : Storage::url($enforcer->profile_picture);
                // Convert to full URL
                $profilePictureUrl = url($storagePath);
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $enforcer->id,
                'fullname' => $enforcer->fullname,
                'username' => $enforcer->username,
                'id_number' => $enforcer->id_number,
                'gender' => $enforcer->gender,
                'dob' => $enforcer->dob ? $enforcer->dob->toDateString() : null,
                'contact_number' => $enforcer->contact_number,
                'address' => $enforcer->address,
                'profile_picture' => $profilePictureUrl,
                'created_at' => $enforcer->created_at ? $enforcer->created_at->toDateTimeString() : null,
                'updated_at' => $enforcer->updated_at ? $enforcer->updated_at->toDateTimeString() : null,
            ],
        ]);
    }

    /**
     * Logout enforcer
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}

