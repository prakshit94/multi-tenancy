<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In - {{ tenant('id') ? ucfirst(tenant('id')) : 'KrushifyAgro CRM' }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-[Outfit] bg-[#020617] text-white">

<div class="flex min-h-screen">

    <!-- LEFT SIDE (Premium Branding) -->
    <div class="hidden lg:flex w-1/2 relative items-center justify-center overflow-hidden">

        <!-- Gradient Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-purple-600 to-blue-600"></div>

        <!-- Glow -->
        <div class="absolute w-[500px] h-[500px] bg-white/10 blur-[120px] rounded-full"></div>

        <!-- Content -->
        <div class="relative z-10 text-center px-10">
            <h1 class="text-4xl font-bold mb-4">Welcome Back 👋</h1>

            <p class="text-lg text-white/80 max-w-md mx-auto">
                @if(tenant('id'))
                    Access your <span class="font-semibold">{{ ucfirst(tenant('id')) }}</span> workspace securely.
                @else
                    Manage your account with enterprise-grade security and performance.
                @endif
            </p>

            <div class="mt-10 space-y-4 text-white/70 text-sm">
                <p>✔ Secure Authentication</p>
                <p>✔ Multi Workspace Support</p>
                <p>✔ Fast & Reliable System</p>
            </div>
        </div>
    </div>


    <!-- RIGHT SIDE (LOGIN FORM) -->
    <div class="flex w-full lg:w-1/2 items-center justify-center px-6 py-12 relative">

        <!-- Background Glow -->
        <div class="absolute inset-0">
            <div class="absolute top-[-10%] left-[-10%] w-[300px] h-[300px] bg-indigo-500/20 blur-[120px] rounded-full"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[300px] h-[300px] bg-purple-500/20 blur-[120px] rounded-full"></div>
        </div>

        <!-- Notifications -->
        <div class="absolute top-6 left-1/2 -translate-x-1/2 w-full max-w-md px-4 space-y-4 z-50">
            @if (session('error'))
                <div class="rounded-xl bg-red-500/10 p-4 border border-red-500/20 backdrop-blur">
                    <p class="text-sm text-red-400">{{ session('error') }}</p>
                </div>
            @endif

            @if (session('success'))
                <div class="rounded-xl bg-green-500/10 p-4 border border-green-500/20 backdrop-blur">
                    <p class="text-sm text-green-400">{{ session('success') }}</p>
                </div>
            @endif
        </div>

        <!-- Login Card -->
        <div class="relative z-10 w-full max-w-md bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl">

            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <div class="h-14 w-14 rounded-2xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                    <span class="text-white font-bold text-lg">S</span>
                </div>
            </div>

            <h2 class="text-xl font-semibold text-center mb-1">Sign In</h2>
            <p class="text-center text-sm text-gray-400 mb-6">
                @if(tenant('id'))
                    {{ ucfirst(tenant('id')) }} workspace login
                @else
                    Access your account
                @endif
            </p>

            <form class="space-y-5" action="{{ tenant() ? request()->getSchemeAndHttpHost() . '/login' : config('app.url').'/login' }}" method="POST">
                @csrf

                <!-- Email -->
                <div>
                    <label class="text-sm text-gray-300">Email</label>
                    <input type="email" name="email"
                        value="{{ old('email', \Illuminate\Support\Facades\Cookie::get('saved_email')) }}"
                        required
                        class="mt-2 w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 focus:border-indigo-500 outline-none">
                    @error('email')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="text-sm text-gray-300">Password</label>
                    <input type="password" name="password" required
                        class="mt-2 w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 focus:border-indigo-500 outline-none">
                </div>

                <!-- Remember -->
                <div class="flex items-center">
                    <input type="checkbox" name="remember"
                        {{ \Illuminate\Support\Facades\Cookie::get('saved_email') ? 'checked' : '' }}
                        class="rounded bg-white/10 border-white/20 text-indigo-500">
                    <label class="ml-2 text-sm text-gray-400">Remember me</label>
                </div>

                <!-- Button -->
                <button type="submit"
                    class="w-full py-3 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 font-semibold hover:opacity-90 transition">
                    Sign in
                </button>
            </form>

            @if(tenant('id'))
            <p class="text-xs text-center text-gray-400 mt-6">
                Not the right workspace?
                <a href="{{ config('app.url') }}" class="text-indigo-400 hover:underline">Switch</a>
            </p>
            @endif
        </div>

        <!-- Footer -->
        <p class="absolute bottom-6 text-xs text-gray-500 text-center w-full">
            Protected by enterprise-grade security
        </p>

    </div>

</div>

</body>
</html>