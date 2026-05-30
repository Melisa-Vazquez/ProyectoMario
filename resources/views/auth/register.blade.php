<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta — Gestión Ágil</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm">

        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-600/20 border border-indigo-500/30 rounded-2xl mb-4">
                <span class="text-2xl">🚀</span>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Gestión Ágil</h1>
            <p class="text-slate-500 text-sm mt-1">Crea tu cuenta para unirte al proyecto</p>
        </div>

        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-8 shadow-2xl">

            @if ($errors->hasAny(['name', 'password']))
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-xl px-4 py-3 mb-5 space-y-1">
                    @foreach ($errors->get('name') as $e)<p>⚠ {{ $e }}</p>@endforeach
                    @foreach ($errors->get('password') as $e)<p>⚠ {{ $e }}</p>@endforeach
                </div>
            @endif

            <form method="POST" action="/register">
                @csrf

                {{-- Nombre --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                        Nombre completo
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-indigo-500 transition placeholder:text-slate-600"
                        placeholder="Ana García">
                </div>

                {{-- Correo --}}
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                        Correo electrónico
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full bg-slate-900 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none transition placeholder:text-slate-600
                               {{ $errors->has('email') ? 'border-2 border-red-500 focus:border-red-400' : 'border border-slate-700 focus:border-indigo-500' }}"
                        placeholder="tu@correo.com">
                    @error('email')
                        <p class="text-red-400 text-xs mt-1.5">⚠ {{ $message }}</p>
                    @enderror
                </div>

                {{-- Contraseña --}}
                <div class="mb-2">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                        Contraseña
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="reg-password" required
                            oninput="checkStrength(this.value)"
                            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 pr-16 text-white text-sm focus:outline-none focus:border-indigo-500 transition placeholder:text-slate-600"
                            placeholder="Mínimo 8 caracteres">
                        <button type="button" onclick="togglePassword('reg-password', 'reg-eye')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition text-xs font-bold select-none"
                            id="reg-eye">VER</button>
                    </div>
                </div>

                {{-- Requisitos de contraseña --}}
                <div class="mb-4 bg-slate-900/60 border border-slate-700/60 rounded-xl p-3 space-y-1">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Requisitos</p>
                    <p id="req-length"  class="text-xs text-slate-600 flex items-center gap-1.5"><span>○</span> Mínimo 8 caracteres</p>
                    <p id="req-upper"   class="text-xs text-slate-600 flex items-center gap-1.5"><span>○</span> Al menos una mayúscula (A-Z)</p>
                    <p id="req-lower"   class="text-xs text-slate-600 flex items-center gap-1.5"><span>○</span> Al menos una minúscula (a-z)</p>
                    <p id="req-number"  class="text-xs text-slate-600 flex items-center gap-1.5"><span>○</span> Al menos un número (0-9)</p>
                    <p id="req-symbol"  class="text-xs text-slate-600 flex items-center gap-1.5"><span>○</span> Al menos un símbolo (@$!%*?&._-#)</p>
                </div>

                {{-- Confirmar contraseña --}}
                <div class="mb-6">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">
                        Confirmar contraseña
                    </label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="reg-confirm" required
                            class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 pr-16 text-white text-sm focus:outline-none focus:border-indigo-500 transition placeholder:text-slate-600"
                            placeholder="Repite tu contraseña">
                        <button type="button" onclick="togglePassword('reg-confirm', 'reg-eye2')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 transition text-xs font-bold select-none"
                            id="reg-eye2">VER</button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 rounded-xl transition shadow-lg shadow-indigo-600/20 text-sm">
                    Crear cuenta
                </button>
            </form>

        </div>

        <p class="text-center text-sm text-slate-500 mt-6">
            ¿Ya tienes cuenta?
            <a href="/login" class="text-indigo-400 hover:text-indigo-300 font-semibold transition">Inicia sesión</a>
        </p>

    </div>

    <script>
        function togglePassword(inputId, btnId) {
            const input = document.getElementById(inputId);
            const btn   = document.getElementById(btnId);
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerText = 'OCULTAR';
            } else {
                input.type = 'password';
                btn.innerText = 'VER';
            }
        }

        function checkStrength(value) {
            const rules = [
                { id: 'req-length', ok: value.length >= 8 },
                { id: 'req-upper',  ok: /[A-Z]/.test(value) },
                { id: 'req-lower',  ok: /[a-z]/.test(value) },
                { id: 'req-number', ok: /[0-9]/.test(value) },
                { id: 'req-symbol', ok: /[@$!%*?&._\-#]/.test(value) },
            ];
            rules.forEach(r => {
                const el = document.getElementById(r.id);
                if (r.ok) {
                    el.className = 'text-xs text-emerald-400 flex items-center gap-1.5';
                    el.querySelector('span').innerText = '✓';
                } else {
                    el.className = 'text-xs text-slate-600 flex items-center gap-1.5';
                    el.querySelector('span').innerText = '○';
                }
            });
        }
    </script>

</body>
</html>
