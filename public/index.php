<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/helpers.php';

if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'fr'], true)) {
    $_SESSION['lang'] = $_GET['lang'];
}

if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'fr';
}

$action = $_GET['action'] ?? 'home';
$pdo = db();
$flash = get_flash();
$user = current_user();

if ($user && (int)$user['is_blocked'] === 1 && $action !== 'logout') {
    $_SESSION = [];
    set_flash(tr('account_blocked'));
    header('Location: ?action=login');
    exit;
}

function redirect(string $url): void
{
    header("Location: {$url}");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action !== 'login' && $action !== 'register' && $action !== 'forgot_password' && $action !== 'reset_password') {
    validate_csrf();
}

if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim((string)($_POST['username'] ?? '')));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $phone = trim((string)($_POST['phone'] ?? ''));
    $notify = isset($_POST['notify']) ? 1 : 0;
    $photoPath = null;

    if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
        set_flash(tr('invalid_username'));
        redirect('?action=register');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        set_flash(tr('invalid_email_or_password'));
        redirect('?action=register');
    }

    if (!empty($_FILES['photo']['name']) && (int)$_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo((string)$_FILES['photo']['name'], PATHINFO_EXTENSION);
        $target = __DIR__ . '/assets/uploads';
        if (!is_dir($target)) {
            mkdir($target, 0777, true);
        }
        $filename = bin2hex(random_bytes(8)) . '.' . preg_replace('/[^a-zA-Z0-9]/', '', $ext);
        $dest = $target . '/' . $filename;
        move_uploaded_file($_FILES['photo']['tmp_name'], $dest);
        $photoPath = 'public/assets/uploads/' . $filename;
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO users(username, email, password_hash, photo_path, phone, notify_new_flights, created_at)
             VALUES(:username, :email, :password_hash, :photo_path, :phone, :notify, :created_at)"
        );
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'photo_path' => $photoPath,
            'phone' => $phone ?: null,
            'notify' => $notify,
            'created_at' => gmdate('c'),
        ]);
        set_flash(tr('account_created'));
        redirect('?action=login');
    } catch (Throwable $e) {
        set_flash(tr('username_or_email_exists'));
        redirect('?action=register');
    }
}

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $u = $stmt->fetch();
    if (!$u || (int)$u['is_blocked'] === 1 || !password_verify($password, (string)$u['password_hash'])) {
        set_flash(tr('invalid_credentials'));
        redirect('?action=login');
    }
    $_SESSION['user_id'] = (int)$u['id'];
    redirect('?action=home');
}

if ($action === 'logout') {
    $_SESSION = [];
    session_destroy();
    session_start();
    set_flash(tr('logged_out'));
    redirect('?action=login');
}

if ($action === 'forgot_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string)($_POST['email'] ?? ''));
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $u = $stmt->fetch();
    if ($u) {
        $token = bin2hex(random_bytes(24));
        $stmt = $pdo->prepare(
            "INSERT INTO password_resets(user_id, token, expires_at, created_at)
             VALUES(:uid, :token, :expires_at, :created_at)"
        );
        $stmt->execute([
            'uid' => $u['id'],
            'token' => $token,
            'expires_at' => gmdate('c', time() + 3600),
            'created_at' => gmdate('c'),
        ]);
        $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/index.php?action=reset_password&token=' . $token;
        send_mail_log((string)$u['email'], tr('mail_subject_password_reset'), sprintf(tr('mail_body_password_reset'), $url));
    }
    set_flash(tr('reset_email_if_exists'));
    redirect('?action=forgot_password');
}

if ($action === 'reset_password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim((string)($_POST['token'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if (strlen($password) < 8) {
        set_flash(tr('password_too_short'));
        redirect('?action=reset_password&token=' . urlencode($token));
    }
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = :token AND used_at IS NULL LIMIT 1");
    $stmt->execute(['token' => $token]);
    $r = $stmt->fetch();
    if (!$r || strtotime((string)$r['expires_at']) < time()) {
        set_flash(tr('invalid_or_expired_token'));
        redirect('?action=forgot_password');
    }
    $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id")->execute([
        'hash' => password_hash($password, PASSWORD_DEFAULT),
        'id' => $r['user_id'],
    ]);
    $pdo->prepare("UPDATE password_resets SET used_at = :used_at WHERE id = :id")->execute([
        'used_at' => gmdate('c'),
        'id' => $r['id'],
    ]);
    set_flash(tr('password_reset_success'));
    redirect('?action=login');
}

