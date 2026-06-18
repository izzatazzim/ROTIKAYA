<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Rotikaya Media</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0a0a0a] min-h-screen flex items-center justify-center font-sans">
    <div class="w-full max-w-md px-4">
        <div class="text-center mb-8">
            <img src="{{ asset('images/rotikaya-logo.png') }}" alt="Rotikaya Media"
                 class="mx-auto h-12 w-auto object-contain">
        </div>

        <div class="bg-[#141414] border border-white/[0.06] rounded-xl p-8">
            <h2 class="text-2xl font-display font-semibold text-white mb-3">Forgot Password</h2>
            <p class="text-sm text-gray-300 mb-6">Enter your email to receive a password reset link.</p>

            @if (session('success'))
                <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-950/40 px-4 py-3 text-sm text-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-500/30 bg-red-950/40 px-4 py-3 text-sm text-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Email address"
                    required
                    class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/40 transition"
                >
                <button
                    type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-red-500/40 focus:ring-offset-2 focus:ring-offset-[#141414]"
                >
                    Email Reset Link
                </button>
            </form>

            <a href="{{ route('login') }}" class="mt-4 inline-block text-sm text-red-300 hover:text-red-200 transition">
                Back to sign in
            </a>
        </div>
    </div>
</body>
</html>
