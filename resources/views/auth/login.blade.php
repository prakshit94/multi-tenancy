<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In - {{ tenant('id') ? ucfirst(tenant('id')) : 'KrushifyAgro CRM' }}</title>

    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full font-[Outfit] bg-[#020617] text-white overflow-hidden">

<div class="flex min-h-screen">

    <!-- LEFT SIDE (ENTERPRISE AGRI BRANDING) -->
    <div class="hidden lg:flex w-1/2 relative items-center justify-center overflow-hidden">

        <!-- Premium Gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-green-900 via-emerald-700 to-lime-600"></div>

        <!-- Multi Glow System -->
        <div class="absolute w-[700px] h-[700px] bg-lime-300/20 blur-[180px] rounded-full"></div>
        <div class="absolute w-[500px] h-[500px] bg-emerald-400/20 blur-[160px] rounded-full animate-pulse"></div>

        <!-- Texture -->
        <div class="absolute inset-0 opacity-[0.05] bg-[url('https://www.transparenttextures.com/patterns/leaf.png')]"></div>

        <!-- Content -->
        <div class="relative z-10 px-12 text-white max-w-xl">

            <!-- Brand -->
            <h1 class="text-5xl font-extrabold tracking-tight mb-6 leading-tight">
                Krushify Agro 🌿
            </h1>

            <!-- Tagline -->
            <p class="text-lg text-white/90 leading-relaxed">
                Unified Agriculture CRM platform to manage products, farmers, inventory and distribution — all in one place.
            </p>

            <!-- Tenant -->
            <p class="mt-5 text-sm text-white/70">
                @if(tenant('id'))
                    Managing <span class="font-semibold text-white">{{ ucfirst(tenant('id')) }}</span> workspace with precision.
                @else
                    Built for modern agri-business operations.
                @endif
            </p>

            <!-- Product Cards -->
            <div class="mt-12 grid grid-cols-2 gap-4 text-sm">

                <div class="bg-white/5 border border-white/10 rounded-xl p-4 backdrop-blur hover:bg-white/10 transition">
                    🛡️ Crop Protection
                </div>

                <div class="bg-white/5 border border-white/10 rounded-xl p-4 backdrop-blur hover:bg-white/10 transition">
                    🌱 Seeds Management
                </div>

                <div class="bg-white/5 border border-white/10 rounded-xl p-4 backdrop-blur hover:bg-white/10 transition">
                    🧪 Fertilizers & Nutrients
                </div>

                <div class="bg-white/5 border border-white/10 rounded-xl p-4 backdrop-blur hover:bg-white/10 transition">
                    ⚙️ Agri Hardware
                </div>

            </div>

            <!-- Extra Features -->
            <div class="mt-10 grid grid-cols-2 gap-3 text-xs text-white/70">
                <div>📦 Smart Inventory</div>
                <div>🚚 Distribution</div>
                <div>👨‍🌾 Farmer CRM</div>
                <div>📊 Sales Insights</div>
            </div>

            <!-- Footer -->
            <div class="mt-12 text-xs text-white/60 tracking-widest uppercase">
                From Seed to Market • Digitally Powered 🌾
            </div>

        </div>
    </div>


    <!-- RIGHT SIDE (LOGIN - PREMIUM GLASS UI) -->
    <div class="flex w-full lg:w-1/2 items-center justify-center px-6 py-12 relative">

        <!-- Background Glow -->
        <div class="absolute inset-0">
            <div class="absolute top-[-10%] left-[-10%] w-[400px] h-[400px] bg-green-500/20 blur-[150px] rounded-full"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[400px] h-[400px] bg-lime-500/20 blur-[150px] rounded-full"></div>
        </div>

        <!-- Notifications -->
        <div class="absolute top-6 left-1/2 -translate-x-1/2 w-full max-w-md px-4 space-y-4 z-50">
            @if (session('error'))
                <div class="rounded-2xl bg-red-500/10 p-4 border border-red-500/20 backdrop-blur-xl">
                    <p class="text-sm text-red-400">{{ session('error') }}</p>
                </div>
            @endif

            @if (session('success'))
                <div class="rounded-2xl bg-green-500/10 p-4 border border-green-500/20 backdrop-blur-xl">
                    <p class="text-sm text-green-400">{{ session('success') }}</p>
                </div>
            @endif
        </div>

        <!-- Card -->
        <div class="relative z-10 w-full max-w-md 
                    bg-white/[0.07] backdrop-blur-2xl 
                    border border-white/10 
                    rounded-3xl p-8 
                    shadow-[0_30px_100px_rgba(0,0,0,0.7)]">

            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <div class="h-16 w-16 rounded-2xl bg-gradient-to-tr from-green-500 to-lime-500 flex items-center justify-center shadow-xl shadow-green-500/40">
                    <span class="text-xl">🌿</span>
                </div>
            </div>

            <!-- Title -->
            <h2 class="text-2xl font-semibold text-center mb-1">Welcome Back</h2>

            <p class="text-center text-sm text-gray-400 mb-6">
                @if(tenant('id'))
                    {{ ucfirst(tenant('id')) }} workspace login
                @else
                    Access your Krushify Agro account
                @endif
            </p>

            <!-- Form -->
            <form class="space-y-5" action="{{ tenant() ? request()->getSchemeAndHttpHost() . '/login' : config('app.url').'/login' }}" method="POST">
                @csrf

                <!-- Email -->
                <div>
                    <label class="text-sm text-gray-300">Email</label>
                    <input type="email" name="email"
                        value="{{ old('email', \Illuminate\Support\Facades\Cookie::get('saved_email')) }}"
                        required
                        class="mt-2 w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 focus:border-lime-400 focus:ring-2 focus:ring-lime-400/20 outline-none transition">
                    @error('email')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="text-sm text-gray-300">Password</label>
                    <input type="password" name="password" required
                        class="mt-2 w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 focus:border-lime-400 focus:ring-2 focus:ring-lime-400/20 outline-none transition">
                </div>

                <!-- Remember -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-400">
                        <input type="checkbox" name="remember"
                            {{ \Illuminate\Support\Facades\Cookie::get('saved_email') ? 'checked' : '' }}
                            class="rounded bg-white/10 border-white/20 text-lime-500">
                        Remember me
                    </label>
                </div>

                <!-- Button -->
                <button type="submit"
                    class="w-full py-3 rounded-xl bg-gradient-to-r from-green-500 via-emerald-500 to-lime-500 font-semibold shadow-lg shadow-green-500/40 hover:scale-[1.03] active:scale-95 transition-all">
                    Sign in
                </button>
            </form>

            <!-- Switch -->
            @if(tenant('id'))
            <p class="text-xs text-center text-gray-400 mt-6">
                Not the right workspace?
                <a href="{{ config('app.url') }}" class="text-lime-400 hover:underline">Switch</a>
            </p>
            @endif
        </div>

        <!-- Footer -->
        <p class="absolute bottom-6 text-xs text-gray-500 text-center w-full">
            Secure • Scalable • Built for Agriculture 🌱
        </p>

    </div>

</div>

</body>
</html>