if ($action === 'profile_save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_auth();
    $user = current_user();
    $phone = trim((string)($_POST['phone'] ?? ''));
    $notify = isset($_POST['notify']) ? 1 : 0;
    $password = (string)($_POST['password'] ?? '');
    $aircraftModel1 = trim((string)($_POST['aircraft_model_1'] ?? ''));
    $aircraftModel2 = trim((string)($_POST['aircraft_model_2'] ?? ''));
    $aircraftModel3 = trim((string)($_POST['aircraft_model_3'] ?? ''));
    $query = "UPDATE users SET phone = :phone, notify_new_flights = :notify, aircraft_model_1 = :aircraft_model_1, aircraft_model_2 = :aircraft_model_2, aircraft_model_3 = :aircraft_model_3";
    $params = [
        'phone' => $phone ?: null,
        'notify' => $notify,
        'aircraft_model_1' => $aircraftModel1 !== '' ? $aircraftModel1 : null,
        'aircraft_model_2' => $aircraftModel2 !== '' ? $aircraftModel2 : null,
        'aircraft_model_3' => $aircraftModel3 !== '' ? $aircraftModel3 : null,
        'id' => $user['id'],
    ];
    if ($password !== '') {
        if (strlen($password) < 8) {
            set_flash(tr('password_too_short'));
            redirect('?action=profile');
        }
        $query .= ", password_hash = :hash";
        $params['hash'] = password_hash($password, PASSWORD_DEFAULT);
    }
    if (!empty($_FILES['photo']['name']) && (int)$_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo((string)$_FILES['photo']['name'], PATHINFO_EXTENSION);
        $target = __DIR__ . '/assets/uploads';
        if (!is_dir($target)) {
            mkdir($target, 0777, true);
        }
        $filename = bin2hex(random_bytes(8)) . '.' . preg_replace('/[^a-zA-Z0-9]/', '', $ext);
        $dest = $target . '/' . $filename;
        move_uploaded_file($_FILES['photo']['tmp_name'], $dest);
        $query .= ", photo_path = :photo_path";
        $params['photo_path'] = 'public/assets/uploads/' . $filename;
    }
    $query .= " WHERE id = :id";
    $pdo->prepare($query)->execute($params);
    set_flash(tr('profile_updated'));
    redirect('?action=profile');
}

if ($action === 'deregister' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_auth();
    $user = current_user();
    $pdo->prepare("DELETE FROM flights WHERE user_id = :id")->execute(['id' => $user['id']]);
    $pdo->prepare("DELETE FROM users WHERE id = :id")->execute(['id' => $user['id']]);
    $_SESSION = [];
    session_destroy();
    session_start();
    set_flash(tr('account_removed'));
    redirect('?action=register');
}

if ($action === 'flight_save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_auth();
    $user = current_user();
    $id = (int)($_POST['id'] ?? 0);
    $departureInput = trim((string)($_POST['departure_at'] ?? ''));
    $returnInput = trim((string)($_POST['return_at'] ?? ''));
    $departureNormalized = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $departureInput)));
    $returnNormalized = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $returnInput)));
    $fields = [
        'title' => trim((string)($_POST['title'] ?? '')),
        'origin' => trim((string)($_POST['origin'] ?? '')),
        'destination' => trim((string)($_POST['destination'] ?? '')),
        'aircraft_model' => trim((string)($_POST['aircraft_model'] ?? '')),
        'departure_at' => $departureNormalized,
        'return_at' => $returnNormalized,
        'duration_minutes' => (int)($_POST['duration_minutes'] ?? 0),
        'seats_available' => (int)($_POST['seats_available'] ?? 0),
        'additional_info' => trim((string)($_POST['additional_info'] ?? '')),
    ];
    foreach (['title', 'origin', 'destination', 'aircraft_model', 'departure_at', 'return_at'] as $k) {
        if ($fields[$k] === '') {
            set_flash(sprintf(tr('field_required'), $k));
            redirect('?action=flight_edit&id=' . $id);
        }
    }
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM flights WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $flight = $stmt->fetch();
        if (!$flight || ((int)$flight['user_id'] !== (int)$user['id'] && (int)$user['is_admin'] !== 1)) {
            http_response_code(403);
            exit('Forbidden');
        }
        $stmt = $pdo->prepare(
            "UPDATE flights SET
                title=:title, origin=:origin, destination=:destination, aircraft_model=:aircraft_model,
                departure_at=:departure_at, return_at=:return_at, duration_minutes=:duration_minutes,
                seats_available=:seats_available, additional_info=:additional_info, updated_at=:updated_at
             WHERE id=:id"
        );
        $stmt->execute($fields + ['updated_at' => gmdate('c'), 'id' => $id]);
        set_flash(tr('flight_updated'));
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO flights
             (user_id, title, origin, destination, aircraft_model, departure_at, return_at, duration_minutes, seats_available, additional_info, created_at, updated_at)
             VALUES
             (:user_id, :title, :origin, :destination, :aircraft_model, :departure_at, :return_at, :duration_minutes, :seats_available, :additional_info, :created_at, :updated_at)"
        );
        $stmt->execute($fields + ['user_id' => $user['id'], 'created_at' => gmdate('c'), 'updated_at' => gmdate('c')]);
        $flightId = (int)$pdo->lastInsertId();

        $watchers = $pdo->query("SELECT email FROM users WHERE notify_new_flights = 1 AND is_blocked = 0")->fetchAll();
        $mailSubject = sprintf(tr('mail_subject_new_flight'), $fields['title']);
        $mailBody = sprintf(
            tr('mail_body_new_flight'),
            $fields['title'],
            $fields['origin'],
            $fields['destination'],
            $fields['aircraft_model'],
            $fields['departure_at'],
            $fields['return_at'],
            (string)$fields['duration_minutes'],
            (string)$fields['seats_available'],
            (string)$user['username'],
            ($fields['additional_info'] !== '' ? $fields['additional_info'] : '-'),
            (string)$flightId
        );
        foreach ($watchers as $watcher) {
            send_mail_log((string)$watcher['email'], $mailSubject, $mailBody);
        }
        set_flash(tr('flight_created_notifications_sent'));
    }
    redirect('?action=home');
}

