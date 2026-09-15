<?php
declare(strict_types=1);

function tr(string $key): string
{
    $lang = $_SESSION['lang'] ?? 'fr';
    $dict = [
        'en' => [
            'app_title' => 'jumpseat - Shared flights',
            'calendar' => 'Calendar',
            'login' => 'Login',
            'logout' => 'Logout',
            'register' => 'Register',
            'profile' => 'Profile',
            'admin' => 'Admin',
            'new_flight' => 'New flight',
            'my_flights' => 'My flights',
            'forgot_password' => 'Forgot password',
            'reset_password' => 'Reset password',
            'save' => 'Save',
            'delete' => 'Delete',
            'edit' => 'Edit',
            'send' => 'Send',
            'cancel' => 'Cancel',
            'week_prev' => 'Previous',
            'week_next' => 'Next',
            'today' => 'Today',
            'no_flight_yet' => 'No flight yet.',
            'title' => 'Title',
            'route' => 'Route',
            'aircraft' => 'Aircraft',
            'departure' => 'Departure',
            'return' => 'Return',
            'seats' => 'Seats',
            'actions' => 'Actions',
            'close' => 'Close',
            'loading' => 'Loading...',
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
            'new_password' => 'New password',
            'photo' => 'Photo',
            'change_photo' => 'Change photo',
            'phone' => 'Phone',
            'notify_new_flights_label' => 'Notify me on new flights',
            'deregister_account' => 'Deregister account',
            'flight_title' => 'Flight Title',
            'origin' => 'Origin',
            'destination' => 'Destination',
            'planned_departure_local' => 'Planned departure date/time (local)',
            'planned_return_local' => 'Planned return date/time (local)',
            'planned_duration_minutes' => 'Planned flight duration (minutes)',
            'seats_available' => 'Number of seats available',
            'additional_information' => 'Additional information',
            'create_flight' => 'Create flight',
            'edit_flight' => 'Edit flight',
            'add_aircraft_models_first' => 'Add aircraft models in profile first',
            'aircraft_model_1_label' => 'Aircraft model 1',
            'aircraft_model_2_label' => 'Aircraft model 2',
            'aircraft_model_3_label' => 'Aircraft model 3',
            'admin_users' => 'Admin - Users',
            'status' => 'Status',
            'block' => 'Block',
            'unblock' => 'Unblock',
            'reset_password_admin' => 'Reset password',
            'active' => 'Active',
            'blocked' => 'Blocked',
            'by' => 'by',
            'flight_details' => 'Flight details',
            'contact' => 'Contact',
            'send_interest_notification' => 'Send interest notification',
            'your_message' => 'Your message',
            'confirm_delete_flight' => 'Are you sure you want to delete this flight?',
            'no_seat_available' => 'No seat available',
            'invalid_username' => 'Invalid username. Use 3-30 chars: letters, numbers, underscore.',
            'invalid_email_or_password' => 'Invalid email or password too short (min 8).',
            'account_created' => 'Account created. You can login now.',
            'username_or_email_exists' => 'Username or email already exists.',
            'invalid_credentials' => 'Invalid credentials.',
            'logged_out' => 'Logged out.',
            'account_blocked' => 'Your account is blocked.',
            'reset_email_if_exists' => 'If the account exists, a reset email has been sent.',
            'password_too_short' => 'Password too short.',
            'invalid_or_expired_token' => 'Invalid or expired token.',
            'password_reset_success' => 'Password reset successful.',
            'profile_updated' => 'Profile updated.',
            'account_removed' => 'Account removed.',
            'field_required' => 'Field %s is required.',
            'flight_updated' => 'Flight updated.',
            'flight_created_notifications_sent' => 'Flight created and notifications sent.',
            'flight_deleted' => 'Flight deleted.',
            'notification_already_sent' => 'Notification already sent.',
            'notification_sent_to_creator' => 'Notification sent to flight creator.',
            'admin_action_completed' => 'Admin action completed.',
            'mail_subject_password_reset' => 'Password reset',
            'mail_body_password_reset' => 'Reset your password: %s',
            'mail_subject_new_flight' => 'New flight created - %s',
            'mail_body_new_flight' => "A new flight has been created in jumpseat.\n\nTitle: %s\nRoute: %s -> %s\nAircraft: %s\nDeparture: %s\nReturn: %s\nDuration: %s minutes\nSeats: %s\nBy: %s\nInfo: %s\nFlight ID: %s",
            'mail_subject_interest' => 'New jumpseat request',
            'mail_body_interest' => 'Pilot %s is interested in your flight "%s" (departure: %s). Message: %s',
            'mail_subject_admin_reset' => 'Admin reset your password',
            'mail_body_admin_reset' => 'Temporary password: %s',
            'disclaimer_button' => 'Disclaimer',
            'disclaimer_title' => 'Disclaimer',
            'disclaimer_text' => "Use of this website and the proposed services is under the sole and entire responsibility of users.\nPilots, passengers, and platform members are responsible for verifying the legal, regulatory, technical, and safety compliance of their aeronautical activities.\nThe website publisher does not exercise any control over published information, pilot skills or qualifications, flight safety, or compliance with legal obligations applicable to aviation.\nConsequently, the publisher cannot be held liable, directly or indirectly, for any damage, loss, incident, or accident occurring before, during, or after a flight organized through the platform.\nBy using this service, you acknowledge that you fully accept these terms and release the publisher from any liability related to use of the site or participation in a shared flight.",
            'user_manual_title' => 'User manual',
        ],
        'fr' => [
            'app_title' => 'jumpseat - Vols partagés',
            'calendar' => 'Calendrier',
            'login' => 'Connexion',
            'logout' => 'Deconnexion',
            'register' => 'Inscription',
            'profile' => 'Profil',
            'admin' => 'Admin',
            'new_flight' => 'Nouveau vol',
            'my_flights' => 'Mes vols',
            'forgot_password' => 'Mot de passe oublie',
            'reset_password' => 'Reinitialiser le mot de passe',
            'save' => 'Enregistrer',
            'delete' => 'Supprimer',
            'edit' => 'Modifier',
            'send' => 'Envoyer',
            'cancel' => 'Annuler',
            'week_prev' => 'Précedent',
            'week_next' => 'Suivant',
            'today' => "Aujourd'hui",
            'no_flight_yet' => 'Aucun vol.',
            'title' => 'Titre',
            'route' => 'Trajet',
            'aircraft' => 'Aeronef',
            'departure' => 'Depart',
            'return' => 'Retour',
            'seats' => 'Places',
            'actions' => 'Actions',
            'close' => 'Fermer',
            'loading' => 'Chargement...',
            'username' => "Nom d'utilisateur",
            'email' => 'Email',
            'password' => 'Mot de passe',
            'new_password' => 'Nouveau mot de passe',
            'photo' => 'Photo',
            'change_photo' => 'Changer la photo',
            'phone' => 'Telephone',
            'notify_new_flights_label' => 'Me notifier des nouveaux vols',
            'deregister_account' => 'Supprimer le compte',
            'flight_title' => 'Titre du vol',
            'origin' => 'Origine',
            'destination' => 'Destination',
            'planned_departure_local' => 'Départ prévu date/heure (local)',
            'planned_return_local' => 'Retour prévu date/heure (local)',
            'planned_duration_minutes' => 'Durée de vol prévue (minutes)',
            'seats_available' => 'Nombre de places disponibles',
            'additional_information' => 'Informations complementaires',
            'create_flight' => 'Créer un vol',
            'edit_flight' => 'Modifier le vol',
            'add_aircraft_models_first' => "Ajoutez d'abord des aéronefs dans le profil",
            'aircraft_model_1_label' => 'Aéronef 1',
            'aircraft_model_2_label' => 'Aéronef 2',
            'aircraft_model_3_label' => 'Aéronef 3',
            'admin_users' => 'Admin - Utilisateurs',
            'status' => 'Statut',
            'block' => 'Bloquer',
            'unblock' => 'Debloquer',
            'reset_password_admin' => 'Réinitialiser le mot de passe',
            'active' => 'Actif',
            'blocked' => 'Bloque',
            'by' => 'par',
            'flight_details' => 'Détails du vol',
            'contact' => 'Contacter',
            'send_interest_notification' => "Envoyer une notification d'interêt",
            'your_message' => 'Votre message',
            'confirm_delete_flight' => 'Voulez-vous vraiment supprimer ce vol ?',
            'no_seat_available' => 'Aucune place disponible',
            'invalid_username' => "Nom d'utilisateur invalide. 3-30 caractères: lettres, chiffres, underscore.",
            'invalid_email_or_password' => 'Email invalide ou mot de passe trop court (min 8).',
            'account_created' => 'Compte créé. Vous pouvez maintenant vous connecter.',
            'username_or_email_exists' => "Le nom d'utilisateur ou l'email existe deja.",
            'invalid_credentials' => 'Identifiants invalides.',
            'logged_out' => 'Déconnexion réussie.',
            'account_blocked' => 'Votre compte est bloqué.',
            'reset_email_if_exists' => 'Si le compte existe, un email de réinitialisation a été envoyé.',
            'password_too_short' => 'Mot de passe trop court.',
            'invalid_or_expired_token' => 'Jeton invalide ou expire.',
            'password_reset_success' => 'Mot de passe réinitialisé avec succès.',
            'profile_updated' => 'Profil mis à jour.',
            'account_removed' => 'Compte supprime.',
            'field_required' => 'Le champ %s est obligatoire.',
            'flight_updated' => 'Vol mis à jour.',
            'flight_created_notifications_sent' => 'Vol créé et notifications envoyées.',
            'flight_deleted' => 'Vol supprime.',
            'notification_already_sent' => 'Notification déjà envoyée.',
            'notification_sent_to_creator' => 'Notification envoyée au créateur du vol.',
            'admin_action_completed' => 'Action admin terminée.',
            'mail_subject_password_reset' => 'Reinitialisation du mot de passe',
            'mail_body_password_reset' => 'Réinitialisez votre mot de passe: %s',
            'mail_subject_new_flight' => 'Nouveau vol créé - %s',
            'mail_body_new_flight' => "Un nouveau vol a été créé dans jumpseat.\n\nTitre: %s\nTrajet: %s -> %s\nAéronef: %s\nDépart: %s\nRetour: %s\nDurée: %s minutes\nPlaces: %s\nPar: %s\nInfo: %s\nID du vol: %s",
            'mail_subject_interest' => 'Nouvelle demande jumpseat',
            'mail_body_interest' => 'Le pilote %s est intéressé par votre vol "%s" (départ: %s). Message: %s',
            'mail_subject_admin_reset' => "L'admin a réinitialisé votre mot de passe",
            'mail_body_admin_reset' => 'Mot de passe temporaire: %s',
            'disclaimer_button' => 'Avertissement',
            'disclaimer_title' => 'Avertissement',
            'disclaimer_text' => "L'utilisation de ce site et des services proposés relève de la seule et entière responsabilité des utilisateurs.
Les pilotes, passagers et membres de la plateforme sont responsables de vérifier la conformité légale, réglementaire, technique et sécuritaire de leurs activités aéronautiques.
L'éditeur du site n'exerce aucun contrôle sur les informations publiées, les compétences ou qualifications des pilotes, la sécurité des vols, ni sur le respect des obligations légales applicables à l'aviation.
En conséquence, l'éditeur ne saurait être tenu responsable, directement ou indirectement, de tout dommage, perte, incident ou accident survenant avant, pendant ou après un vol organisé via la plateforme.
En utilisant ce service, vous reconnaissez accepter pleinement ces conditions et exonérer l'éditeur de toute responsabilité relative à l'utilisation du site ou à la participation à un vol partagé.",
            'user_manual_title' => 'Manuel utilisateur',
        ],
    ];
    return $dict[$lang][$key] ?? $key;
}

