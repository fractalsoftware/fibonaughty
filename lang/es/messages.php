<?php

return [
    // Branding & Layout
    'app_name' => 'Fibonaughty',
    'app_tagline' => 'Estimación ágil colaborativa, gamificada y basada en consenso.',
    'welcome_back' => '¡Bienvenido de nuevo, :name!',
    'logout' => 'Cerrar Sesión',
    'scrum_master' => 'Scrum Master',
    'active_rooms' => 'Salas Activas',
    'past_sessions' => 'Sesiones de Estimación Pasadas',

    // Landing / Login
    'sign_in_with' => 'Iniciar Sesión con :provider',
    'login_title' => 'Inicia Sesión para Estimar',
    'login_subtitle' => 'Solo los creadores necesitan autenticarse. ¡Los participantes se unen al instante como invitados!',
    'guest_sign_in_notice' => 'No se requiere registro para los participantes. Solo únete mediante el enlace de la sala.',

    // Creator Dashboard
    'create_room' => 'Crear una Nueva Sala',
    'room_name' => 'Nombre de la Sala',
    'deck_type' => 'Tipo de Baraja',
    'fibonacci' => 'Fibonacci Modificado (0, 1, 2, 3, 5, 8, 13, 20, 40, 100)',
    'tshirt' => 'Tallas de Camiseta (XS, S, M, L, XL)',
    'create_button' => 'Lanzar Sala',
    'past_rounds' => 'Rondas de Estimación Pasadas',
    'avg_estimate' => 'Estimación Promedio',
    'consensus_rate' => 'Tasa de Consenso',
    'date' => 'Fecha',

    // Voting Room
    'enter_display_name' => 'Introduce tu Nombre de Pantalla',
    'display_name_placeholder' => 'ej. CodeNinja, BugSlayer',
    'join_room_button' => 'Entrar a la Batalla',
    'waiting_for_creator' => 'Esperando a que el Scrum Master defina la siguiente tarea...',
    'current_task' => 'Tarea Actual',
    'define_task' => 'Definir Siguiente Tarea',
    'task_title_placeholder' => 'ej. Implementar controlador OAuth o arreglar glassmorphism de CSS',
    'start_voting' => 'Iniciar Votación',
    'reveal_cards' => 'Revelar Cartas',
    'next_round' => 'Iniciar Siguiente Ronda / Volver a Votar',
    'consensus_achieved' => '¡Milagro conseguido! Consenso alcanzado sin que ningún desarrollador llore.',
    'consensus_divergence' => 'Houston, tenemos un debate de arquitectura. Una persona piensa que es una sola línea; otra piensa que requiere una reescritura completa.',
    'waiting_for_votes' => 'Esperando a que los participantes aseguren sus suposiciones...',
    'vote_submitted' => '¡Estimación guardada!',
    'no_votes_yet' => 'Nadie ha votado todavía.',
    'how_it_works' => 'Selecciona una carta abajo para enviar tu estimación de forma privada.',
    'deck_options' => 'Tus Opciones de Baraja',

    // Humorous loading & error states
    'compiling_consensus' => 'Compilando consenso... Espera mientras resolvemos los conflictos de optimismo de tu equipo.',
    'blaming_intern' => 'Culpando al becario por la latencia de la red local...',
    'calculating_sanity' => 'Calculando la relación entre puntos de historia y la cordura restante del desarrollador...',
    'brewing_espresso' => 'Preparando espresso virtual para contrarrestar el scope creep entrante...',
    'refactoring_engine' => 'Refactorizando el motor de estimación para eludir las expectativas del PM...',

    'error_estimate_too_high' => 'Tu estimación es demasiado alta para este backlog. Por favor baja tus expectativas o repón tu suministro de café.',
    'error_scope_creep' => 'La estimación supera los 100 puntos de historia. Esto ya no es una tarea; es una elección de estilo de vida.',
    'error_consensus_timeout' => 'Consenso no alcanzado. El procedimiento estándar dicta combate a muerte o culpar al administrador de la base de datos.',
    'error_session_invalid' => '¿Quién anda ahí? No pudimos verificar tu identidad. ¿Se autodestruyó tu cookie local?',
    'voting_closed' => 'La votación está cerrada para esta ronda.',
    'invalid_estimate' => 'Opción de estimación no válida seleccionada.',
];
