<?php
/**
 * Taskora bootstrap: session, database, helpers, security.
 * Compatible with PHP 7.2+ / MariaDB 10.1+.
 */
require_once dirname(__DIR__) . '/config.php';

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

if (session_status() === PHP_SESSION_NONE) {
    session_name('taskora_sess');
    session_set_cookie_params(0, '/', '', false, true);
    session_start();
}

/* ---------- Base URL ---------- */
function base_url($path = '')
{
    static $base = null;
    if ($base === null) {
        if (BASE_URL_OVERRIDE !== '') {
            $base = rtrim(BASE_URL_OVERRIDE, '/');
        } else {
            $root = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
            $app  = str_replace('\\', '/', realpath(dirname(__DIR__)));
            $base = ($root && stripos($app, $root) === 0) ? substr($app, strlen($root)) : '';
            $base = '/' . trim($base, '/');
            if ($base === '/') $base = '';
        }
    }
    return $base . '/' . ltrim($path, '/');
}

function asset($path)
{
    $file = dirname(__DIR__) . '/assets/' . $path;
    $v = file_exists($file) ? filemtime($file) : 1;
    return base_url('assets/' . $path) . '?v=' . $v;
}

/* ---------- Database ---------- */
function db()
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $ex) {
            http_response_code(500);
            echo '<div style="font-family:sans-serif;max-width:640px;margin:80px auto;padding:32px;border:1px solid #d4af37;border-radius:14px">'
               . '<h2 style="color:#04382b">Database connection failed</h2>'
               . '<p>Start MySQL in the XAMPP Control Panel and import <code>database.sql</code> into a database named <b>' . DB_NAME . '</b> '
               . '(or open <a href="' . base_url('install.php') . '">install.php</a>).</p>'
               . (APP_DEBUG ? '<pre style="white-space:pre-wrap;color:#b42318">' . htmlspecialchars($ex->getMessage()) . '</pre>' : '')
               . '</div>';
            exit;
        }
    }
    return $pdo;
}

function q($sql, $params = [])
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}
function row($sql, $params = [])  { $r = q($sql, $params)->fetch(); return $r ?: null; }
function rows($sql, $params = []) { return q($sql, $params)->fetchAll(); }
function val($sql, $params = [])  { $v = q($sql, $params)->fetchColumn(); return $v === false ? null : $v; }

/* ---------- Escaping & formatting ---------- */
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/** Escape, then turn "\n" into <br> (for multi-line CMS titles). */
function nl($s) { return nl2br(e($s), false); }

/** Wrap the emphasis phrase of a title in <em>. */
function emph($title, $emphasis = '')
{
    $html = nl($title);
    if ($emphasis !== '' && $emphasis !== null) {
        $em = e($emphasis);
        $pos = strrpos($html, $em);
        if ($pos !== false) {
            $html = substr_replace($html, '<em>' . $em . '</em>', $pos, strlen($em));
        }
    }
    return $html;
}

function slugify($s)
{
    $s = strtolower(trim($s));
    $s = str_replace('&', 'and', $s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-');
}

function money($n) { return 'PKR ' . number_format((float)$n); }

function time_ago($dt)
{
    $d = time() - strtotime($dt);
    if ($d < 60) return 'just now';
    if ($d < 3600) return floor($d / 60) . ' min ago';
    if ($d < 86400) return floor($d / 3600) . ' h ago';
    if ($d < 604800) return floor($d / 86400) . ' d ago';
    return date('d M Y', strtotime($dt));
}

function lines($text)
{
    $out = [];
    foreach (preg_split('/\r\n|\r|\n/', (string)$text) as $l) {
        $l = trim($l);
        if ($l !== '') $out[] = $l;
    }
    return $out;
}

function make_ref($prefix)
{
    return strtoupper($prefix) . '-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}

/* ---------- Settings & CMS sections ---------- */
function setting($key, $default = '')
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (rows('SELECT skey, svalue FROM settings') as $r) $cache[$r['skey']] = $r['svalue'];
    }
    return (isset($cache[$key]) && $cache[$key] !== '') ? $cache[$key] : $default;
}

