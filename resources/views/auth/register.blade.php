<x-layouts.app title="Sign Up - Tradexy">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col lg:flex-row w-full">
            <!-- Hero Section (Left) -->
            <div class="hidden lg:flex w-1/2 relative bg-gray-900 rounded-2xl overflow-hidden my-4 mr-0">
                <img src="{{ asset('images/tradexy-hero-1.png') }}" alt="Trading Chart"
                    class="absolute inset-0 w-full h-full object-cover opacity-60">
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                <div class="absolute bottom-0 left-0 p-12 text-white z-10">
                    <blockquote class="text-2xl font-medium mb-4 leading-relaxed">"The market is a device for
                        transferring
                        money from the impatient to the patient."</blockquote>
                    <cite class="not-italic opacity-80 font-normal">— Warren Buffett</cite>
                </div>
            </div>

            <!-- Form Section (Right) -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-8 lg:p-16">
                <div class="w-full max-w-md space-y-8">
                    <div class="text-center lg:text-left">
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Create an account
                        </h1>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Start journaling your trades today. <a href="{{ route('login') }}"
                                class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">Already
                                have an
                                account?</a>
                        </p>
                    </div>

                    <!-- Social Login -->
                    <div class="grid grid-cols-2 gap-4">
                        <a href="/auth/google"
                            class="flex items-center justify-center gap-2 px-4 p-2.5 border border-gray-200 dark:border-[#3E3E3A] rounded-lg hover:bg-gray-50 dark:hover:bg-[#1a1a19] transition-all bg-white dark:bg-[#161615] text-sm font-medium dark:text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
                                <path
                                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"
                                    fill="#4285F4" />
                                <path
                                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                                    fill="#34A853" />
                                <path
                                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18A11.96 11.96 0 0 0 0 12c0 1.94.46 3.77 1.28 5.4l3.56-2.77.01-.54z"
                                    fill="#FBBC05" />
                                <path
                                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                                    fill="#EA4335" />
                            </svg>
                            Google
                        </a>
                        <a href="/auth/facebook"
                            class="flex items-center justify-center gap-2 px-4 p-2.5 border border-gray-200 dark:border-[#3E3E3A] rounded-lg hover:bg-gray-50 dark:hover:bg-[#1a1a19] transition-all bg-white dark:bg-[#161615] text-sm font-medium dark:text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                                fill="#1877F2">
                                <path
                                    d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                            </svg>
                            Facebook
                        </a>
                    </div>

                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200 dark:border-gray-800"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="bg-[#FDFDFC] dark:bg-[#0a0a0a] px-2 text-gray-500">Or register with
                                email</span>
                        </div>
                    </div>

                    {{-- Error Banner --}}
                    @if ($errors->any())
                        <div
                            class="p-4 rounded-lg bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/20">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">There were errors with
                                        your
                                        submission</h3>
                                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                        <ul class="list-disc pl-5 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="/register" method="post" class="space-y-6">
                        @csrf
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-900 dark:text-gray-200">Full
                                Name</label>
                            <div class="mt-2">
                                <input type="text" name="name" id="name" autocomplete="name" placeholder="John Doe"
                                    value="{{ old('name') }}"
                                    class="block w-full rounded-md border-0 p-2.5 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-900 dark:focus:ring-white sm:text-sm sm:leading-6 dark:bg-[#161615] @error('name') ring-red-500 @enderror">
                            </div>
                            @error('name')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-900 dark:text-gray-200">Email
                                address</label>
                            <div class="mt-2">
                                <input id="email" name="email" type="email" autocomplete="email"
                                    placeholder="you@example.com" value="{{ old('email') }}"
                                    class="block w-full rounded-md border-0 p-2.5 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-900 dark:focus:ring-white sm:text-sm sm:leading-6 dark:bg-[#161615] @error('email') ring-red-500 @enderror">
                            </div>
                            @error('email')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password"
                                class="block text-sm font-medium text-gray-900 dark:text-gray-200">Password</label>
                            <div class="mt-2">
                                <input id="password" name="password" type="password" autocomplete="new-password"
                                    placeholder="Min. 8 characters"
                                    class="block w-full rounded-md border-0 p-2.5 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-900 dark:focus:ring-white sm:text-sm sm:leading-6 dark:bg-[#161615] @error('password') ring-red-500 @enderror">
                            </div>
                            @error('password')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation"
                                class="block text-sm font-medium text-gray-900 dark:text-gray-200">Confirm
                                Password</label>
                            <div class="mt-2">
                                <input id="password_confirmation" name="password_confirmation" type="password"
                                    autocomplete="new-password" placeholder="Repeat Password"
                                    class="block w-full rounded-md border-0 p-2.5 text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-gray-900 dark:focus:ring-white sm:text-sm sm:leading-6 dark:bg-[#161615] @error('password_confirmation') ring-red-500 @enderror">
                            </div>
                            @error('password_confirmation')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Terms -->
                        <div class="flex items-start">
                            <div class="flex h-6 items-center">
                                <input id="terms" name="terms" type="checkbox" {{ old('terms') ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-900 dark:border-gray-700 dark:bg-[#161615] dark:focus:ring-white">
                            </div>
                            <div class="ml-3 text-sm leading-6">
                                <label for="terms" class="font-medium text-gray-900 dark:text-gray-300">I agree to the
                                    <a href="/terms-and-conditions"
                                        class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">Terms
                                        of
                                        Service</a> and <a href="/privacy-policy"
                                        class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">Privacy
                                        Policy</a></label>
                            </div>
                        </div>

                        <div>
                            <button type="submit"
                                class="flex w-full justify-center rounded-md bg-gray-900 px-3 p-2.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-gray-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-900 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 transition-colors">
                                Create Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>