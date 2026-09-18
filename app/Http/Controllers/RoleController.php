<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return view("roles.index", compact("roles"));
    }
    public function store(Request $request)
    {
        $role = Role::create($request->all());
        return redirect()->route("roles.index")->with("success", "Role created successfully.");
    }
    // public function show($id)
    // {
    //     // Logic to retrieve a specific role by ID
    // }
    // public function update(Request $request, $id)
    // {
    //     // Logic to update a specific role by ID 
    // }
}
