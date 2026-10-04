<?php
declare(strict_types=1);
namespace Numok\Services;
use Numok\Database\Database;
/** Sessions survive Railway releases; a database advisory lock serializes each session. */
final class PortalSessionHandler implements \SessionHandlerInterface, \SessionUpdateTimestampHandlerInterface {
    private ?string $lock=null;
    public function open(string $path,string $name): bool { return true; }
    public function close(): bool {
        if($this->lock!==null){Database::query('SELECT RELEASE_LOCK(?)',[$this->lock]);$this->lock=null;}
        return true;
    }
    public function read(string $id): string|false {
        if(!preg_match('/^[A-Za-z0-9,-]{16,128}$/',$id))return false;
        $lock='portal_'.substr(hash('sha256',$id),0,55);
        if((int)Database::query('SELECT GET_LOCK(?,10)',[$lock])->fetchColumn()!==1)return false;
        $this->lock=$lock;
        return Database::query('SELECT data FROM portal_sessions WHERE id=? AND expires_at>UTC_TIMESTAMP()',[$id])->fetchColumn()?:'';
    }
    public function write(string $id,string $data): bool {
        Database::query('INSERT INTO portal_sessions (id,data,expires_at) VALUES (?,?,DATE_ADD(UTC_TIMESTAMP(),INTERVAL 1 DAY)) ON DUPLICATE KEY UPDATE data=VALUES(data),expires_at=VALUES(expires_at)',[$id,$data]);return true;
    }
    public function destroy(string $id): bool { Database::query('DELETE FROM portal_sessions WHERE id=?',[$id]);return true; }
    public function gc(int $max_lifetime): int|false { return Database::query('DELETE FROM portal_sessions WHERE expires_at<UTC_TIMESTAMP()')->rowCount(); }
    public function validateId(string $id): bool { return (bool)Database::query('SELECT 1 FROM portal_sessions WHERE id=? AND expires_at>UTC_TIMESTAMP()',[$id])->fetchColumn(); }
    public function updateTimestamp(string $id,string $data): bool { return $this->write($id,$data); }
}