if ($action === 'flight_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_auth();
    $user = current_user();
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare("SELECT * FROM flights WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $flight = $stmt->fetch();
    if ($flight && ((int)$flight['user_id'] === (int)$user['id'] || (int)$user['is_admin'] === 1)) {
        $pdo->prepare("DELETE FROM flights WHERE id = :id")->execute(['id' => $id]);
        set_flash(tr('flight_deleted'));
    }
    redirect('?action=home');
}

if ($action === 'notify_interest' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_auth();
    $fromUser = current_user();
    $flightId = (int)($_POST['flight_id'] ?? 0);
    $message = trim((string)($_POST['message'] ?? ''));
    $stmt = $pdo->prepare(
        "SELECT f.*, u.email as owner_email, u.id as owner_id
         FROM flights f
         JOIN users u ON u.id = f.user_id
         WHERE f.id = :id"
    );
    $stmt->execute(['id' => $flightId]);
    $f = $stmt->fetch();
    if ($f && (int)$f['seats_available'] > 0) {
        $owner = (int)$f['owner_id'];
        $dupeStmt = $pdo->prepare(
            "SELECT id FROM interest_notifications
             WHERE flight_id = :flight_id
               AND from_user_id = :from_user_id
               AND to_user_id = :to_user_id
               AND message = :message
               AND datetime(created_at) >= datetime(:min_created_at)
             LIMIT 1"
        );
        $dupeStmt->execute([
            'flight_id' => $flightId,
            'from_user_id' => $fromUser['id'],
            'to_user_id' => $owner,
            'message' => $message,
            'min_created_at' => gmdate('c', time() - 60),
        ]);
        $existing = $dupeStmt->fetch();
        if ($existing) {
            set_flash(tr('notification_already_sent'));
            redirect('?action=home');
        }

        $pdo->prepare(
            "INSERT INTO interest_notifications(flight_id, from_user_id, to_user_id, message, created_at)
             VALUES (:flight_id, :from_user_id, :to_user_id, :message, :created_at)"
        )->execute([
            'flight_id' => $flightId,
            'from_user_id' => $fromUser['id'],
            'to_user_id' => $owner,
            'message' => $message,
            'created_at' => gmdate('c'),
        ]);
        $body = sprintf(tr('mail_body_interest'), (string)$fromUser['username'], (string)$f['title'], (string)$f['departure_at'], $message);
        send_mail_log((string)$f['owner_email'], tr('mail_subject_interest'), $body);
        set_flash(tr('notification_sent_to_creator'));
    }
    redirect('?action=home');
}

if ($action === 'admin_user_action' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    $targetId = (int)($_POST['target_id'] ?? 0);
    $type = (string)($_POST['type'] ?? '');
    if ($targetId > 0) {
        if ($type === 'block') {
            $pdo->prepare("UPDATE users SET is_blocked = 1 WHERE id = :id")->execute(['id' => $targetId]);
        } elseif ($type === 'unblock') {
            $pdo->prepare("UPDATE users SET is_blocked = 0 WHERE id = :id")->execute(['id' => $targetId]);
        } elseif ($type === 'delete') {
            $pdo->prepare("DELETE FROM users WHERE id = :id AND is_admin = 0")->execute(['id' => $targetId]);
        } elseif ($type === 'reset_password') {
            $tempPass = 'Temp' . random_int(10000, 99999) . '!';
            $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id")->execute([
                'hash' => password_hash($tempPass, PASSWORD_DEFAULT),
                'id' => $targetId,
            ]);
            $stmt = $pdo->prepare("SELECT email FROM users WHERE id = :id");
            $stmt->execute(['id' => $targetId]);
            $t = $stmt->fetch();
            if ($t) {
                send_mail_log((string)$t['email'], tr('mail_subject_admin_reset'), sprintf(tr('mail_body_admin_reset'), $tempPass));
            }
        }
        set_flash(tr('admin_action_completed'));
    }
    redirect('?action=admin_users');
}

