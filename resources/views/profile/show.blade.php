<x-layouts.app title="Profile - Tradexy">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between my-4">
            <div>
                <x-page-title title="Profile" subtitle="Manage your account settings and preferences" />
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success mt-4">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="flex flex-col gap-8 my-8">
            <!-- Profile Picture Section -->
            <div class="bg-gray-100 rounded-lg p-8 flex items-center justify-between gap-6">
                <div class="flex items-center gap-6">
                    <div
                        class="w-24 h-24 rounded-full bg-primary text-white flex items-center justify-center text-3xl font-bold overflow-hidden shadow-sm">
                        @if (Auth::user()->profile_picture)
                            <img src="{{ Auth::user()->profile_picture }}" alt="{{ Auth::user()->name }}"
                                class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        @endif
                    </div>
                    <div>
                        <p class="text-xl font-bold">{{ Auth::user()->name }}</p>
                        <p class="text-sm text-gray-500">JPG, PNG, GIF. Max 2MB</p>
                    </div>
                </div>
                <div>
                    @if (Auth::user()->profile_picture)
                        <form action="{{ route('profile.remove-picture') }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-error text-white">Remove Picture</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-8">
                <!-- Profile Information Form -->
                <form id="form" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
                    class="bg-gray-100 rounded-lg p-8 w-full md:w-1/2">
                    @csrf
                    @method('PUT')
                    <div class="flex items-center gap-3 text-gray-800 mb-6 border-b border-gray-200 pb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6 text-primary">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        <h2 class="text-xl font-bold uppercase tracking-wider">Profile Information</h2>
                    </div>

                    <div class="flex flex-col gap-6">
                        <div>
                            <fieldset class="fieldset w-full">
                                <legend
                                    class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                    Name</legend>
                                <input type="text" id="name" name="name" value="{{ old('name', Auth::user()->name) }}"
                                    class="input w-full @error('name') input-error @enderror" required>
                                @error('name')
                                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </fieldset>
                        </div>

                        <div>
                            <fieldset class="fieldset w-full">
                                <legend
                                    class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                    Email</legend>
                                <input type="email" id="email" name="email"
                                    value="{{ old('email', Auth::user()->email) }}"
                                    class="input w-full @error('email') input-error @enderror" required>
                                @error('email')
                                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </fieldset>
                        </div>

                        <div>
                            <fieldset class="fieldset w-full">
                                <legend
                                    class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                    Upload New Picture</legend>
                                <input type="file" id="profile_picture" name="profile_picture" accept="image/*"
                                    class="file-input file-input-bordered w-full @error('profile_picture') file-input-error @enderror">
                                @error('profile_picture')
                                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </fieldset>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="btn btn-primary w-full md:w-auto">Save Profile Info</button>
                    </div>
                </form>

                <!-- Change Password Form -->
                <form action="{{ route('profile.change-password') }}" method="POST"
                    class="dirty-check bg-gray-100 rounded-lg p-8 w-full md:w-1/2">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center gap-3 text-gray-800 mb-6 border-b border-gray-200 pb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-6 text-primary">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                        </svg>
                        <h2 class="text-xl font-bold uppercase tracking-wider">Change Password</h2>
                    </div>

                    <div class="flex flex-col gap-6">
                        <div>
                            <fieldset class="fieldset w-full">
                                <legend
                                    class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                    Current Password</legend>
                                <input type="password" id="current_password" name="current_password"
                                    class="input w-full @error('current_password') input-error @enderror" required>
                                @error('current_password')
                                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </fieldset>
                        </div>

                        <div class="border-t border-gray-200 my-2"></div>

                        <div>
                            <fieldset class="fieldset w-full">
                                <legend
                                    class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                    New Password</legend>
                                <input type="password" id="new_password" name="new_password"
                                    class="input w-full @error('new_password') input-error @enderror" required>
                                @error('new_password')
                                    <p class="text-error text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </fieldset>
                        </div>

                        <div>
                            <fieldset class="fieldset w-full">
                                <legend
                                    class="fieldset-legend uppercase font-semibold text-xs tracking-wider text-gray-500">
                                    Confirm New Password</legend>
                                <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                                    class="input w-full" required>
                            </fieldset>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="btn btn-primary w-full md:w-auto">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
@include('components.form-dirty-state-check')