<div class="flex-1 flex flex-col relative" 
     x-data="{ consensusReached: @json($consensusReached) }" 
     x-init="if (consensusReached) { triggerConfetti(); }" 
     @consensus-achieved.window="triggerConfetti()" 
     wire:poll.10s="ping"
>
    <!-- Immersive Background Particle Canvas (Confetti) -->
    <canvas id="confetti-canvas" class="pointer-events-none fixed inset-0 z-50" style="display: none;"></canvas>

    <!-- 1. GUEST JOIN MODAL (Full Screen Glass Overlay) -->
    @if($showJoinModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-2xl bg-black/50 overflow-y-auto">
            <div class="w-full max-w-md rounded-3xl border border-purple-800/30 bg-[#0a0f19]/80 p-8 shadow-2xl relative overflow-hidden">
                <div class="absolute -top-[30%] -left-[20%] w-72 h-72 rounded-full bg-purple-600/10 blur-[90px] -z-10"></div>
                <div class="absolute -bottom-[30%] -right-[10%] w-60 h-60 rounded-full bg-emerald-600/10 blur-[90px] -z-10"></div>

                <div class="text-center space-y-6">
                    <div class="inline-flex items-center justify-center p-4 rounded-2xl bg-purple-950/40 border border-purple-800/40 shadow-xl">
                        <span class="text-4xl">👋</span>
                    </div>
                    <div>
                        <h2 class="text-2xl font-black font-outfit text-white tracking-tight">
                            {{ __('messages.enter_display_name') }}
                        </h2>
                        <p class="text-xs text-gray-400 mt-2 leading-relaxed">
                            {{ __('messages.login_subtitle') }}
                        </p>
                    </div>

                    <form wire:submit.prevent="joinRoom" class="space-y-4">
                        <div class="relative">
                            <input 
                                type="text" 
                                wire:model="participantName" 
                                placeholder="{{ __('messages.display_name_placeholder') }}"
                                class="w-full px-5 py-3.5 rounded-xl text-sm bg-gray-950/70 border border-gray-800 text-gray-100 placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all font-semibold"
                            />
                            @error('participantName')
                                <span class="text-xs text-rose-400 mt-1.5 block text-left font-medium">{{ $message }}</span>
                            @enderror
                        </div>

                        <button 
                            type="submit" 
                            class="w-full px-5 py-3.5 rounded-xl font-bold text-sm text-white bg-gradient-to-r from-purple-600 via-indigo-600 to-emerald-600 hover:opacity-95 transition-all shadow-xl hover:scale-[1.01] active:scale-[0.99] duration-150"
                        >
                            {{ __('messages.join_room_button') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Main Navigation Header Room Sub-Bar -->
    <div class="mb-8 rounded-3xl border border-gray-800/60 bg-gray-900/10 p-5 backdrop-blur-md flex flex-col md:flex-row md:items-center justify-between gap-4 relative overflow-hidden">
        <div class="absolute inset-0 bg-grid-white/[0.01] -z-10"></div>
        <div class="flex items-center gap-4">
            <div class="p-3 rounded-2xl bg-purple-950/30 border border-purple-800/40 text-2xl">
                @if($room->deck_type === 'fibonacci') 🔢 @else 👕 @endif
            </div>
            <div>
                <h1 class="text-xl font-extrabold font-outfit text-white leading-tight">
                    {{ $room->name }}
                </h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs font-semibold text-gray-400">
                        {{ __('messages.deck_type') }}:
                    </span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-purple-950/40 text-purple-300 border border-purple-900/30">
                        {{ $room->deck_type === 'fibonacci' ? __('messages.fibonacci') : __('messages.tshirt') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <span class="text-xs font-medium text-gray-400">{{ __('messages.current_task') }}:</span>
            @if($room->current_task_title)
                <span class="text-sm px-4 py-1.5 rounded-xl font-bold bg-[#0c1322] border border-gray-800 text-emerald-400 tracking-tight flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ $room->current_task_title }}
                </span>
            @else
                <span class="text-xs italic text-gray-500 font-medium">
                    {{ __('messages.waiting_for_creator') }}
                </span>
            @endif
        </div>
    </div>

    @if(session()->has('error'))
        <div class="mb-6 px-4 py-3 rounded-2xl border border-rose-900/40 bg-rose-950/20 text-rose-300 text-xs font-semibold flex items-center gap-2">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <!-- 2. CORE GAME GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left Side: THE ROUND GAME TABLE & CARDS (8 Columns) -->
        <div class="lg:col-span-8 space-y-8">
            
            <!-- THE ROUND BOARD/TABLE LAYER -->
            <div class="rounded-3xl border border-gray-800/80 bg-gray-950/20 p-8 shadow-2xl relative min-h-[400px] flex flex-col justify-between">
                <div class="absolute -top-[10%] -left-[10%] w-60 h-60 rounded-full bg-purple-900/5 blur-[100px] -z-10"></div>
                <div class="absolute -bottom-[10%] -right-[10%] w-60 h-60 rounded-full bg-emerald-900/5 blur-[100px] -z-10"></div>

                <!-- Table Header info -->
                <div class="flex justify-between items-center border-b border-gray-800/50 pb-4 mb-6">
                    <h2 class="text-xs font-bold text-gray-400 tracking-widest uppercase flex items-center gap-1.5">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-purple-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-purple-500"></span>
                        </span>
                        {{ __('messages.active_rooms') }} ({{ $activeParticipants->count() }})
                    </h2>
                    
                    <div>
                        @if($room->status === 'idle')
                            <span class="text-xs px-3 py-1 rounded-xl bg-gray-800 text-gray-400 font-bold border border-gray-700">
                                💤 IDLE
                            </span>
                        @elseif($room->status === 'voting')
                            <span class="text-xs px-3 py-1 rounded-xl bg-purple-950/40 text-purple-400 font-bold border border-purple-800/30 animate-pulse">
                                🗳️ ESTIMATING
                            </span>
                        @elseif($room->status === 'revealed')
                            <span class="text-xs px-3 py-1 rounded-xl bg-emerald-950/40 text-emerald-400 font-bold border border-emerald-900/30">
                                🃏 REVEALED
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Center: VIRTUAL AGILE TABLE (Visual grid of players surrounding a hub) -->
                <div class="flex-1 flex flex-col items-center justify-center my-6">
                    @if($activeParticipants->isEmpty())
                        <div class="text-center space-y-3 py-8">
                            <span class="text-4xl block">🌵</span>
                            <p class="text-sm font-semibold text-gray-500">
                                No developers have joined this round yet. Share the link!
                            </p>
                        </div>
                    @else
                        <!-- Player Grid representation -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-6 w-full max-w-2xl justify-items-center">
                            @foreach($activeParticipants as $p)
                                @php
                                    $pVote = $votes->firstWhere('participant_id', $p->id);
                                    $hasVoted = $pVote !== null;
                                @endphp

                                <div class="flex flex-col items-center gap-3 w-full max-w-[120px]">
                                    <!-- Dynamic Card Wrapper -->
                                    <div class="relative w-20 h-28 group transition duration-300">
                                        @if($room->status === 'revealed')
                                            <!-- Revealed State Card (Face Up) -->
                                            <div class="absolute inset-0 rounded-2xl border bg-gradient-to-b from-[#0e1726] to-[#080d1a] flex flex-col items-center justify-between p-3.5 shadow-2xl transition-all duration-500 {{ $consensusReached ? 'border-emerald-500/50 shadow-emerald-950/30' : 'border-purple-800/40' }}">
                                                <span class="text-2xl font-black font-outfit text-white select-none">
                                                    {{ $pVote?->estimate_value ?? '❓' }}
                                                </span>
                                                <span class="text-[9px] font-bold text-gray-500 tracking-wider">
                                                    POINT
                                                </span>
                                            </div>
                                        @else
                                            <!-- Interactive Voting State Card (Face Down / Hidden) -->
                                            @if($hasVoted)
                                                <!-- Locked in Card (Glowing purple grid back) -->
                                                <div class="absolute inset-0 rounded-2xl border border-purple-500 bg-gradient-to-br from-purple-900/30 via-indigo-950/50 to-gray-950 flex flex-col items-center justify-center shadow-xl shadow-purple-950/20 select-none cursor-default">
                                                    <span class="text-2xl text-purple-400 font-bold animate-pulse">🔒</span>
                                                    <span class="text-[8px] font-black text-purple-400 mt-2 tracking-widest uppercase">LOCKED</span>
                                                </div>
                                            @else
                                                <!-- Empty / Idle player card placeholder -->
                                                <div class="absolute inset-0 rounded-2xl border-2 border-dashed border-gray-800 hover:border-gray-700 bg-gray-900/10 flex flex-col items-center justify-center transition select-none">
                                                    <span class="text-xs font-semibold text-gray-600">...</span>
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                    <!-- Participant Nickname -->
                                    <div class="text-center w-full">
                                        <span class="text-xs font-bold text-gray-200 block truncate" title="{{ $p->name }}">
                                            {{ $p->name }}
                                        </span>
                                        @if($room->status !== 'revealed')
                                            @if($hasVoted)
                                                <span class="text-[9px] font-bold text-emerald-400 flex items-center justify-center gap-1 mt-0.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    READY
                                                </span>
                                            @else
                                                <span class="text-[9px] font-bold text-gray-500 flex items-center justify-center gap-1 mt-0.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-600"></span>
                                                    THINKING
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Consensus Display Banner (Shown on reveal) -->
                @if($room->status === 'revealed')
                    <div class="mt-6 border-t border-gray-800/60 pt-6">
                        @if($consensusReached)
                            <div class="p-5 rounded-2xl border border-emerald-800/40 bg-emerald-950/20 shadow-lg relative overflow-hidden">
                                <div class="absolute -right-10 -bottom-10 text-7xl opacity-10 rotate-12">🎉</div>
                                <h3 class="text-emerald-400 font-extrabold font-outfit text-sm tracking-tight flex items-center gap-1.5">
                                    🌟 {{ __('messages.consensus_achieved') }}
                                </h3>
                                <p class="text-xs text-emerald-300/80 mt-1 leading-relaxed max-w-2xl">
                                    Your team successfully locked in a matching <strong>{{ $consensusValue }}</strong>. Standard protocol permits high-fives and continuing the sprint without a single architectural debate!
                                </p>
                            </div>
                        @else
                            <div class="p-5 rounded-2xl border border-purple-900/40 bg-[#0c0d16] shadow-lg relative overflow-hidden">
                                <div class="absolute -right-10 -bottom-10 text-7xl opacity-10 rotate-12">🤷</div>
                                <h3 class="text-purple-400 font-extrabold font-outfit text-sm tracking-tight flex items-center gap-1.5">
                                    ⚔️ {{ __('messages.consensus_divergence') }}
                                </h3>
                                <p class="text-xs text-purple-300/80 mt-1 leading-relaxed max-w-2xl">
                                    Estimates range widely. The current average is <strong class="text-emerald-400">{{ $averageEstimate }}</strong>. The Scrum Master should initiate a brief discussion before resetting the round to vote again.
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- 3. DECKS SELECTION PORTAL (Only for non-creator estimators during voting state) -->
            @if(!$isCreator && $room->status === 'voting')
                <div class="rounded-3xl border border-gray-800/60 bg-gray-900/5 p-6 backdrop-blur-md relative overflow-hidden">
                    <div class="absolute inset-0 bg-grid-white/[0.005] -z-10"></div>
                    
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center border-b border-gray-800/40 pb-4 mb-6 gap-3">
                        <div>
                            <h2 class="text-sm font-extrabold font-outfit text-white tracking-tight">
                                🃏 {{ __('messages.deck_options') }}
                            </h2>
                            <p class="text-xs text-gray-400 mt-1">
                                {{ __('messages.how_it_works') }}
                            </p>
                        </div>
                        
                        @if($isVoteLocked)
                            <span class="text-xs px-3 py-1 rounded-xl bg-emerald-950/40 text-emerald-400 font-bold border border-emerald-900/30 flex items-center gap-1">
                                ✔️ {{ __('messages.vote_submitted') }}
                            </span>
                        @endif
                    </div>

                    <!-- Cards Deck list -->
                    <div class="flex flex-wrap gap-4 items-center justify-center">
                        @php
                            $deck = $room->deck_type === 'fibonacci'
                                ? [
                                    '0' => 'One-liner typo fix.',
                                    '1' => 'Standard micro edit.',
                                    '2' => 'Simple CRUD controller.',
                                    '3' => 'Full day task.',
                                    '5' => 'Standard core logic flow.',
                                    '8' => 'Refactoring required.',
                                    '13' => 'Spaghetti monster ahead.',
                                    '20' => 'Highly complex module.',
                                    '40' => 'Complete legacy rewrite.',
                                    '100' => 'Life choices required.',
                                    '☕' => 'Brewing virtual espresso.',
                                    '❓' => 'Needs specification.',
                                    '∞' => 'Infinite loops incoming.'
                                ]
                                : [
                                    'XS' => 'One-liner simple patch.',
                                    'S' => 'Small feature modules.',
                                    'M' => 'Standard sprint feature.',
                                    'L' => 'Heavy infrastructure task.',
                                    'XL' => 'Major database migration.',
                                    '☕' => 'Grabbing a tea/coffee.',
                                    '❓' => 'Ambiguous scope.'
                                ];
                        @endphp

                        @foreach($deck as $cardVal => $flavorText)
                            @php
                                $isSelected = $selectedEstimate === $cardVal;
                            @endphp

                            <button 
                                wire:click="castVote('{{ $cardVal }}')"
                                @if($isVoteLocked && !$isSelected) disabled @endif
                                class="w-16 sm:w-20 h-24 sm:h-28 rounded-2xl border flex flex-col items-center justify-between p-3 transition duration-150 relative hover:-translate-y-2 select-none group 
                                {{ $isSelected 
                                    ? 'border-emerald-500 bg-gradient-to-br from-emerald-950/40 to-[#03150d] text-white shadow-xl shadow-emerald-950/40 scale-105' 
                                    : 'border-gray-800 bg-[#0c121f]/60 text-gray-300 hover:text-white hover:border-purple-500/50 shadow-md hover:shadow-purple-950/10' }}"
                                title="{{ $flavorText }}"
                            >
                                <span class="text-xl sm:text-2xl font-black font-outfit leading-none mt-1">
                                    {{ $cardVal }}
                                </span>
                                
                                <span class="text-[7px] sm:text-[8px] font-bold text-center tracking-tight leading-normal uppercase opacity-75 truncate w-full">
                                    {{ $flavorText }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Side: MODERATOR/SCRUM MASTER CONTROLS & ARCHIVE (4 Columns) -->
        <div class="lg:col-span-4 space-y-8">
            
            <!-- SCRUM MASTER CONTROL STATION -->
            @if($isCreator)
                <div class="rounded-3xl border border-gray-800/80 bg-gray-950/30 p-6 shadow-2xl relative overflow-hidden">
                    <div class="absolute -top-[30%] -right-[30%] w-56 h-56 rounded-full bg-purple-900/10 blur-[80px] -z-10"></div>
                    
                    <h2 class="text-sm font-black font-outfit text-white tracking-widest uppercase border-b border-gray-800/50 pb-3 mb-5 flex items-center gap-1.5">
                        👑 {{ __('messages.scrum_master') }}
                    </h2>

                    <div class="space-y-6">
                        <!-- Form 1: Define / Update Task Title -->
                        <form wire:submit.prevent="updateTask" class="space-y-3">
                            <label class="block text-xs font-bold text-gray-400 tracking-wider">
                                {{ __('messages.define_task') }}
                            </label>
                            <div class="flex gap-2">
                                <input 
                                    type="text" 
                                    wire:model="taskTitleInput" 
                                    placeholder="{{ __('messages.task_title_placeholder') }}"
                                    class="flex-1 px-4 py-2.5 rounded-xl text-xs bg-gray-950/70 border border-gray-800 text-gray-100 placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500/20 transition-all font-semibold"
                                />
                                <button 
                                    type="submit" 
                                    class="px-4 py-2.5 rounded-xl font-bold text-xs text-white bg-purple-600 hover:bg-purple-500 transition shadow-lg active:scale-95"
                                >
                                    💾
                                </button>
                            </div>
                            @error('taskTitleInput')
                                <span class="text-xs text-rose-400 mt-1 block font-medium">{{ $message }}</span>
                            @enderror
                        </form>

                        <!-- Actions Stack -->
                        <div class="space-y-3 pt-2">
                            @if($room->status === 'idle' || empty($room->current_task_title))
                                <button 
                                    wire:click="startVoting"
                                    @if(empty($room->current_task_title)) disabled @endif
                                    class="w-full px-5 py-3 rounded-xl font-bold text-xs text-center text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:opacity-95 transition-all shadow-md flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed active:scale-[0.99] duration-150"
                                >
                                    🗳️ {{ __('messages.start_voting') }}
                                </button>
                            @elseif($room->status === 'voting')
                                <button 
                                    wire:click="revealCards"
                                    class="w-full px-5 py-3 rounded-xl font-bold text-xs text-center text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:opacity-95 transition-all shadow-md flex items-center justify-center gap-2 active:scale-[0.99] duration-150"
                                >
                                    🃏 {{ __('messages.reveal_cards') }}
                                </button>
                            @elseif($room->status === 'revealed')
                                <button 
                                    wire:click="resetRound"
                                    class="w-full px-5 py-3 rounded-xl font-bold text-xs text-center text-white bg-gradient-to-r from-purple-600 via-indigo-600 to-emerald-600 hover:opacity-95 transition-all shadow-md flex items-center justify-center gap-2 active:scale-[0.99] duration-150"
                                >
                                    🔄 {{ __('messages.next_round') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- PARTICIPANT SIDEBAR (For Non-Creators) -->
            @if(!$isCreator)
                <div class="rounded-3xl border border-gray-800/80 bg-gray-950/20 p-6 shadow-xl relative overflow-hidden">
                    <h2 class="text-xs font-bold text-gray-400 tracking-widest uppercase border-b border-gray-800/50 pb-3 mb-4 flex items-center gap-1.5">
                        👥 Your Info
                    </h2>
                    
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center font-bold text-xs text-white border border-purple-500/30">
                            👤
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 font-medium block">Nickname</span>
                            <span class="text-sm font-extrabold text-white tracking-tight">
                                {{ $participant?->name ?? 'Guest' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- PAST ARCHIVE HISTORY FOR THIS SPECIFIC ROOM -->
            @php
                $histories = $room->estimationHistories()->orderBy('completed_at', 'desc')->get();
            @endphp

            <div class="rounded-3xl border border-gray-800/80 bg-gray-950/20 p-6 shadow-2xl relative overflow-hidden">
                <h2 class="text-xs font-bold text-gray-400 tracking-widest uppercase border-b border-gray-800/50 pb-3 mb-4 flex items-center gap-1.5">
                    🗄️ {{ __('messages.past_rounds') }}
                </h2>

                @if($histories->isEmpty())
                    <p class="text-xs text-gray-500 italic py-3 text-center">
                        No estimations archived for this room yet.
                    </p>
                @else
                    <div class="space-y-4 max-h-[300px] overflow-y-auto pr-1">
                        @foreach($histories as $h)
                            <div class="p-3.5 rounded-2xl bg-[#0c121e] border border-gray-800/80 flex items-center justify-between gap-3 shadow-md hover:border-gray-700 transition">
                                <div class="truncate">
                                    <span class="text-xs font-bold text-white block truncate" title="{{ $h->task_title }}">
                                        {{ $h->task_title }}
                                    </span>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[9px] font-bold text-gray-500 tracking-wider">
                                            {{ $h->completed_at->format('M d, H:i') }}
                                        </span>
                                        <span class="text-[9px] font-bold text-gray-500">•</span>
                                        <span class="text-[9px] font-bold text-gray-400">
                                            Rounds: {{ $h->rounds_count }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end shrink-0">
                                    @if($h->consensus_reached)
                                        <span class="text-xs font-black font-outfit text-emerald-400 bg-emerald-950/30 border border-emerald-900/40 px-2.5 py-1 rounded-xl">
                                            {{ $h->final_estimate }}
                                        </span>
                                        <span class="text-[8px] font-extrabold text-emerald-400 mt-1 tracking-widest uppercase">CONSENSUS</span>
                                    @else
                                        <span class="text-xs font-black font-outfit text-purple-400 bg-purple-950/30 border border-purple-900/40 px-2.5 py-1 rounded-xl">
                                            DIV
                                        </span>
                                        <span class="text-[8px] font-extrabold text-purple-400 mt-1 tracking-widest uppercase">DIVERGED</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- 4. Confetti Animation JavaScript Core Logic -->
    <script>
        function triggerConfetti() {
            var canvas = document.getElementById('confetti-canvas');
            if (!canvas) return;
            canvas.style.display = 'block';
            
            var ctx = canvas.getContext('2d');
            var pieces = [];
            var numberOfPieces = 150;
            var colors = ['#a78bfa', '#818cf8', '#34d399', '#f472b6', '#fbbf24', '#60a5fa'];

            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;

            function updateCanvasSize() {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            }
            window.addEventListener('resize', updateCanvasSize);

            function Piece(x, y) {
                this.x = x;
                this.y = y;
                this.size = (Math.random() * 10) + 6;
                this.gravity = (Math.random() * 0.15) + 0.12;
                this.rotation = (Math.random() * 360);
                this.rotationSpeed = (Math.random() * 4) - 2;
                this.color = colors[Math.floor(Math.random() * colors.length)];
                this.xSpeed = (Math.random() * 6) - 3;
                this.ySpeed = (Math.random() * -12) - 8;
            }

            for (var i = 0; i < numberOfPieces; i++) {
                pieces.push(new Piece(canvas.width / 2, canvas.height + 20));
            }

            var animationFrameId;
            var duration = 4000; // 4 seconds confetti span
            var startTime = Date.now();

            function animate() {
                var elapsed = Date.now() - startTime;
                if (elapsed > duration) {
                    canvas.style.display = 'none';
                    window.removeEventListener('resize', updateCanvasSize);
                    cancelAnimationFrame(animationFrameId);
                    return;
                }

                ctx.clearRect(0, 0, canvas.width, canvas.height);

                pieces.forEach(function (p) {
                    p.y += p.ySpeed;
                    p.ySpeed += p.gravity;
                    p.x += p.xSpeed;
                    p.rotation += p.rotationSpeed;

                    ctx.save();
                    ctx.translate(p.x, p.y);
                    ctx.rotate(p.rotation * Math.PI / 180);
                    ctx.fillStyle = p.color;
                    ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
                    ctx.restore();
                });

                animationFrameId = requestAnimationFrame(animate);
            }

            animate();
        }
    </script>
</div>
