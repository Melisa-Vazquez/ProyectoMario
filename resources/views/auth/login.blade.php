<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Gestión Ágil</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm">

        {{-- Logo / título --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-600/20 border border-indigo-500/30 rounded-2xl mb-4">
                <span class="text-2xl">🚀</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Gestión Ágil</h1>
            <p class="text-slate-500 text-sm mt-1">Inicia sesión para acceder al tablero</p>
        </div>

        {{-- Card --}}
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-8 shadow-2xl">

            @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-xl px-4 py-3 mb-5">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/login">
                @csrf

                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                        Correo electrónico
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 transition placeholder:text-slate-600"
                        placeholder="tu@correo.com">
                </div>

                <div class="mb-6">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                        Contraseña
                    </label>
                    <input type="password" name="password" required
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 transition placeholder:text-slate-600"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center gap-2 mb-6">
                    <input type="checkbox" name="remember" id="remember"
                        class="w-4 h-4 rounded accent-indigo-500 cursor-pointer">
                    <label for="remember" class="text-xs text-slate-400 cursor-pointer">Recordarme</label>
                </div>

                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 rounded-xl transition shadow-lg shadow-indigo-600/20 text-sm">
                    Iniciar sesión
                </button>
            </form>

        </div>

        <p class="text-center text-sm text-slate-500 mt-6">
            ¿No tienes cuenta?
            <a href="/register" class="text-indigo-400 hover:text-indigo-300 font-semibold transition">Regístrate</a>
        </p>

    </div>

</body>
</html>