function render_header(string $title, ?array $user, ?string $flash): void
{
    ?>
    <!doctype html>
    <html lang="<?= h($_SESSION['lang'] ?? 'fr') ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= h($title) ?></title>
        <link rel="stylesheet" href="public/assets/styles.css">
    </head>
    <body
      data-lbl-title="<?= h(tr('title')) ?>"
      data-lbl-route="<?= h(tr('route')) ?>"
      data-lbl-aircraft="<?= h(tr('aircraft')) ?>"
      data-lbl-departure="<?= h(tr('departure')) ?>"
      data-lbl-return="<?= h(tr('return')) ?>"
      data-lbl-duration="<?= h(tr('planned_duration_minutes')) ?>"
      data-lbl-seats="<?= h(tr('seats')) ?>"
      data-lbl-by="<?= h(tr('by')) ?>"
      data-lbl-info="<?= h(tr('additional_information')) ?>"
      data-lbl-flight-details="<?= h(tr('flight_details')) ?>"
      data-lbl-no-seat="<?= h(tr('no_seat_available')) ?>"
    >
    <header class="topbar">
        <div class="brand">jumpseat</div>
        <nav>
            <a href="?action=home"><?= h(tr('calendar')) ?></a>
            <?php if ($user): ?>
                <span class="user-pill"><?= h((string)$user['username']) ?></span>
                <a href="?action=flight_edit"><?= h(tr('new_flight')) ?></a>
                <a href="?action=my_flights"><?= h(tr('my_flights')) ?></a>
                <a href="?action=profile"><?= h(tr('profile')) ?></a>
                <?php if ((int)$user['is_admin'] === 1): ?><a href="?action=admin_users"><?= h(tr('admin')) ?></a><?php endif; ?>
                <a href="?action=logout"><?= h(tr('logout')) ?></a>
            <?php else: ?>
                <a href="?action=login"><?= h(tr('login')) ?></a>
                <a href="?action=register"><?= h(tr('register')) ?></a>
            <?php endif; ?>
            <a href="?action=<?= h($GLOBALS['action']) ?>&lang=en">EN</a>
            <a href="?action=<?= h($GLOBALS['action']) ?>&lang=fr">FR</a>
            <button type="button" id="manualBtn" class="topbar-help-btn">?</button>
        </nav>
    </header>
    <main class="container">
        <?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
    <?php
}

function render_footer(): void
{
    ?>
    </main>
    <div class="footer-actions">
        <button type="button" id="disclaimerBtn" class="btn"><?= h(tr('disclaimer_button')) ?></button>
    </div>
    <div class="app-version">
        <?= h(APP_NAME) ?> <?= h(APP_VERSION) ?> - <?= h(APP_RELEASE_DATE) ?>
    </div>
    <div id="manualModal" class="modal hidden">
        <div class="modal-content disclaimer-modal-content">
            <h3><?= h(tr('user_manual_title')) ?></h3>
            <pre class="manual-pre"><?= h(get_user_manual_text()) ?></pre>
            <div class="row">
                <button type="button" id="manualCloseBtn"><?= h(tr('close')) ?></button>
            </div>
        </div>
    </div>
    <div id="disclaimerModal" class="modal hidden">
        <div class="modal-content disclaimer-modal-content">
            <h3><?= h(tr('disclaimer_title')) ?></h3>
            <p class="modal-preline"><?= nl2br(h(tr('disclaimer_text'))) ?></p>
            <div class="row">
                <button type="button" id="disclaimerCloseBtn"><?= h(tr('close')) ?></button>
            </div>
        </div>
    </div>
    <div id="globalSpinner" class="global-spinner hidden" aria-hidden="true">
        <div class="global-spinner-box">
            <div class="global-spinner-wheel"></div>
            <div><?= h(tr('loading')) ?></div>
        </div>
    </div>
    <script src="public/assets/app.js"></script>
    </body>
    </html>
    <?php
}

