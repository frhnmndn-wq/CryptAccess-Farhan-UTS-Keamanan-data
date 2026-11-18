<?php require __DIR__.'/../app/bootstrap.php';
require __DIR__.'/_layout.php';
use CryptAccess\Core\{Auth,DB};

$u = Auth::require();
$s = DB::pdo()->prepare('SELECT event_type,ip_address,detail,created_at FROM activity_logs WHERE administrator_id=? OR administrator_id IS NULL ORDER BY id DESC LIMIT 100');
$s->execute([$u['id']]);
page_head('Activity Logs', true);?><div class="content"><p class="eyebrow">AUDIT TRAIL</p><h1 class="h2">Activity logs</h1><p class="muted">Security events record no passphrases, private keys, DEKs, or decrypted content.</p><div class="panel p-3 mt-4"><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Event</th><th>IP</th><th>Detail</th><th>Time</th></tr></thead><tbody><?php foreach ($s as $r):?><tr><td><?=htmlspecialchars(str_replace('_', ' ', $r['event_type']))?></td><td class="muted"><?=htmlspecialchars($r['ip_address'] ?? '—')?></td><td class="muted"><?=htmlspecialchars($r['detail'] ?? '—')?></td><td class="muted"><?=htmlspecialchars($r['created_at'])?></td></tr><?php endforeach?></tbody></table></div></div></div><?php page_foot(true);
