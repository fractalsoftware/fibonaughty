<div class="space-y-10 py-4 font-sans">
    <!-- Header Greeting Card (Glassmorphic Banner) -->
    <div class="relative overflow-hidden rounded-3xl border border-gray-800/60 bg-gradient-to-r from-purple-950/10 via-gray-900/40 to-emerald-950/10 p-6 sm:p-8 backdrop-blur-md">
        <div class="absolute inset-0 bg-grid-white/[0.02] -z-10"></div>
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold font-outfit text-white tracking-tight">
                    {{ __('messages.welcome_back', ['name' => auth()->user()->name]) }}
                </h1>
                <p class="text-sm text-gray-400 mt-1 max-w-xl">
                    {{ __('messages.app_tagline') }} {{ __('messages.login_subtitle') }}
                </p>
            </div>
            <!-- Witty PM Quote banner -->
            <div class="px-4 py-3 rounded-xl bg-purple-950/20 border border-purple-900/30 max-w-sm">
                <span class="text-xs font-bold text-purple-400 uppercase tracking-wider block">🗣️ Backlog Reality Check</span>
                <p class="text-xs text-purple-300/90 italic mt-1 leading-relaxed">
                    "{{ __('messages.compiling_consensus') }}"
                </p>
            </div>
        </div>
    </div>

    <!-- Main Grid: Create Room & Active Rooms -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Create Room Form Card -->
        <div class="lg:col-span-5 rounded-3xl border border-gray-800/60 bg-gray-900/20 p-6 sm:p-8 backdrop-blur-md relative">
            <div class="absolute top-0 right-10 w-24 h-24 bg-purple-500/5 rounded-full blur-2xl -z-10"></div>
            <h2 class="text-xl font-bold font-outfit text-white tracking-tight flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-purple-950/40 text-purple-400 border border-purple-800/50">🛠️</span>
                {{ __('messages.create_room') }}
            </h2>

            <form wire:submit.prevent="createNewSession" class="mt-6 space-y-6">
                <!-- Room Name Input -->
                <div class="space-y-2">
                    <label for="roomName" class="text-xs font-bold uppercase tracking-wider text-gray-400">
                        {{ __('messages.room_name') }}
                    </label>
                    <input 
                        type="text" 
                        id="roomName" 
                        wire:model="roomName" 
                        placeholder="{{ __('messages.display_name_placeholder') }}" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-800 bg-gray-950/60 text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-500/50 transition duration-200"
                    >
                    @error('roomName')
                        <span class="text-xs font-semibold text-rose-400 block mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Deck Type Selector -->
                <div class="space-y-3">
                    <label class="text-xs font-bold uppercase tracking-wider text-gray-400 block">
                        {{ __('messages.deck_type') }}
                    </label>
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Fibonacci Option Card -->
                        <label class="relative flex p-4 rounded-xl border cursor-pointer transition focus:outline-none {{ $deckType === 'fibonacci' ? 'bg-purple-950/20 border-purple-500/50 shadow-[0_0_15px_-3px_rgba(139,92,246,0.25)]' : 'bg-gray-950/40 border-gray-800 hover:border-gray-700' }}">
                            <input type="radio" wire:model.live="deckType" value="fibonacci" class="sr-only">
                            <div class="flex items-start gap-3">
                                <span class="text-2xl mt-0.5">🔢</span>
                                <div>
                                    <span class="block text-sm font-bold text-white">{{ __('messages.fibonacci') }}</span>
                                    <span class="block text-xs text-gray-500 mt-1">Cards: 0, 1, 2, 3, 5, 8, 13, 20, 40, 100, ☕, ❓</span>
                                </div>
                            </div>
                        </label>

                        <!-- T-Shirt Option Card -->
                        <label class="relative flex p-4 rounded-xl border cursor-pointer transition focus:outline-none {{ $deckType === 'tshirt' ? 'bg-purple-950/20 border-purple-500/50 shadow-[0_0_15px_-3px_rgba(139,92,246,0.25)]' : 'bg-gray-950/40 border-gray-800 hover:border-gray-700' }}">
                            <input type="radio" wire:model.live="deckType" value="tshirt" class="sr-only">
                            <div class="flex items-start gap-3">
                                <span class="text-2xl mt-0.5">👕</span>
                                <div>
                                    <span class="block text-sm font-bold text-white">{{ __('messages.tshirt') }}</span>
                                    <span class="block text-xs text-gray-500 mt-1">Cards: XS, S, M, L, XL, ☕, ❓ (mapped to 1, 2, 3, 4, 5)</span>
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('deckType')
                        <span class="text-xs font-semibold text-rose-400 block mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full relative group overflow-hidden rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 p-3 text-sm font-bold text-white transition hover:scale-[1.02] duration-200 neon-glow-primary border border-purple-500/20"
                >
                    <!-- Button loading spinner states -->
                    <div wire:loading wire:target="createNewSession" class="absolute inset-0 flex items-center justify-center bg-purple-600/90 z-10">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <span class="relative z-0 flex items-center justify-center gap-2">
                        <span>🚀</span>
                        {{ __('messages.create_button') }}
                    </span>
                </button>
            </form>
        </div>

        <!-- Right: Active Rooms List Card -->
        <div class="lg:col-span-7 rounded-3xl border border-gray-800/60 bg-gray-900/20 p-6 sm:p-8 backdrop-blur-md h-full flex flex-col relative">
            <div class="absolute top-0 right-10 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl -z-10"></div>
            <h2 class="text-xl font-bold font-outfit text-white tracking-tight flex items-center gap-2">
                <span class="p-1.5 rounded-lg bg-emerald-950/40 text-emerald-400 border border-emerald-800/50">⚡</span>
                {{ __('messages.active_rooms') }}
            </h2>

            <div class="mt-6 flex-1 overflow-y-auto max-h-[360px] pr-2 space-y-4">
                @forelse($activeRooms as $room)
                    <div class="flex items-center justify-between p-4 rounded-2xl border border-gray-800/50 bg-gray-950/30 hover:border-gray-700/60 hover:bg-gray-950/50 transition duration-200">
                        <div class="flex items-center gap-3 truncate">
                            <span class="text-2xl">{{ $room->deck_type === 'fibonacci' ? '🔢' : '👕' }}</span>
                            <div class="truncate">
                                <span class="block text-sm font-bold text-white truncate">{{ $room->name }}</span>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="text-xs text-gray-500">
                                        {{ $room->participants_count }} {{ trans_choice('participant|participants', $room->participants_count) }} active
                                    </span>
                                    <span class="text-xs text-gray-700">•</span>
                                    <span class="text-xs text-gray-500 capitalize">{{ $room->deck_type }}</span>
                                </div>
                            </div>
                        </div>
                        <a 
                            href="{{ route('room.show', ['id' => $room->id]) }}" 
                            class="flex items-center gap-1 px-4 py-2 text-xs font-extrabold rounded-xl border border-emerald-500/30 bg-emerald-950/20 text-emerald-400 hover:bg-emerald-500 hover:text-white transition duration-200 shadow-sm"
                        >
                            <span>Enter</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                @empty
                    <!-- Empty State Active Rooms -->
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <span class="text-4xl mb-3">🕸️</span>
                        <h3 class="text-sm font-bold text-gray-300">No active rooms found</h3>
                        <p class="text-xs text-gray-500 mt-1 max-w-xs">
                            Your server looks quiet. Spin up a new session on the left to start receiving wild story point assumptions!
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Bottom: Past Estimation Rounds (Past Sessions Logs) -->
    <div class="rounded-3xl border border-gray-800/60 bg-gray-900/20 p-6 sm:p-8 backdrop-blur-md relative">
        <h2 class="text-xl font-bold font-outfit text-white tracking-tight flex items-center gap-2">
            <span class="p-1.5 rounded-lg bg-indigo-950/40 text-indigo-400 border border-indigo-800/50">📜</span>
            {{ __('messages.past_sessions') }}
        </h2>

        <div class="mt-6 overflow-x-auto rounded-2xl border border-gray-800/50 bg-gray-950/20">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-gray-800 bg-gray-950/40 text-xs font-bold uppercase tracking-wider text-gray-400">
                        <th class="px-6 py-4">{{ __('messages.current_task') }}</th>
                        <th class="px-6 py-4">{{ __('messages.room_name') }}</th>
                        <th class="px-6 py-4">{{ __('messages.deck_type') }}</th>
                        <th class="px-6 py-4 text-center">{{ __('messages.avg_estimate') }}</th>
                        <th class="px-6 py-4 text-center">{{ __('messages.consensus_rate') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('messages.date') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/60 text-sm text-gray-300">
                    @forelse($pastSessions as $sessionLog)
                        <tr class="hover:bg-gray-900/20 transition duration-150">
                            <td class="px-6 py-4 font-bold text-white max-w-xs truncate">
                                {{ $sessionLog->task_title }}
                            </td>
                            <td class="px-6 py-4 truncate">
                                {{ $sessionLog->room->name ?? 'Deleted Room' }}
                            </td>
                            <td class="px-6 py-4 text-xs font-medium">
                                <span class="px-2.5 py-1 rounded-full capitalize {{ $sessionLog->deck_type === 'fibonacci' ? 'bg-purple-950/30 text-purple-400 border border-purple-900/20' : 'bg-blue-950/30 text-blue-400 border border-blue-900/20' }}">
                                    {{ $sessionLog->deck_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center font-extrabold text-white text-base">
                                {{ $sessionLog->final_estimate ?? '☕' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($sessionLog->consensus_reached)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-950/30 text-emerald-400 border border-emerald-900/20 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        Consensus
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-950/30 text-amber-400 border border-amber-900/20 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                        Divergence
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-xs text-gray-500 font-medium">
                                {{ $sessionLog->completed_at->diffForHumans() }}
                            </td>
                        </tr>
                    @empty
                        <!-- Empty State History -->
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <span class="text-4xl block mb-3">📁</span>
                                <h3 class="text-sm font-bold text-gray-300">No past rounds estimated yet</h3>
                                <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                                    Your estimation logs are clean. Launch a room, complete a voting round with consensus, and we'll track the analytics here!
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
