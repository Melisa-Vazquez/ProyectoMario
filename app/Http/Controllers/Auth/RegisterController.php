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
            'password'              => ['required', 'confirmed', 'min:8',
                                        'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&._\-#]).{8,}$/'],
        ], [
            'name.required'         => 'El nombre es obligatorio.',
            'email.required'        => 'El correo electrónico es obligatorio.',
            'email.email'           => 'El correo electrónico no tiene un formato válido. Ejemplo: usuario@correo.com',
            'email.unique'          => 'Ya existe una cuenta con ese correo electrónico.',
            'password.min'          => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex'        => 'La contraseña debe tener al menos una mayúscula, una minúscula, un número y un símbolo especial (@$!%*?&._-#).',
            'password.confirmed'    => 'Las contraseñas no coinciden.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);
        return redirect('/');
    }
}