if ($action === 'register') {
    render_header('Register', $user, $flash);
    ?>
    <h1><?= h(tr('register')) ?></h1>
    <form method="post" action="?action=register" enctype="multipart/form-data" class="card">
        <label><?= h(tr('username')) ?>* <input type="text" name="username" required minlength="3" maxlength="30" pattern="[a-zA-Z0-9_]+"></label>
        <label><?= h(tr('email')) ?>* <input type="email" name="email" required></label>
        <label><?= h(tr('password')) ?>* <input type="password" name="password" required minlength="8"></label>
        <label><?= h(tr('photo')) ?> <input type="file" name="photo" accept="image/*"></label>
        <label><?= h(tr('phone')) ?> <input type="text" name="phone"></label>
        <label><input type="checkbox" name="notify" checked> <?= h(tr('notify_new_flights_label')) ?></label>
        <button type="submit"><?= h(tr('register')) ?></button>
    </form>
    <?php
    render_footer();
    exit;
}

if ($action === 'login') {
    render_header('Login', $user, $flash);
    ?>
    <h1><?= h(tr('login')) ?></h1>
    <form method="post" action="?action=login" class="card">
        <label><?= h(tr('email')) ?> <input type="email" name="email" required></label>
        <label><?= h(tr('password')) ?> <input type="password" name="password" required></label>
        <button type="submit"><?= h(tr('login')) ?></button>
        <a href="?action=forgot_password"><?= h(tr('forgot_password')) ?></a>
    </form>
    <?php
    render_footer();
    exit;
}

if ($action === 'forgot_password') {
    render_header('Forgot Password', $user, $flash);
    ?>
    <h1><?= h(tr('forgot_password')) ?></h1>
    <form method="post" action="?action=forgot_password" class="card">
        <label><?= h(tr('email')) ?> <input type="email" name="email" required></label>
        <button type="submit"><?= h(tr('send')) ?></button>
    </form>
    <?php
    render_footer();
    exit;
}

if ($action === 'reset_password') {
    render_header('Reset Password', $user, $flash);
    $token = (string)($_GET['token'] ?? '');
    ?>
    <h1><?= h(tr('reset_password')) ?></h1>
    <form method="post" action="?action=reset_password" class="card">
        <input type="hidden" name="token" value="<?= h($token) ?>">
        <label><?= h(tr('new_password')) ?> <input type="password" name="password" required minlength="8"></label>
        <button type="submit"><?= h(tr('save')) ?></button>
    </form>
    <?php
    render_footer();
    exit;
}

if ($action === 'profile') {
    require_auth();
    $user = current_user();
    render_header('Profile', $user, $flash);
    ?>
    <h1><?= h(tr('profile')) ?></h1>
    <form method="post" action="?action=profile_save" class="card" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <?php if (!empty($user['photo_path'])): ?>
            <img class="profile-photo" src="<?= h(photo_url((string)$user['photo_path'])) ?>" alt="User photo">
        <?php endif; ?>
        <label><?= h(tr('username')) ?> <input type="text" value="<?= h((string)$user['username']) ?>" disabled></label>
        <label><?= h(tr('email')) ?> <input type="email" value="<?= h($user['email']) ?>" disabled></label>
        <label><?= h(tr('change_photo')) ?> <input type="file" name="photo" accept="image/*"></label>
        <label><?= h(tr('aircraft_model_1_label')) ?> <input type="text" name="aircraft_model_1" value="<?= h((string)($user['aircraft_model_1'] ?? '')) ?>"></label>
        <label><?= h(tr('aircraft_model_2_label')) ?> <input type="text" name="aircraft_model_2" value="<?= h((string)($user['aircraft_model_2'] ?? '')) ?>"></label>
        <label><?= h(tr('aircraft_model_3_label')) ?> <input type="text" name="aircraft_model_3" value="<?= h((string)($user['aircraft_model_3'] ?? '')) ?>"></label>
        <label><?= h(tr('phone')) ?> <input type="text" name="phone" value="<?= h($user['phone']) ?>"></label>
        <label><?= h(tr('new_password')) ?> <input type="password" name="password" minlength="8"></label>
        <label><input type="checkbox" name="notify" <?= (int)$user['notify_new_flights'] === 1 ? 'checked' : '' ?>> <?= h(tr('notify_new_flights_label')) ?></label>
        <button type="submit"><?= h(tr('save')) ?></button>
    </form>
    <form method="post" action="?action=deregister" class="card">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <button type="submit" class="danger"><?= h(tr('deregister_account')) ?></button>
    </form>
    <?php
    render_footer();
    exit;
}

