<x-guest-layout>
        <div class="w-full sm:max-w-md px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <!-- Logo/Brand -->
            <div class="mb-8 text-center">
                <h2 class="text-2xl font-semibold text-gray-800">Selamat Datang</h2>
                <p class="mt-2 text-sm text-gray-600">Silakan masuk dengan akun Anda</p>
            </div>

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- NRP -->
                <div>
                    <x-input-label for="nrp" :value="'NRP'" class="text-gray-700 font-medium" />
                    <x-text-input 
                        id="nrp" 
                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" 
                        type="text" 
                        name="nrp" 
                        :value="old('nrp')" 
                        required 
                        autofocus 
                        placeholder="Masukkan NRP"
                    />
                    <x-input-error :messages="$errors->get('nrp')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="'Kata Sandi'" class="text-gray-700 font-medium" />
                    <x-text-input 
                        id="password" 
                        class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        type="password"
                        name="password"
                        required
                        placeholder="••••••••"
                    />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between mt-4">
                    <label for="remember_me" class="inline-flex items-center">
                        <input 
                            id="remember_me" 
                            type="checkbox" 
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" 
                            name="remember"
                        >
                        <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="text-sm text-indigo-600 hover:text-indigo-800 transition-colors" href="{{ route('password.request') }}">
                            Lupa password?
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                    <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>