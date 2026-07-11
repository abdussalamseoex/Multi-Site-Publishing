<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- Bio Section --}}
        <div>
            <x-input-label for="bio" :value="__('Bio / About Me')" />
            <textarea id="bio" name="bio" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Write a short author bio...">{{ old('bio', $user->bio ?? '') }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bio')" />
        </div>

        {{-- Website & Social Links --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
            <div>
                <x-input-label for="website" :value="__('Website URL')" />
                <x-text-input id="website" name="website" type="url" class="mt-1 block w-full" :value="old('website', $user->website ?? '')" placeholder="https://yourwebsite.com" />
                <x-input-error class="mt-2" :messages="$errors->get('website')" />
            </div>

            <div>
                <x-input-label for="twitter" :value="__('Twitter / X URL')" />
                <x-text-input id="twitter" name="twitter" type="text" class="mt-1 block w-full" :value="old('twitter', $user->twitter ?? '')" placeholder="https://twitter.com/username" />
                <x-input-error class="mt-2" :messages="$errors->get('twitter')" />
            </div>

            <div>
                <x-input-label for="facebook" :value="__('Facebook URL')" />
                <x-text-input id="facebook" name="facebook" type="text" class="mt-1 block w-full" :value="old('facebook', $user->facebook ?? '')" placeholder="https://facebook.com/username" />
                <x-input-error class="mt-2" :messages="$errors->get('facebook')" />
            </div>

            <div>
                <x-input-label for="linkedin" :value="__('LinkedIn URL')" />
                <x-text-input id="linkedin" name="linkedin" type="text" class="mt-1 block w-full" :value="old('linkedin', $user->linkedin ?? '')" placeholder="https://linkedin.com/in/username" />
                <x-input-error class="mt-2" :messages="$errors->get('linkedin')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

