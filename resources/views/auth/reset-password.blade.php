<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Rotikaya Media</title>
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
            <h2 class="text-2xl font-display font-semibold text-white mb-6">Reset Password</h2>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-500/30 bg-red-950/40 px-4 py-3 text-sm text-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $email) }}"
                    placeholder="Email address"
                    required
                    class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/40 transition"
                >

                <input
                    type="password"
                    name="password"
                    placeholder="New password"
                    required
                    class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/40 transition"
                >

                <input
                    type="password"
                    name="password_confirmation"
                    placeholder="Confirm new password"
                    required
                    class="w-full bg-white/[0.02] border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500/40 transition"
                >

                <button
                    type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-lg transition focus:outline-none focus:ring-2 focus:ring-red-500/40 focus:ring-offset-2 focus:ring-offset-[#141414]"
                >
                    Reset Password
                </button>
            </form>
        </div>
    </div>
</body>
</html>
