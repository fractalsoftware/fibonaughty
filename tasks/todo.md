# Tasks: Fibonaughty Application Construction

This document tracks the steps for implementing **Fibonaughty**—the real-time collaborative agile estimation tool.

## Todo List

### Phase 1: Base Application & Database Setup
- [x] Check local system environment (PHP, Composer, Node, NPM) <!-- id: app_env -->
- [x] Initialize fresh Laravel 11 application in the current directory <!-- id: init_laravel -->
- [x] Setup and configure local `.env` database and Redis connections <!-- id: env_config -->
- [x] Create database migrations (users, sessions, participants, votes, estimation_history) <!-- id: migrations -->
- [x] Install and configure Laravel Socialite <!-- id: socialite_install -->
- [x] Create eloquent models with standard relations and keys <!-- id: create_models -->
- [x] Create OAuth Controller for Google, GitHub, and Apple callback handlings <!-- id: oauth_controller -->

### Phase 2: NanoID, Routing & Localization
- [x] Install `miladrahimi/php-nanoid` package <!-- id: install_nanoid -->
- [x] Create `NanoIdGenerator` service helper class <!-- id: nanoid_service -->
- [x] Establish routes in `routes/web.php` using the specific NanoID regex filters <!-- id: web_routes -->
- [x] Create localization translation catalogs (`lang/en/messages.php`, `lang/es/messages.php`) <!-- id: local_files -->
- [x] Create guest cookie resolution middleware/hooks <!-- id: guest_middleware -->


### Phase 3: Core Game Loop Logic (Livewire)
- [x] Install and setup Livewire v4 <!-- id: install_livewire -->
- [x] Implement Creator Dashboard & Layout Setup <!-- id: creator_dashboard_substeps -->
  - [x] Create `resources/views/layouts/app.blade.php` with a stunning, premium dark-mode, glassmorphic layout and Google Fonts integration
  - [x] Implement `CreatorDashboard` Livewire controller logic (handling room creation via NanoID, retrieval of active rooms and past history)
  - [x] Implement `resources/views/livewire/creator-dashboard.blade.php` blade layout with localized elements, room creation form, active rooms table, and past rounds history
  - [x] Create/update a modern dark login page in `resources/views/welcome.blade.php` with Social Auth buttons
- [x] Fix Welcome Layout Directives & Test Compatibility <!-- id: fix_welcome_layout -->
  - [x] Add conditional `slot` or `@yield` block inside `resources/views/layouts/app.blade.php`
  - [x] Refactor `welcome.blade.php` to use `@extends` / `@section` syntax
  - [x] Run and verify `php artisan test` runs successfully
- [x] Create the primary `VotingRoom` Livewire Component <!-- id: voting_room_comp -->
  - [x] Generate Livewire VotingRoom component class and view
  - [x] Add participant guest token tracking (cookie check and initialization)
  - [x] Implement active participants tracking & state synchronization
  - [x] Implement voting card interaction with technical/humor subtexts (e.g. coffee, infinity, question mark)
  - [x] Code game transitions: Scrum Master control for 'Reveal Cards' and 'Reset/Revote'
  - [x] Record consensus analytics in `estimation_histories` on reveal
- [x] Code the frontend card layouts in Blade with technical humor elements <!-- id: core_views -->

### Phase 4: Real-Time Layer (Reverb) & Premium Polish
- [x] Install and configure Laravel Reverb WebSocket server <!-- id: reverb_install -->
- [x] Bind events in `VotingRoom` logic (dispatching `RoomStateUpdated` on join, vote, task update, start voting, reveal cards, and reset round) <!-- id: bind_events -->
- [x] Integrate Laravel Echo dynamic listeners inside `VotingRoom` component class (`getListeners`) <!-- id: dynamic_listeners -->
- [x] Refactor the frontend view to remove active 3-second polling and use localized 10-second heartbeat ping for session liveness <!-- id: polling_refactor -->
- [x] Improve Alpine-driven consensus-reached confetti trigger by dispatching a custom browser event from the server on reveal <!-- id: confetti_trigger_refactor -->
- [x] Run and verify all PHPUnit and Livewire feature tests to ensure perfect compatibility <!-- id: test_suite_verification -->

## Review & Verification
- [x] Verify that all user-specified requirements (such as NanoID, Social Auth only, No-Auth participants, and localization) are fully detailed in the design documents.
- [x] Ensure that code scaffolding matches the latest Laravel and Livewire v4 standards.

### Phase 5: Version Control & Remote Sync
- [x] Stage all untracked files <!-- id: stage_files -->
- [x] Write a descriptive commit message and commit the files <!-- id: commit_files -->
- [x] Push the commit to the remote repository `origin/main` <!-- id: push_files -->