function section($key)
{
    static $cache = [];
    if (!array_key_exists($key, $cache)) {
        $r = row('SELECT * FROM sections WHERE skey = ?', [$key]);
        if ($r) {
            $r['data'] = json_decode($r['data'] ?: '{}', true) ?: [];
        }
        $cache[$key] = $r;
    }
    return $cache[$key];
}

/** Section is present and switched on. */
function on($s) { return $s && (int)$s['is_active'] === 1; }

/** Safe array getter. */
function g($arr, $key, $default = [])
{
    return (is_array($arr) && isset($arr[$key])) ? $arr[$key] : $default;
}

/* ---------- CSRF ---------- */
function csrf_token()
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check()
{
    $t = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!is_string($t) || !hash_equals(csrf_token(), $t)) {
        // 403 (not 419): Apache rewrites non-standard status codes to 500.
        if (is_ajax()) json_out(['ok' => false, 'message' => 'Session expired. Please refresh the page and try again.'], 403);
        http_response_code(403);
        exit('Session expired. Please go back, refresh and try again.');
    }
}

/* ---------- Request / response ---------- */
function is_post() { return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'; }
function is_ajax() { return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'; }
function post($k, $d = '') { return isset($_POST[$k]) ? (is_string($_POST[$k]) ? trim($_POST[$k]) : $_POST[$k]) : $d; }
function get($k, $d = '')  { return isset($_GET[$k]) && is_string($_GET[$k]) ? trim($_GET[$k]) : $d; }

function json_out($data, $code = 200)
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function redirect($path)
{
    $url = preg_match('#^https?://#', $path) ? $path : base_url($path);
    header('Location: ' . $url);
    exit;
}

function flash($type = null, $msg = null)
{
    if ($type !== null) { $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg]; return null; }
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function client_ip() { return substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45); }

/** Very small rate limiter (per session, per action). */
function throttle($action, $max = 5, $window = 60)
{
    $now = time();
    $k = 'thr_' . $action;
    $hits = array_filter($_SESSION[$k] ?? [], function ($t) use ($now, $window) { return $t > $now - $window; });
    if (count($hits) >= $max) return false;
    $hits[] = $now;
    $_SESSION[$k] = array_values($hits);
    return true;
}

/* ---------- Auth: customers & providers ---------- */
function current_user()
{
    static $u = false;
    if ($u === false) {
        $u = !empty($_SESSION['uid']) ? row('SELECT * FROM users WHERE id = ?', [$_SESSION['uid']]) : null;
    }
    return $u;
}
function require_user($role = null)
{
    $u = current_user();
    if (!$u) {
        flash('error', 'Please sign in to continue.');
        redirect('account/login.php');
    }
    if ($role && $u['role'] !== $role) redirect('account/index.php');
    return $u;
}

function notify($userId, $title, $body = '', $link = '')
{
    if (!$userId) return;
    q('INSERT INTO notifications (user_id, title, body, link) VALUES (?,?,?,?)', [$userId, $title, $body, $link]);
}

/* ---------- Auth: admin ---------- */
function current_admin()
{
    static $a = false;
    if ($a === false) {
        $a = !empty($_SESSION['admin_id']) ? row('SELECT * FROM admins WHERE id = ?', [$_SESSION['admin_id']]) : null;
    }
    return $a;
}
function require_admin()
{
    if (!current_admin()) redirect('admin/login.php');
    return current_admin();
}

/* ---------- Booking status vocabulary ---------- */
function booking_statuses()
{
    return [
        'requested'       => ['Requested', 'neutral'],
        'provider_joined' => ['Provider joined', 'info'],
        'quoted'          => ['Quotation shared', 'gold'],
        'deal_locked'     => ['Deal locked', 'green'],
        'in_progress'     => ['In progress', 'info'],
        'completed'       => ['Completed', 'green'],
        'cancelled'       => ['Cancelled', 'red'],
    ];
}
function status_pill($status)
{
    $map = booking_statuses();
    $s = $map[$status] ?? [ucwords(str_replace('_', ' ', $status)), 'neutral'];
    return '<span class="pill pill-' . $s[1] . '">' . e($s[0]) . '</span>';
}

function cities()
{
    $c = lines(setting('cities', "Islamabad\nRawalpindi\nLahore\nKarachi\nFaisalabad\nPeshawar\nMultan"));
    return $c;
}

require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/layout.php';
