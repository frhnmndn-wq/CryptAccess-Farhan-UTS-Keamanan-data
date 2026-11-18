<?php require __DIR__.'/../app/bootstrap.php';
require __DIR__.'/_layout.php';
use CryptAccess\Core\{Auth,DB,Csrf,Crypto,Log};

$u = Auth::require();
$error = null;
$ok = null;
$categories = DB::pdo()->query('SELECT * FROM data_categories')->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::check();
    try {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $cat = (int)($_POST['category_id'] ?? 1);
        if ($title === '' || $content === '' || !in_array($cat, [1,2,3], true)) {
            throw new RuntimeException();
        }$key = DB::one('SELECT * FROM cryptographic_keys WHERE administrator_id=? AND status="active"', [$u['id']]);
        if (!$key) {
            throw new RuntimeException();
        }$dek = random_bytes(32);
        [$tc,$tn,$tt] = Crypto::aes($title, $dek);
        [$cc,$cn,$ct] = Crypto::aes($content, $dek);
        $wrapped = Crypto::wrap($dek, $key['public_key_pem']);
        DB::pdo()->beginTransaction();
        DB::exec('INSERT INTO encrypted_records(administrator_id,category_id,title_ciphertext,title_nonce,title_tag,content_ciphertext,content_nonce,content_tag) VALUES(?,?,?,?,?,?,?,?)', [$u['id'],$cat,$tc,$tn,$tt,$cc,$cn,$ct]);
        $id = (int)DB::pdo()->lastInsertId();
        DB::exec('INSERT INTO encryption_keys(record_id,cryptographic_key_id,encrypted_dek) VALUES(?,?,?)', [$id,$key['id'],$wrapped]);
        Log::add($u['id'], 'record_encrypted', 'Record #'.$id);
        DB::pdo()->commit();
        $ok = 'Information encrypted and stored as record #'.$id.'.';
    } catch (Throwable) {
        if (DB::pdo()->inTransaction()) {
            DB::pdo()->rollBack();
        }$error = 'Encryption could not be completed. No plaintext was stored.';
    }
}
page_head('Encryption Center', true);?><div class="content"><p class="eyebrow">ENCRYPTION CENTER</p><h1 class="h2">Protect information</h1><p class="muted">A fresh 256-bit DEK is generated for this record, then wrapped with the active RSA public key.</p><?php if ($ok):?><p class="notice"><?=htmlspecialchars($ok)?></p><?php endif;
if ($error):?><p class="notice danger"><?=htmlspecialchars($error)?></p><?php endif?><form method="post" class="panel p-4 mt-4 col-xl-8"><input type="hidden" name="csrf" value="<?=Csrf::token()?>"><label class="form-label">Category</label><select class="form-select" name="category_id"><?php foreach ($categories as $c):?><option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?></option><?php endforeach?></select><label class="form-label mt-3">Title</label><input required maxlength="500" class="form-control" name="title"><label class="form-label mt-3">Classified content</label><textarea required class="form-control" rows="9" name="content"></textarea><button class="btn btn-primary mt-4">Encrypt and store</button></form></div><?php page_foot(true);
