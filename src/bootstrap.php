<?php
declare(strict_types=1);

const APP_NAME = 'jumpseat';
const APP_VERSION = 'v1.0.0';
const APP_RELEASE_DATE = '2026-04-07';
const DB_PATH = __DIR__ . '/../data/jumpseat.sqlite';
const MAIL_LOG = __DIR__ . '/../data/mail.log';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('UTC');

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    init_schema($pdo);
    seed_admin($pdo);
    return $pdo;
}

function init_schema(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            photo_path TEXT NULL,
            phone TEXT NULL,
            aircraft_model_1 TEXT NULL,
            aircraft_model_2 TEXT NULL,
            aircraft_model_3 TEXT NULL,
            notify_new_flights INTEGER NOT NULL DEFAULT 1,
            is_admin INTEGER NOT NULL DEFAULT 0,
            is_blocked INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL
        )"
    );
    migrate_users_table($pdo);

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS password_resets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token TEXT NOT NULL UNIQUE,
            expires_at TEXT NOT NULL,
            used_at TEXT NULL,
            created_at TEXT NOT NULL,
            FOREIGN KEY(user_id) REFERENCES users(id)
        )"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS flights (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            origin TEXT NOT NULL,
            destination TEXT NOT NULL,
            aircraft_model TEXT NOT NULL,
            departure_at TEXT NOT NULL,
            return_at TEXT NOT NULL,
            duration_minutes INTEGER NOT NULL,
            seats_available INTEGER NOT NULL,
            additional_info TEXT NULL,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            FOREIGN KEY(user_id) REFERENCES users(id)
        )"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS interest_notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            flight_id INTEGER NOT NULL,
            from_user_id INTEGER NOT NULL,
            to_user_id INTEGER NOT NULL,
            message TEXT NOT NULL,
            created_at TEXT NOT NULL,
            FOREIGN KEY(flight_id) REFERENCES flights(id),
            FOREIGN KEY(from_user_id) REFERENCES users(id),
            FOREIGN KEY(to_user_id) REFERENCES users(id)
        )"
    );
}

function seed_admin(PDO $pdo): void
{
    $existing = $pdo->query("SELECT id FROM users WHERE is_admin = 1 LIMIT 1")->fetch();
    if ($existing) {
        return;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO users (username, email, password_hash, notify_new_flights, is_admin, created_at)
         VALUES (:username, :email, :hash, 1, 1, :created_at)"
    );
    $stmt->execute([
        'username' => 'admin',
        'email' => 'admin@jumpseat.local',
        'hash' => password_hash('Admin123!', PASSWORD_DEFAULT),
        'created_at' => gmdate('c'),
    ]);
}

function migrate_users_table(PDO $pdo): void
{
    $columns = $pdo->query("PRAGMA table_info(users)")->fetchAll();
    $columnNames = array_map(static fn(array $c): string => (string)$c['name'], $columns);

    if (!in_array('username', $columnNames, true)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN username TEXT");
    }
    if (!in_array('aircraft_model_1', $columnNames, true)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN aircraft_model_1 TEXT");
    }
    if (!in_array('aircraft_model_2', $columnNames, true)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN aircraft_model_2 TEXT");
    }
    if (!in_array('aircraft_model_3', $columnNames, true)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN aircraft_model_3 TEXT");
    }

    $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_users_username_unique ON users(username)");

    $rows = $pdo->query("SELECT id, email, username FROM users")->fetchAll();
    foreach ($rows as $row) {
        if (!empty($row['username'])) {
            continue;
        }
        $base = preg_replace('/[^a-zA-Z0-9_]/', '', strstr((string)$row['email'], '@', true) ?: 'pilot');
        if ($base === '') {
            $base = 'pilot';
        }
        $candidate = strtolower($base);
        $suffix = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
            $stmt->execute(['username' => $candidate, 'id' => (int)$row['id']]);
            if (!$stmt->fetch()) {
                break;
            }
            $candidate = strtolower($base) . $suffix;
            $suffix++;
        }
        $pdo->prepare("UPDATE users SET username = :username WHERE id = :id")
            ->execute(['username' => $candidate, 'id' => (int)$row['id']]);
    }
}

function send_mail_log(string $to, string $subject, string $body): void
{
    $line = sprintf(
        "[%s] TO:%s | SUBJECT:%s | BODY:%s\n",
        gmdate('c'),
        $to,
        $subject,
        str_replace(["\r", "\n"], [' ', ' '], $body)
    );
    file_put_contents(MAIL_LOG, $line, FILE_APPEND);
    @mail($to, $subject, $body);
}
