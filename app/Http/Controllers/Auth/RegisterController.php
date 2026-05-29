<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|unique:users',
            'password'              => 'required|min:8|confirmed',
        ], [
            'name.required'         => 'El nombre es obligatorio.',
            'email.unique'          => 'Ya existe una cuenta con ese correo.',
            'password.min'          => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'    => 'Las contraseñas no coinciden.',
        ]);

        // El primer usuario registrado es admin, los demás son miembros
        $role = User::count() === 0 ? 'admin' : 'member';

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $role,
        ]);

        Auth::login($user);
        return redirect('/');
    }
}
