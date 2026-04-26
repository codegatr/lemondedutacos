<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/functions.php';

/**
 * CRUD yardımcıları – admin modülleri için.
 *
 * Kullanım örneği bir sayfada:
 *   $cfg = [
 *     'table'    => 'campaigns',
 *     'label'    => 'Kampanya',
 *     'fields'   => [...],
 *     'images'   => ['image','image_mobile'],
 *     'image_dir'=> 'kampanya',
 *   ];
 *   $crud = new Crud($cfg); $crud->handle();
 */

class Crud
{
    public function __construct(public readonly array $cfg) {}

    public function handle(): void
    {
        $action = $_GET['action'] ?? $_POST['action'] ?? 'list';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_required();
            match ($action) {
                'save'   => $this->save(),
                'delete' => $this->delete(),
                'toggle' => $this->toggle(),
                'sort'   => $this->sort(),
                default  => null,
            };
        }
    }

    private function save(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        $images = (array)($this->cfg['images'] ?? []);
        $data = [];
        foreach ($this->cfg['fields'] as $f => $meta) {
            // Görsel alanları POST'ta gelmez (file input). Bu döngüde atla,
            // aşağıda upload bloğunda işlenecek. Aksi halde mevcut görselin
            // üzerine NULL yazılır ve veri kaybı olur.
            if (in_array($f, $images, true)) continue;

            $val = $_POST[$f] ?? null;
            if (($meta['type'] ?? '') === 'bool') {
                $data[$f] = $val ? 1 : 0;
            } elseif (($meta['type'] ?? '') === 'int') {
                $data[$f] = $val !== null && $val !== '' ? (int)$val : null;
            } elseif (($meta['type'] ?? '') === 'html') {
                $data[$f] = $val ?? null;
            } elseif (is_string($val)) {
                $data[$f] = clean_multi($val);
                if ($data[$f] === '') $data[$f] = null;
            } else {
                $data[$f] = $val;
            }
            if (!empty($meta['required']) && empty($data[$f])) {
                flash_set('error', ($meta['label'] ?? $f) . ' alanı zorunludur.');
                header('Location: ' . basename($_SERVER['PHP_SELF']) . ($id ? '?action=edit&id=' . $id : '?action=new')); exit;
            }
        }

        // Image uploads — sadece yeni dosya yüklendiğinde DB'yi güncelle.
        // Yüklenmediyse mevcut değer korunur (UPDATE'te alan SET edilmez).
        foreach ($images as $imgField) {
            if (!empty($_FILES[$imgField]['name'])) {
                $url = upload_file($imgField, $this->cfg['image_dir'] ?? 'sayfa', ALLOWED_IMG);
                if ($url) {
                    // delete old
                    if ($id) {
                        $stmt = db()->prepare("SELECT $imgField FROM " . $this->cfg['table'] . " WHERE id = ?");
                        $stmt->execute([$id]);
                        delete_upload((string)$stmt->fetchColumn());
                    }
                    $data[$imgField] = $url;
                }
            } elseif (!$id) {
                // Yeni kayıt + dosya yok → DB'ye NULL yaz (varsayılan)
                $data[$imgField] = null;
            }
            // UPDATE + dosya yok → $data'ya alan eklenmez, mevcut değer korunur
        }

        if ($id) {
            $sets = [];
            $vals = [];
            foreach ($data as $k => $v) {
                $sets[] = "$k = ?";
                $vals[] = $v;
            }
            $vals[] = $id;
            $sql = "UPDATE " . $this->cfg['table'] . " SET " . implode(', ', $sets) . " WHERE id = ?";
            db()->prepare($sql)->execute($vals);
            log_activity($this->cfg['table'] . '_updated', null, "ID: $id");
            flash_set('success', $this->cfg['label'] . ' güncellendi.');
        } else {
            $cols = implode(',', array_keys($data));
            $place = implode(',', array_fill(0, count($data), '?'));
            $sql = "INSERT INTO " . $this->cfg['table'] . " ($cols) VALUES ($place)";
            db()->prepare($sql)->execute(array_values($data));
            $newId = (int)db()->lastInsertId();
            log_activity($this->cfg['table'] . '_created', null, "ID: $newId");
            flash_set('success', $this->cfg['label'] . ' eklendi.');
        }
        header('Location: ' . basename($_SERVER['PHP_SELF'])); exit;
    }

    private function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            // delete uploaded files
            foreach (($this->cfg['images'] ?? []) as $imgField) {
                $stmt = db()->prepare("SELECT $imgField FROM " . $this->cfg['table'] . " WHERE id = ?");
                $stmt->execute([$id]);
                delete_upload((string)$stmt->fetchColumn());
            }
            db()->prepare("DELETE FROM " . $this->cfg['table'] . " WHERE id = ?")->execute([$id]);
            log_activity($this->cfg['table'] . '_deleted', null, "ID: $id");
            flash_set('success', $this->cfg['label'] . ' silindi.');
        }
        header('Location: ' . basename($_SERVER['PHP_SELF'])); exit;
    }

    private function toggle(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            db()->prepare("UPDATE " . $this->cfg['table'] . " SET is_active = 1 - is_active WHERE id = ?")->execute([$id]);
            log_activity($this->cfg['table'] . '_toggled', null, "ID: $id");
        }
        header('Location: ' . basename($_SERVER['PHP_SELF'])); exit;
    }

    private function sort(): void
    {
        $ids = $_POST['ids'] ?? [];
        if (is_array($ids)) {
            $stmt = db()->prepare("UPDATE " . $this->cfg['table'] . " SET sort_order = ? WHERE id = ?");
            foreach ($ids as $i => $id) {
                $stmt->execute([(int)$i, (int)$id]);
            }
        }
        json_response(['ok' => true]);
    }

    public function getRow(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM " . $this->cfg['table'] . " WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function listAll(string $orderBy = 'sort_order, id'): array
    {
        return db()->query("SELECT * FROM " . $this->cfg['table'] . " ORDER BY $orderBy")->fetchAll();
    }
}

