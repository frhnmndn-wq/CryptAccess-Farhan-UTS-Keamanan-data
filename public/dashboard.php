<?php require __DIR__.'/../app/bootstrap.php';
require __DIR__.'/_layout.php';
use CryptAccess\Core\{Auth,DB};

$u = Auth::require();
$records = (int)(DB::one('SELECT COUNT(*) n FROM encrypted_records WHERE status="encrypted"')['n']);
$files = (int)(DB::one('SELECT COUNT(*) n FROM encrypted_files WHERE status="encrypted"')['n']);
$logs = DB::one('SELECT COUNT(*) n FROM activity_logs WHERE administrator_id=? AND created_at>=DATE_SUB(NOW(),INTERVAL 7 DAY)', [$u['id']])['n'];
$recent = DB::pdo()->prepare('SELECT event_type,detail,created_at FROM activity_logs WHERE administrator_id=? OR administrator_id IS NULL ORDER BY id DESC LIMIT 8');
$recent->execute([$u['id']]);
page_head('Dashboard', true);?><div class="content"><p class="eyebrow">SECURITY OVERVIEW</p><h1 class="h2 mb-4">Operational dashboard</h1><div class="row g-3"><div class="col-md-4"><div class="panel stat"><div class="muted">Encrypted information</div><div class="number"><?=$records?></div></div></div><div class="col-md-4"><div class="panel stat"><div class="muted">Protected files</div><div class="number"><?=$files?></div></div></div><div class="col-md-4"><div class="panel stat"><div class="muted">Your activity, 7 days</div><div class="number"><?=$logs?></div></div></div></div><div class="panel mt-4 p-3"><h2 class="h5">Recent activity</h2><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Event</th><th>Detail</th><th>Time</th></tr></thead><tbody><?php foreach ($recent as $r):?><tr><td><?=htmlspecialchars(str_replace('_', ' ', $r['event_type']))?></td><td class="muted"><?=htmlspecialchars($r['detail'] ?? '—')?></td><td class="muted"><?=htmlspecialchars($r['created_at'])?></td></tr><?php endforeach;
if (!$recent->rowCount()):?><tr><td colspan="3" class="muted">No activity recorded yet.</td></tr><?php endif?></tbody></table></div></div></div><?php page_foot(true);
