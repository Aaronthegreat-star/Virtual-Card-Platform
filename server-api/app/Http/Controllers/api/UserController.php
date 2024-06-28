<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request){

        $userType = $request->userType;
        $userQuery = User::query();

        if ($userType != null)
            $userQuery->where('user_type', $userType);

        if ($request->paginate == '0')
            return UserResource::collection($userQuery->orderBy('name', 'asc')->get());

        $blocked = $request->blocked;
        $order = $request->order;

        if ($blocked != null)
            $userQuery->where('blocked', $blocked);

        if ($order != null)
            $userQuery->orderBy('name', $order);
        
        
        return UserResource::collection($userQuery->paginate(15));
    }
    
    public function show(User $user){
        return new UserResource($user);
    }

    // public function update(UpdateUserRequest $request, User $user)
    // {   
    //     $user->update($request->validated());
    //     return new UserResource($user);
    // }

    public function show_me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function getDistributionOfUsers ()
    {
        $distributionUsers = User::select('user_type', \DB::raw('COUNT(*) as count'))
            ->groupBy('user_type')
            ->get();
    
        return response()->json($distributionUsers);
    }
    

}
