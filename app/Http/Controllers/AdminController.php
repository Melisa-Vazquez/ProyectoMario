<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    private function checkAdmin()
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Solo los administradores pueden realizar esta acción.');
        }
    }

    // Lista todos los usuarios (solo admin)
    public function users()
    {
        $this->checkAdmin();
        return response()->json(
            User::select('id', 'name', 'email', 'role', 'created_at')->orderBy('name')->get()
        );
    }

    // Crea un nuevo miembro desde el panel admin
    public function createUser(Request $request)
    {
        $this->checkAdmin();

        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8',
        ], [
            'email.unique'    => 'Ya existe un usuario con ese correo.',
            'password.min'    => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role'     => 'member',
        ]);

        return response()->json(['ok' => true, 'user' => $user->only('id', 'name', 'email', 'role')], 201);
    }

    // Cambia el rol de un usuario (solo admin)
    public function updateRole(Request $request, $id)
    {
        $this->checkAdmin();

        $request->validate(['role' => 'required|in:admin,member']);

        $user = User::findOrFail($id);

        // No se puede quitar el rol a sí mismo
        if ($user->id === auth()->id()) {
            return response()->json(['error' => 'No puedes cambiar tu propio rol.'], 422);
        }

        $user->update(['role' => $request->role]);

        return response()->json(['ok' => true, 'user' => $user->only('id', 'name', 'role')]);
    }
}