if ($action === 'flight_edit') {
    require_auth();
    $user = current_user();
    $id = (int)($_GET['id'] ?? 0);
    $flight = [
        'id' => 0, 'title' => '', 'origin' => '', 'destination' => '', 'aircraft_model' => '',
        'departure_at' => '', 'return_at' => '', 'duration_minutes' => 0, 'seats_available' => 0, 'additional_info' => '',
    ];
    $availableAircraftModels = array_values(array_filter([
        trim((string)($user['aircraft_model_1'] ?? '')),
        trim((string)($user['aircraft_model_2'] ?? '')),
        trim((string)($user['aircraft_model_3'] ?? '')),
    ], static fn(string $m): bool => $m !== ''));
    if ($id === 0 && count($availableAircraftModels) > 0) {
        $flight['aircraft_model'] = $availableAircraftModels[0];
    }
    if ($id === 0) {
        $prefillDate = trim((string)($_GET['prefill_date'] ?? ''));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $prefillDate)) {
            $prefillDate = (new DateTimeImmutable('today'))->format('Y-m-d');
        }
        $flight['departure_at'] = $prefillDate . ' 08:00';
        $flight['return_at'] = $prefillDate . ' 17:00';
    }
    if ($id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM flights WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $f = $stmt->fetch();
        if ($f) {
            $flight = $f;
        }
    }
    render_header('Flight', $user, $flash);
    ?>
    <h1><?= h($id > 0 ? tr('edit_flight') : tr('create_flight')) ?></h1>
    <form method="post" action="?action=flight_save" class="card">
        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= h((string)$flight['id']) ?>">
        <label><?= h(tr('flight_title')) ?> <input type="text" name="title" required value="<?= h($flight['title']) ?>"></label>
        <label><?= h(tr('origin')) ?> <input type="text" name="origin" required value="<?= h($flight['origin']) ?>"></label>
        <label><?= h(tr('destination')) ?> <input type="text" name="destination" required value="<?= h($flight['destination']) ?>"></label>
        <label><?= h(tr('aircraft')) ?>
            <select name="aircraft_model" required>
                <?php if (count($availableAircraftModels) === 0): ?>
                    <option value=""><?= h(tr('add_aircraft_models_first')) ?></option>
                <?php else: ?>
                    <?php foreach ($availableAircraftModels as $model): ?>
                        <option value="<?= h($model) ?>" <?= (string)$flight['aircraft_model'] === $model ? 'selected' : '' ?>><?= h($model) ?></option>
                    <?php endforeach; ?>
                    <?php if ((string)$flight['aircraft_model'] !== '' && !in_array((string)$flight['aircraft_model'], $availableAircraftModels, true)): ?>
                        <option value="<?= h((string)$flight['aircraft_model']) ?>" selected><?= h((string)$flight['aircraft_model']) ?></option>
                    <?php endif; ?>
                <?php endif; ?>
            </select>
        </label>
        <label><?= h(tr('planned_departure_local')) ?> <input type="datetime-local" name="departure_at" required value="<?= h(str_replace(' ', 'T', substr((string)$flight['departure_at'], 0, 16))) ?>"></label>
        <label><?= h(tr('planned_return_local')) ?> <input type="datetime-local" name="return_at" required value="<?= h(str_replace(' ', 'T', substr((string)$flight['return_at'], 0, 16))) ?>"></label>
        <label><?= h(tr('planned_duration_minutes')) ?>
            <select name="duration_minutes" required>
                <?php for ($minutes = 15; $minutes <= 720; $minutes += 15): ?>
                    <option value="<?= h((string)$minutes) ?>" <?= (int)$flight['duration_minutes'] === $minutes ? 'selected' : '' ?>><?= h((string)$minutes) ?></option>
                <?php endfor; ?>
                <?php if ((int)$flight['duration_minutes'] > 0 && ((int)$flight['duration_minutes'] % 15 !== 0 || (int)$flight['duration_minutes'] > 720)): ?>
                    <option value="<?= h((string)$flight['duration_minutes']) ?>" selected><?= h((string)$flight['duration_minutes']) ?></option>
                <?php endif; ?>
            </select>
        </label>
        <label><?= h(tr('seats_available')) ?>
            <select name="seats_available" required>
                <?php for ($seats = 0; $seats <= 3; $seats++): ?>
                    <option value="<?= h((string)$seats) ?>" <?= (int)$flight['seats_available'] === $seats ? 'selected' : '' ?>><?= h((string)$seats) ?></option>
                <?php endfor; ?>
                <?php if ((int)$flight['seats_available'] > 3): ?>
                    <option value="<?= h((string)$flight['seats_available']) ?>" selected><?= h((string)$flight['seats_available']) ?></option>
                <?php endif; ?>
            </select>
        </label>
        <label><?= h(tr('additional_information')) ?> <textarea name="additional_info"><?= h($flight['additional_info']) ?></textarea></label>
        <div class="row">
            <button type="button" onclick="window.history.back()"><?= h(tr('cancel')) ?></button>
            <button type="submit"><?= h(tr('save')) ?></button>
        </div>
    </form>
    <?php
    render_footer();
    exit;
}

