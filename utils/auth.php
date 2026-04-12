<?php
/**
 * utils/auth.php
 * Authentication guard helpers.
 *
 * Call these at the top of any page/endpoint AFTER session_start().
 * They terminate execution on failure — no return value needs to be checked.
 *
 * Include with: require_once __DIR__ . '/utils/auth.php';   (from project root)
 *           or: require_once __DIR__ . '/../utils/auth.php'; (from api/)
 */

/**
 * Require an active lawyer session (any role).
 * API callers receive a JSON 401; browser callers are redirected to login.
 */
function requireLawyer(): void
{
    if (empty($_SESSION['lawyer_id'])) {
        _authFail('login.php', 401);
    }
}

/**
 * Require an active admin session.
 * Non-admins receive a JSON 403 (API) or are redirected to the lawyer dashboard.
 */
function requireAdmin(): void
{
    requireLawyer();
    if (($_SESSION['lawyer_role'] ?? '') !== 'admin') {
        _authFail('lawyer_dashboard.php', 403);
    }
}

/**
 * Internal: redirect or emit a JSON error depending on whether the caller is an API script.
 *
 * @param string $redirect  Path to redirect browser callers to (no leading slash needed)
 * @param int    $code      HTTP status code (401 or 403)
 */
function _authFail(string $redirect, int $code): never
{
    $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
    $isApi      = str_contains($scriptPath, '/api/');

    if ($isApi) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'    => false,
            'error' => $code === 401 ? 'Not authenticated.' : 'Forbidden.',
        ]);
        exit;
    }

    header('Location: /' . ltrim($redirect, '/'));
    exit;
}
