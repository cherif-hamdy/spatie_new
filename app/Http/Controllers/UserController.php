<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\Fluent\Concerns\Has;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $data = User::latest()->paginate(5);
        return view('users.index', compact('data'));
    }

    public function create()
    {
        $roles = Role::pluck('name', 'name')->all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required'
        ]);

        $user = User::create(array_merge($data, ['password' => Hash::make($data['password'])]));

        $user->assignRole($data['roles']);
        return redirect()->route('users.index')->with('success', 'Added Successfully');
    }

    public function show($id)
    {
        $user = User::find($id);
        return view('users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name', 'name')->all();
        $userRole = $user->roles->pluck('name', 'name')->all();
        return view('users.edit', compact('user', 'roles', 'userRole'));
    }

    public function update(Request $request, $id)
    {
        $data = $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' .$id,
            'password' => 'required|same:confirm-password',
            'roles' => 'required'
        ]);

        if (!empty($data['password']))
        {
            $data['password'] = Hash::make($data['password']);
        }else {
            $data = Arr::except($data,array('password'));
        }
        $user = User::find($id);
        $user->update($user);
        DB::table('model_has_roles')->where('model_id', $id)->delete();
        return redirect()->route('users.index')->with('success', 'Updated Successfully');
    }

    public function destroy($id)
    {
        User::find($id)->delete();
        return redirect()->route('users.index')->with('success','Deleted Successfully');
    }

}
