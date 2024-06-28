<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Http\Resources\AdminResource;
use App\Http\Requests\UpdateAdminRequest;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateUserPasswordRequest;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    public function store(StoreAdminRequest $request)
    {
        $newAdmin = Admin::create($request->validated());
        return new AdminResource($newAdmin);
    }

    public function update(UpdateAdminRequest $request, Admin $admin)
    {
        $admin->update($request->validated());
        return new AdminResource($admin);
    }

    public function update_password (UpdateUserPasswordRequest $request, Admin $admin)
    {
        $admin->password = bcrypt($request->validated()['password']);
        $admin->save();
        return new AdminResource($admin);
    }

    public function destroy (Admin $admin){

        $admin->forceDelete();

        return new AdminResource($admin);
    }
}
