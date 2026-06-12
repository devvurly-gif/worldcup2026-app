<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'user',
        ]);

        $token = $user->createToken('spa')->plainTextToken;

        return response()->json(['user' => $this->userResource($user), 'token' => $token], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => ['Identifiants incorrects.']]);
        }

        if (! $user->is_active) {
            return response()->json(['message' => 'Compte désactivé.'], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('spa')->plainTextToken;

        return response()->json(['user' => $this->userResource($user), 'token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté.']);
    }

    public function profile(Request $request)
    {
        return response()->json($this->userResource($request->user()));
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name'   => 'sometimes|string|max:255',
            'avatar' => 'sometimes|string|nullable',
        ]);
        $user->update($data);
        return response()->json($this->userResource($user));
    }

    // Admin: list users
    public function users(Request $request)
    {
        $this->requireAdmin($request);
        return response()->json(User::orderBy('created_at', 'desc')->get()->map(fn($u) => $this->userResource($u)));
    }

    public function updateUser(Request $request, User $user)
    {
        $this->requireAdmin($request);
        $data = $request->validate([
            'role'      => 'sometimes|in:admin,editor,user',
            'is_active' => 'sometimes|boolean',
        ]);
        $user->update($data);
        return response()->json($this->userResource($user));
    }

    private function requireAdmin(Request $request)
    {
        if (! $request->user()?->isAdmin()) {
            abort(403, 'Accès refusé.');
        }
    }

    private function userResource(User $user): array
    {
        return [
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'role'      => $user->role,
            'avatar'    => $user->avatar,
            'is_active' => $user->is_active,
            'created_at'=> $user->created_at,
        ];
    }
}