/* Form alanı render yardımcısı */
function field(string $name, string $label, ?string $value, string $type = 'text', array $opts = []): void
{
    echo '<div class="row">';
    echo '<label>' . e($label) . ($opts['required'] ?? false ? ' *' : '') . '</label>';
    $req = ($opts['required'] ?? false) ? ' required' : '';
    $ph  = isset($opts['placeholder']) ? ' placeholder="' . e($opts['placeholder']) . '"' : '';
    switch ($type) {
        case 'textarea':
            $rows = $opts['rows'] ?? 3;
            echo '<textarea name="' . e($name) . '" rows="' . (int)$rows . '"' . $req . $ph . '>' . e($value) . '</textarea>';
            break;
        case 'html':
            $rows = $opts['rows'] ?? 8;
            echo '<textarea name="' . e($name) . '" rows="' . (int)$rows . '" style="font-family:monospace;font-size:12px"' . $req . '>' . e($value) . '</textarea>';
            echo '<div class="help">HTML kabul edilir.</div>';
            break;
        case 'select':
            echo '<select name="' . e($name) . '"' . $req . '>';
            foreach (($opts['options'] ?? []) as $k => $v) {
                $sel = (string)$value === (string)$k ? ' selected' : '';
                echo '<option value="' . e((string)$k) . '"' . $sel . '>' . e($v) . '</option>';
            }
            echo '</select>';
            break;
        case 'image':
            echo '<input type="file" name="' . e($name) . '" accept="image/*">';
            if ($value) {
                echo '<div class="help">Mevcut: <img src="' . e(asset($value)) . '" style="height:50px;vertical-align:middle;border-radius:4px"></div>';
            }
            break;
        case 'bool':
            $checked = $value ? ' checked' : '';
            echo '<label class="toggle"><input type="checkbox" name="' . e($name) . '" value="1"' . $checked . '> Aktif</label>';
            break;
        default:
            echo '<input type="' . e($type) . '" name="' . e($name) . '" value="' . e($value) . '"' . $req . $ph . '>';
    }
    if (isset($opts['help'])) {
        echo '<div class="help">' . e($opts['help']) . '</div>';
    }
    echo '</div>';
}
