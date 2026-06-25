@extends('layouts.app')

@section('content')
    <div class="flex-1 flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative font-sans">
        <!-- Floating decorative glowing background blur circles -->
        <div class="absolute top-[20%] left-[25%] w-72 h-72 rounded-full bg-purple-600/10 blur-[100px] -z-10 animate-pulse duration-3000"></div>
        <div class="absolute bottom-[20%] right-[25%] w-72 h-72 rounded-full bg-emerald-600/10 blur-[100px] -z-10 animate-pulse duration-3000"></div>

        <div class="max-w-md w-full text-center space-y-8">
            <!-- Branding Logo & Hero Title -->
            <div class="space-y-4">
                <div class="inline-flex items-center justify-center p-4 rounded-3xl bg-gradient-to-br from-purple-950/40 to-indigo-950/40 border border-purple-800/40 shadow-2xl hover:scale-105 transition duration-300">
                    <span class="text-5xl">🃏</span>
                </div>
                <h1 class="text-4xl sm:text-5xl font-black font-outfit tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-purple-400 via-indigo-300 to-emerald-400">
                    Fibonaughty
                </h1>
                <p class="text-base sm:text-lg text-gray-400 font-medium max-w-sm mx-auto leading-relaxed">
                    {{ __('messages.app_tagline') }}
                </p>
            </div>

            <!-- Login Portal Glassmorphic Box -->
            <div class="rounded-3xl border border-gray-800/80 bg-gray-900/10 p-6 sm:p-10 backdrop-blur-md shadow-2xl relative overflow-hidden">
                <div class="absolute inset-0 bg-grid-white/[0.01] -z-10"></div>
                <div class="space-y-6">
                    <div>
                        <h2 class="text-xl font-bold font-outfit text-white">
                            {{ __('messages.login_title') }}
                        </h2>
                        <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                            {{ __('messages.login_subtitle') }}
                        </p>
                    </div>

                    <!-- Social Sign-in Buttons Stack -->
                    <div class="space-y-3.5 pt-4">
                        <!-- GitHub Login Button -->
                        <a 
                            href="{{ route('oauth.redirect', ['provider' => 'github']) }}" 
                            class="flex items-center justify-center gap-3 w-full px-5 py-3 text-sm font-semibold rounded-xl text-white bg-gray-950 hover:bg-gray-900 border border-gray-800 hover:border-gray-700 transition hover:scale-[1.01] duration-150 shadow-lg"
                        >
                            <!-- GitHub Custom SVG Icon -->
                            <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12c0 4.42 2.865 8.17 6.839 9.49.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.464-1.11-1.464-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.831.092-.646.401-1.088.738-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.579.688.481C19.137 20.167 22 16.418 22 12c0-5.523-4.477-10-10-10z" />
                            </svg>
                            <span>{{ __('messages.sign_in_with', ['provider' => 'GitHub']) }}</span>
                        </a>

                        <!-- Google Login Button -->
                        <a 
                            href="{{ route('oauth.redirect', ['provider' => 'google']) }}" 
                            class="flex items-center justify-center gap-3 w-full px-5 py-3 text-sm font-semibold rounded-xl text-gray-900 bg-white hover:bg-gray-50 border border-gray-200 transition hover:scale-[1.01] duration-150 shadow-lg"
                        >
                            <!-- Google Custom SVG Icon -->
                            <svg class="w-5 h-5" viewBox="0 0 24 24">
                                <path fill="#EA4335" d="M12.24 10.285V14.4h6.887c-.648 2.41-2.519 4.114-5.136 4.114-3.41 0-6.19-2.78-6.19-6.19 0-3.41 2.78-6.19 6.19-6.19 1.55 0 2.96.57 4.05 1.51l3.05-3.05C19.23 2.68 15.93 1.5 12.24 1.5 6.03 1.5 1 6.53 1 12.74c0 6.21 5.03 11.24 11.24 11.24 6.47 0 10.75-4.54 10.75-10.96 0-.74-.08-1.3-.2-1.74h-10.55z"/>
                            </svg>
                            <span>{{ __('messages.sign_in_with', ['provider' => 'Google']) }}</span>
                        </a>

                        <!-- Apple Login Button -->
                        <a 
                            href="{{ route('oauth.redirect', ['provider' => 'apple']) }}" 
                            class="flex items-center justify-center gap-3 w-full px-5 py-3 text-sm font-semibold rounded-xl text-white bg-black hover:bg-neutral-900 border border-neutral-800 transition hover:scale-[1.01] duration-150 shadow-lg"
                        >
                            <!-- Apple Custom SVG Icon -->
                            <svg class="w-4.5 h-4.5 fill-current" viewBox="0 0 18 18">
                                <path d="M15.56 10.1c-.04-2.23 1.82-3.3 1.9-3.35-1.04-1.52-2.63-1.72-3.2-1.77-1.36-.14-2.66.8-3.35.8-.7 0-1.77-.79-2.9-.77-1.48.02-2.85.86-3.61 2.18-1.54 2.67-.4 6.62 1.1 9.07.73 1.05 1.59 2.23 2.72 2.19 1.09-.04 1.5-.7 2.82-.7 1.3 0 1.68.7 2.81.67 1.15-.02 1.92-1.07 2.62-2.11.82-1.2 1.16-2.35 1.18-2.41-.03-.01-2.27-.87-2.29-3.48zM12.98 2.64c.6-.74 1.01-1.76.9-2.78-.88.04-1.95.59-2.58 1.33-.56.65-.98 1.69-.87 2.69.98.08 1.95-.5 2.55-1.24z"/>
                            </svg>
                            <span>{{ __('messages.sign_in_with', ['provider' => 'Apple']) }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footnote Guest Warning Info -->
            <p class="text-xs text-gray-500 max-w-sm mx-auto leading-relaxed">
                🚪 <strong>{{ __('messages.guest_sign_in_notice') }}</strong>
            </p>
        </div>
    </div>
@endsection

