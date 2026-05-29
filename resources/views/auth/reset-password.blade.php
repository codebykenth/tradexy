<x-layouts.app title="Reset Password - Tradexy">
    <section class="max-w-7xl mx-auto px-6 py-12">
        <div class="max-w-md mx-auto bg-base-100 border border-base-300 rounded-2xl p-6 md:p-8 shadow-sm">
            <h1 class="text-2xl font-bold">Set a new password</h1>
            <p class="mt-2 text-sm text-base-content/70">
                Enter your new password to finish resetting your account.
            </p>

            <form class="mt-6 space-y-4" method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="label">
                        <span class="label-text">Email address</span>
                    </label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                        value="{{ old('email', $email) }}"
                        class="input input-bordered w-full @error('email') input-error @enderror"
                        placeholder="you@example.com" />
                    @error('email')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="label">
                        <span class="label-text">New password</span>
                    </label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required
                        class="input input-bordered w-full @error('password') input-error @enderror"
                        placeholder="Enter new password" />
                    @error('password')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="label">
                        <span class="label-text">Confirm new password</span>
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                        autocomplete="new-password" required class="input input-bordered w-full"
                        placeholder="Confirm new password" />
                </div>

                <button type="submit" class="btn btn-primary w-full">
                    Reset password
                </button>
            </form>

            <div class="mt-4 text-sm text-center">
                <a href="{{ route('login') }}" class="link link-hover">Back to sign in</a>
            </div>
        </div>
    </section>
</x-layouts.app>
