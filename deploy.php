<?php
// ============================================================
// Mechlab Auto-Deploy Webhook
// Triggered by GitHub on every push to main
// ============================================================

$secret = 'MECHLAB_DEPLOY_SECRET_2026';

$payload   = file_get_contents('php://input');
$signature = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (!hash_equals($signature, $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '')) {
    http_response_code(403);
    die(json_encode(['error' => 'Invalid signature']));
}

$repo_path   = '/home/welltechbdcp/repositories/Mechlab_Website';
$public_html = '/home/welltechbdcp/mechlabbd.com';

chdir($repo_path);
exec('git fetch origin main 2>&1', $pull_out);
exec('git reset --hard origin/main 2>&1', $reset_out);
$pull_out = array_merge($pull_out, $reset_out);

exec("cp -rf {$repo_path}/* {$public_html}/ 2>&1", $copy_out);
exec("cp -rf {$repo_path}/.* {$public_html}/ 2>/dev/null", $copy_hidden);

$log = "[" . date('Y-m-d H:i:s') . "] Deploy triggered\n";
$log .= implode("\n", $pull_out) . "\n";
file_put_contents($repo_path . '/deploy.log', $log, FILE_APPEND);

http_response_code(200);
echo json_encode(['status' => 'deployed', 'time' => date('Y-m-d H:i:s')]);