if ($action === 'admin_users') {
    require_admin();
    $users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
    render_header(tr('admin_users'), $user, $flash);
    ?>
    <h1><?= h(tr('admin_users')) ?></h1>
    <div class="card">
        <table>
            <thead><tr><th>ID</th><th><?= h(tr('email')) ?></th><th><?= h(tr('phone')) ?></th><th><?= h(tr('status')) ?></th><th><?= h(tr('actions')) ?></th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= h((string)$u['id']) ?></td>
                    <td><?= h($u['email']) ?></td>
                    <td><?= h($u['phone']) ?></td>
                    <td><?= (int)$u['is_blocked'] ? h(tr('blocked')) : h(tr('active')) ?></td>
                    <td>
                        <form method="post" action="?action=admin_user_action" class="inline">
                            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="target_id" value="<?= h((string)$u['id']) ?>">
                            <button name="type" value="<?= (int)$u['is_blocked'] ? 'unblock' : 'block' ?>">
                                <?= (int)$u['is_blocked'] ? h(tr('unblock')) : h(tr('block')) ?>
                            </button>
                            <?php if ((int)$u['is_admin'] !== 1): ?><button name="type" value="delete" class="danger"><?= h(tr('delete')) ?></button><?php endif; ?>
                            <button name="type" value="reset_password"><?= h(tr('reset_password_admin')) ?></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    render_footer();
    exit;
}

if ($action === 'my_flights') {
    require_auth();
    $user = current_user();
    $stmt = $pdo->prepare("SELECT * FROM flights WHERE user_id = :user_id ORDER BY departure_at DESC");
    $stmt->execute(['user_id' => $user['id']]);
    $myFlights = $stmt->fetchAll();
    render_header(tr('my_flights'), $user, $flash);
    ?>
    <h1><?= h(tr('my_flights')) ?></h1>
    <div class="card">
        <?php if (count($myFlights) === 0): ?>
            <p><?= h(tr('no_flight_yet')) ?></p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th><?= h(tr('title')) ?></th>
                    <th><?= h(tr('route')) ?></th>
                    <th><?= h(tr('aircraft')) ?></th>
                    <th><?= h(tr('departure')) ?></th>
                    <th><?= h(tr('return')) ?></th>
                    <th><?= h(tr('seats')) ?></th>
                    <th><?= h(tr('actions')) ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($myFlights as $f): ?>
                    <tr>
                        <td><?= h($f['title']) ?></td>
                        <td><?= h($f['origin']) ?> -> <?= h($f['destination']) ?></td>
                        <td><?= h($f['aircraft_model']) ?></td>
                        <td><?= h($f['departure_at']) ?></td>
                        <td><?= h($f['return_at']) ?></td>
                        <td><?= h((string)$f['seats_available']) ?></td>
                        <td>
                            <form method="post" action="?action=flight_delete" class="inline">
                                <a href="?action=flight_edit&id=<?= h((string)$f['id']) ?>"><?= h(tr('edit')) ?></a>
                                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="id" value="<?= h((string)$f['id']) ?>">
                                <button type="submit" class="link danger" onclick="return confirm('<?= h(tr('confirm_delete_flight')) ?>');"><?= h(tr('delete')) ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
    render_footer();
    exit;
}

