# Master LLM Developer Prompt: Building Fibonaughty

You can copy and paste the prompt block below directly into any state-of-the-art LLM (such as Gemini 1.5 Pro, Gemini 3.5 Flash, or Claude 3.5 Sonnet) to initialize and direct the full-stack construction of the **Fibonaughty** application.

***

```markdown
You are acting as a world-class, high-performance Software Development Team consisting of an Agile Product Manager, a Principal Systems Architect, a Senior Laravel Backend Engineer, a Creative Frontend Specialist, and a rigorous QA Engineer.

Your objective is to build **Fibonaughty**—a collaborative, gamified, and highly secure real-time agile estimation app using **PHP 8.3**, **Laravel 11**, **Livewire v3**, **Alpine.js**, and **Laravel Reverb**.

---

### 1. APPLICATION MECHANICS & CORE CONCEPT
Fibonaughty is a consensus-based planning poker application.
- **The Flow:** An authenticated creator (Scrum Master) logs in via Social Auth, creates a room, selects a deck type, and enters a task. They share a unique NanoID-based link. Guests (Participants) join instantly without signing up by simply typing a display name. Participants secretly cast estimates. When all votes are locked in (or at the creator's discretion), the creator triggers a "Reveal" state, flipping cards simultaneously. If consensus is reached, a celebration triggers; otherwise, they discuss and revote.
- **Decks Supported:**
  - Modified Fibonacci: `['0', '1', '2', '3', '5', '8', '13', '20', '40', '100', '☕', '❓']`
  - T-Shirt Sizes: `['XS', 'S', 'M', 'L', 'XL', '☕', '❓']`
- **Branding & Tone:** Sleek, premium dark mode styling infused with sharp, self-referential technical developer humor (jokes about scope creep, legacy code, coffee levels, and blaming the intern/DBA).

---

### 2. ARCHITECTURAL & TECHNICAL CONSTRAINTS (NON-NEGOTIABLE)

1. **Authentication Architecture:**
   - **Creators:** Social Auth ONLY (Google, GitHub, Apple) using Laravel Socialite. Standard email/password forms must be omitted.
   - **Participants (Guests):** Strictly anonymous. No email, registration, or standard login. Upon entering a name, they receive a secure HTTP-Only cookie `fibonaughty_p_{roomId}` storing a secure UUIDv4. On page refresh, the system must read this cookie and automatically reconnect the participant to their active room state.

2. **Routing & NanoIDs:**
   - Room URLs must use unpredictable NanoIDs of length 12 to 21 characters instead of auto-incrementing integers or standard UUIDs (e.g., `https://fibonaughty.app/room/K9_zL-m7bQ9a`).
   - Use the `miladrahimi/php-nanoid` library for generating these IDs.
   - Enforce NanoID pattern constraints at the routing layer: `[a-zA-Z0-9_-]{12,21}`.

3. **Real-Time synchronization:**
   - Use **Laravel Reverb** (WebSocket server) and **Laravel Echo** to manage real-time presence channels and state changes.
   - When a state change (such as `VoteCast`, `TaskUpdated`, `CardStateRevealed`, `TaskReset`, or `ParticipantPresenceChanged`) occurs, broadcast the event instantly via Reverb so all connected screens update immediately without page polling.

4. **Localization & Clean Code:**
   - The entire application must be built using Laravel's translation engine (`__('messages.key')`) from day one. Do not hardcode raw strings in views.
   - Write fully typed PHP 8.3 code. Keep files neat, modular, and maintain standard Laravel conventions. Avoid placeholders, truncated files, or "left as an exercise for the reader" omissions.

---

### 3. DATABASE SCHEMA REQUIREMENTS

Generate migrations for the following entities, including correct primary keys, indices, and cascading foreign keys:
- **`users`:** Modified for Social OAuth mapping (stores `id`, `name`, `email`, `avatar_url`, `oauth_provider`, and `oauth_id`).
- **`sessions`:** Use NanoID string (length 21) as primary key. Tracks `creator_id`, `name`, `deck_type`, `status` (`idle`, `voting`, `revealed`), and `current_task_title`.
- **`participants`:** Temporary guests. Primary key is UUIDv4. Tracks `session_id`, `name`, `is_active`, and `last_seen_at`.
- **`votes`:** Tracks individual votes per round. Stores `session_id`, `participant_id`, `task_title`, and `estimate_value` (string). Unique composite constraint on `['session_id', 'participant_id', 'task_title']`.
- **`estimation_history`:** Persistent round stats for the creator's dashboard. Tracks `session_id`, `task_title`, `deck_type`, `final_estimate`, `consensus_reached` (boolean), `rounds_count`, and `completed_at`.

---

### 4. IMPLEMENTATION PIPELINE instructions

Please build the application step-by-step by generating the following modules sequentially. Produce complete, fully implementable file contents for each step:

#### STEP 1: Core System & Social Auth Setup
- Generate the Laravel migrations for the database schema described above.
- Configure `config/services.php` and the `OAuthController` handling Laravel Socialite redirections and callbacks for Google, GitHub, and Apple.
- Build the `User` model, removing standard password columns and setting up Social relationship lookups.

#### STEP 2: NanoID, Routing & Localization
- Create a dedicated helper/service class `App\Services\NanoIdGenerator` wrapping `miladrahimi/php-nanoid`.
- Write the routing architecture in `routes/web.php` with regex constraints for Room NanoIDs.
- Generate the localization language files `lang/en/messages.php` containing structural strings, tooltips, and humorous responses.

#### STEP 3: Livewire Core State Machine & Cookie Resolution
- Implement the main Livewire component `App\Livewire\VotingRoom` and its corresponding blade view.
- In `mount()`, implement the logic that reads the `fibonaughty_p_{roomId}` cookie, verifies if a matching record exists, and handles participant reassociation.
- Implement the `joinRoom()` and `submitVote(value)` methods. Securely validate displayName and ensure voting values match the room's current `deck_type`.
- Write the creator-only state toggle methods (e.g., `toggleRevealState()`, `resetRound()`).

#### STEP 4: Real-Time Layer (Reverb) & Premium UI Polish
- Write the WebSocket broadcast events: `RoomStateUpdated` and `VoteCast`.
- Set up the presence channel routes in `routes/channels.php`.
- Complete the Livewire blade file with a premium dark-themed interface:
  - Base background color: Slate-950 (`#0b0f19`).
  - Accent colors: HSL Violet and Mint-green for consensus.
  - Apply Glassmorphism panels utilizing `backdrop-blur-md`.
  - Animate cards using 3D perspective flips when transitioning from facedown to revealed.
  - Implement a JavaScript-powered canvas particle explosion (confetti) that launches the moment consensus is confirmed.

Begin generating the files now. Start with **Step 1 (Migrations and Socialite Auth Controller)**, printing full, valid code without placeholders.
```
