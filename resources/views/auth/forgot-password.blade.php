<x-layouts.app title="Forgot Password - Tradexy">
    <section class="max-w-7xl mx-auto px-6 py-12">
        <div class="max-w-md mx-auto bg-base-100 border border-base-300 rounded-2xl p-6 md:p-8 shadow-sm">
            <h1 class="text-2xl font-bold">Forgot your password?</h1>
            <p class="mt-2 text-sm text-base-content/70">
                Enter your email and we will send a reset link if your account exists.
            </p>

            @if (session('status'))
                <div class="alert alert-success mt-4">
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form class="mt-6 space-y-4" method="POST" action="{{ route('password.email') }}">
                @csrf
                <div>
                    <label for="email" class="label">
                        <span class="label-text">Email address</span>
                    </label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                        value="{{ old('email') }}"
                        class="input input-bordered w-full @error('email') input-error @enderror"
                        placeholder="you@example.com" />
                    @error('email')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    Send reset link
                </button>
            </form>

            <div class="mt-4 text-sm text-center">
                <a href="{{ route('login') }}" class="link link-hover">Back to sign in</a>
            </div>
        </div>
    </section>
</x-layouts.app>