if ($action === 'home') {
    $weekOffset = (int)($_GET['week'] ?? 0);
    [$monday, $days] = week_bounds($weekOffset);
    $start = $days[0]->setTime(0, 0)->format('Y-m-d H:i:s');
    $end = $days[6]->setTime(23, 59, 59)->format('Y-m-d H:i:s');

    $stmt = $pdo->prepare(
        "SELECT f.*, u.email AS owner_email, u.username AS owner_username, u.photo_path AS owner_photo_path
         FROM flights f
         JOIN users u ON u.id = f.user_id
         WHERE datetime(replace(f.departure_at, 'T', ' ')) <= datetime(:week_end)
           AND datetime(replace(f.return_at, 'T', ' ')) >= datetime(:week_start)
         ORDER BY f.departure_at ASC"
    );
    $stmt->execute(['week_end' => $end, 'week_start' => $start]);
    $flights = $stmt->fetchAll();
    render_header(tr('app_title'), $user, $flash);
    ?>
    <h1><?= h(tr('calendar')) ?></h1>
    <div class="week-nav">
        <div class="week-nav-actions">
            <a class="btn" href="?action=home&week=<?= h((string)($weekOffset - 1)) ?>">&larr; <?= h(tr('week_prev')) ?></a>
            <a class="btn" href="?action=home&week=0"><?= h(tr('today')) ?></a>
        </div>
        <strong><?= h($days[0]->format('Y-m-d')) ?> - <?= h($days[6]->format('Y-m-d')) ?></strong>
        <a class="btn" href="?action=home&week=<?= h((string)($weekOffset + 1)) ?>"><?= h(tr('week_next')) ?> &rarr;</a>
    </div>
    <div class="calendar-grid">
        <?php foreach ($days as $day): ?>
            <section class="day" data-date="<?= h($day->format('Y-m-d')) ?>">
                <h3><?= h($day->format('D d M')) ?></h3>
                <?php foreach ($flights as $f): ?>
                    <?php
                    $dep = new DateTimeImmutable((string)$f['departure_at']);
                    $ret = new DateTimeImmutable((string)$f['return_at']);
                    $inside = $day->setTime(12, 0) >= $dep->setTime(0, 0) && $day->setTime(12, 0) <= $ret->setTime(23, 59);
                    if (!$inside) {
                        continue;
                    }
                    $clickable = $user && (int)$f['seats_available'] > 0;
                    ?>
                    <article
                        class="flight-event <?= $clickable ? 'clickable' : 'disabled' ?>"
                        data-title="<?= h($f['title']) ?>"
                        data-origin="<?= h($f['origin']) ?>"
                        data-destination="<?= h($f['destination']) ?>"
                        data-model="<?= h($f['aircraft_model']) ?>"
                        data-departure="<?= h($f['departure_at']) ?>"
                        data-return="<?= h($f['return_at']) ?>"
                        data-duration="<?= h((string)$f['duration_minutes']) ?>"
                        data-seats="<?= h((string)$f['seats_available']) ?>"
                        data-owner="<?= h((string)$f['owner_username']) ?>"
                        data-photo="<?= h(photo_url((string)$f['owner_photo_path'])) ?>"
                        data-info="<?= h($f['additional_info']) ?>"
                        data-flight-id="<?= h((string)$f['id']) ?>"
                        data-user-id="<?= h((string)$f['user_id']) ?>"
                    >
                        <div><strong><?= h($f['title']) ?></strong></div>
                        <div><?= h($f['origin']) ?> -> <?= h($f['destination']) ?></div>
                        <div><?= h(tr('aircraft')) ?>: <?= h($f['aircraft_model']) ?></div>
                        <div><?= h(tr('by')) ?>: <?= h((string)$f['owner_username']) ?></div>
                        <div><?= h(tr('seats')) ?>: <?= h((string)$f['seats_available']) ?></div>
                        <?php if ($user && ((int)$user['id'] === (int)$f['user_id'] || (int)$user['is_admin'] === 1)): ?>
                            <div class="event-actions">
                                <a href="?action=flight_edit&id=<?= h((string)$f['id']) ?>"><?= h(tr('edit')) ?></a>
                                <form method="post" action="?action=flight_delete">
                                    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                                    <input type="hidden" name="id" value="<?= h((string)$f['id']) ?>">
                                    <button type="submit" class="link danger" onclick="return confirm('<?= h(tr('confirm_delete_flight')) ?>');"><?= h(tr('delete')) ?></button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>
    </div>
    <div class="week-nav week-nav-bottom">
        <div class="week-nav-actions">
            <a class="btn" href="?action=home&week=<?= h((string)($weekOffset - 1)) ?>">&larr; <?= h(tr('week_prev')) ?></a>
            <a class="btn" href="?action=home&week=0"><?= h(tr('today')) ?></a>
        </div>
        <strong><?= h($days[0]->format('Y-m-d')) ?> - <?= h($days[6]->format('Y-m-d')) ?></strong>
        <a class="btn" href="?action=home&week=<?= h((string)($weekOffset + 1)) ?>"><?= h(tr('week_next')) ?> &rarr;</a>
    </div>

    <div id="flightInfoModal" class="modal hidden">
        <div class="modal-content">
            <img id="modalUserPhoto" class="modal-user-photo hidden" src="" alt="Pilot photo">
            <h3 id="modalTitle"><?= h(tr('flight_details')) ?></h3>
            <p id="modalBody"></p>
            <div class="row">
                <?php if ($user): ?>
                    <button type="button" id="contactFlightBtn" disabled><?= h(tr('contact')) ?></button>
                <?php endif; ?>
                <button type="button" data-close><?= h(tr('close')) ?></button>
            </div>
        </div>
    </div>

    <?php if ($user): ?>
    <div id="interestModal" class="modal hidden">
        <div class="modal-content">
            <h3><?= h(tr('send_interest_notification')) ?></h3>
            <form method="post" action="?action=notify_interest">
                <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="flight_id" id="interestFlightId">
                <label><?= h(tr('your_message')) ?>
                    <textarea name="message" required maxlength="500"></textarea>
                </label>
                <div class="row">
                    <button type="submit"><?= h(tr('send')) ?></button>
                    <button type="button" data-close><?= h(tr('cancel')) ?></button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php
    render_footer();
    exit;
}

http_response_code(404);
echo 'Not found';
