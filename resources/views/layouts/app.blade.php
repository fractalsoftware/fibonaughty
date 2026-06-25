<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Fibonaughty') }} - {{ __('messages.app_tagline') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles / Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js / Livewire Styles -->
    @livewireStyles

    <!-- Custom Premium Styles -->
    <style>
        [x-cloak] { display: none !important; }
        
        /* Premium Background Glows */
        .neon-glow-primary {
            box-shadow: 0 0 25px -5px rgba(124, 58, 237, 0.4);
        }
        .neon-glow-success {
            box-shadow: 0 0 25px -5px rgba(16, 185, 129, 0.4);
        }
        .neon-border-primary {
            border-color: rgba(124, 58, 237, 0.4);
        }
        .neon-text-primary {
            text-shadow: 0 0 10px rgba(139, 92, 246, 0.6);
        }
        
        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #090e17;
        }
        ::-webkit-scrollbar-thumb {
            background: #1f2937;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #374151;
        }
    </style>
</head>
<body class="h-full bg-[#0a0e17] text-gray-100 font-sans antialiased selection:bg-purple-600/30 selection:text-purple-200">
    <!-- Immersive Backdrop Noise / Gradients -->
    <div class="fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-[40%] -left-[20%] w-[80%] h-[80%] rounded-full bg-purple-900/10 blur-[130px]"></div>
        <div class="absolute -bottom-[30%] -right-[10%] w-[60%] h-[70%] rounded-full bg-emerald-900/10 blur-[120px]"></div>
    </div>

    <div class="min-h-full flex flex-col">
        <!-- Top Sticky Header Navigation -->
        <header class="sticky top-0 z-40 w-full border-b border-gray-800/60 bg-[#0a0e17]/80 backdrop-blur-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Brand Logo -->
                    <div class="flex items-center gap-2">
                        <a href="{{ route('login') }}" class="flex items-center gap-2 group">
                            <span class="text-2xl font-black font-outfit bg-clip-text text-transparent bg-gradient-to-r from-purple-400 via-indigo-400 to-emerald-400 tracking-tight transition duration-300 group-hover:scale-105">
                                Fibonaughty
                            </span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-bold bg-purple-950/40 text-purple-400 border border-purple-800/50 group-hover:border-purple-600 transition">
                                v1.0
                            </span>
                        </a>
                    </div>

                    <!-- Right Controls (Language & User Profiles) -->
                    <div class="flex items-center gap-4">
                        <!-- Language Switcher Dropdown (Alpine-driven) -->
                        <div x-data="{ open: false }" class="relative" @click.away="open = false">
                            <button @click="open = !open" class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-800 bg-gray-900/40 text-gray-300 hover:text-white hover:border-gray-700 transition">
                                <span>🌐 {{ strtoupper(app()->getLocale()) }}</span>
                                <svg class="w-3 h-3 transition duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-32 origin-top-right rounded-xl border border-gray-800/80 bg-[#0c121e] p-1 shadow-2xl focus:outline-none">
                                <a href="{{ route('locale.set', ['lang' => 'en']) }}" class="flex w-full items-center px-3 py-2 text-xs font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 transition {{ app()->getLocale() === 'en' ? 'bg-purple-950/20 text-purple-400 font-bold border border-purple-900/30' : '' }}">
                                    🇺🇸 English
                                </a>
                                <a href="{{ route('locale.set', ['lang' => 'es']) }}" class="flex w-full items-center px-3 py-2 text-xs font-medium rounded-lg text-gray-300 hover:text-white hover:bg-gray-800/50 transition {{ app()->getLocale() === 'es' ? 'bg-purple-950/20 text-purple-400 font-bold border border-purple-900/30' : '' }}">
                                    🇪🇸 Español
                                </a>
                            </div>
                        </div>

                        @auth
                            <!-- Authenticated Creator Profile Dropdown -->
                            <div x-data="{ open: false }" class="relative" @click.away="open = false">
                                <button @click="open = !open" class="flex items-center gap-2 p-1 rounded-full hover:bg-gray-800/30 transition focus:outline-none focus:ring-2 focus:ring-purple-500/50">
                                    @if(auth()->user()->avatar_url)
                                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full border border-purple-500/30 object-cover">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center font-bold text-xs border border-purple-500/30">
                                            {{ substr(auth()->user()->name, 0, 1) }}
                                        </div>
                                    @endif
                                </button>
                                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-56 origin-top-right rounded-2xl border border-gray-800/80 bg-[#0c121e] p-2 shadow-2xl focus:outline-none">
                                    <div class="px-3 py-2 border-b border-gray-800/60 mb-1">
                                        <p class="text-xs text-gray-400 font-medium">{{ __('messages.scrum_master') }}</p>
                                        <p class="text-sm font-bold text-white truncate">{{ auth()->user()->name }}</p>
                                        <p class="text-xs text-gray-500 truncate mt-0.5">{{ auth()->user()->email }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex w-full items-center px-3 py-2 text-xs font-semibold rounded-xl text-rose-400 hover:text-white hover:bg-rose-950/20 hover:border-rose-900/30 border border-transparent transition">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            {{ __('messages.logout') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="text-xs text-gray-400 font-medium hidden sm:block">
                                {{ __('messages.guest_sign_in_notice') }}
                            </div>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @if(isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>
