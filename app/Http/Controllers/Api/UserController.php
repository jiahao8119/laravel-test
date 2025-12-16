<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // GET /api/users?status=active&per_page=10&page=1
    public function index(Request $request)
    {
        $status = $request->query('status');
        $perPage = (int) $request->query('per_page', 10);
        $perPage = max(1, min($perPage, 100)); // clamp 1..100

        $query = User::query();

        if ($status) {
            $query->where('status', $status);
        }

        $users = $query->orderByDesc('id')->paginate($perPage);

        return UserResource::collection($users);
    }

    // POST /api/users
    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'status' => $request->status,
        ]);

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    // GET /api/users/{user}
    public function show(User $user)
    {
        return new UserResource($user);
    }

    // PUT /api/users/{user}
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->only(['name', 'email', 'phone_number', 'status']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return new UserResource($user->fresh());
    }

    // DELETE /api/users/{user} (soft delete)
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted (soft delete).',
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);

        $deleted = User::whereIn('id', $validated['user_ids'])->delete();

        return response()->json([
            'message' => 'Users deleted (soft delete).',
            'deleted_count' => $deleted,
        ]);
    }
}