function h(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function photo_url(?string $path): string
{
    $p = trim((string)$path);
    if ($p === '') {
        return '';
    }
    if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://') || str_starts_with($p, 'public/')) {
        return $p;
    }
    return 'public/' . ltrim($p, '/');
}

function set_flash(string $message): void
{
    $_SESSION['flash'] = $message;
}

function get_flash(): ?string
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = db()->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => (int)$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function require_auth(): void
{
    if (!current_user()) {
        header('Location: ?action=login');
        exit;
    }
}

function require_admin(): void
{
    $user = current_user();
    if (!$user || (int)$user['is_admin'] !== 1) {
        http_response_code(403);
        exit('Forbidden');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function validate_csrf(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(400);
        exit('Bad CSRF token');
    }
}

function week_bounds(int $offset = 0): array
{
    $today = new DateTimeImmutable('today');
    $monday = $today->modify('monday this week')->modify(sprintf('%+d week', $offset));
    $days = [];
    for ($i = 0; $i < 7; $i++) {
        $days[] = $monday->modify("+{$i} day");
    }
    return [$monday, $days];
}

function get_user_manual_text(): string
{
    $lang = $_SESSION['lang'] ?? 'fr';
    $manualPath = __DIR__ . '/../USER_MANUAL.md';
    if ($lang === 'fr') {
        $manualPath = __DIR__ . '/../MANUEL_UTILISATEUR.md';
    }
    if (!is_file($manualPath)) {
        return 'Manual file not found.';
    }
    $content = file_get_contents($manualPath);
    return $content === false ? 'Unable to load manual.' : $content;
}
