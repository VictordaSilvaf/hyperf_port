<?php

declare(strict_types=1);
/**
 * Hyperf API — DDD / Hexagonal
 *
 * @link     https://github.com/VictordaSilvaf/hyperf_port
 * @document https://github.com/VictordaSilvaf/hyperf_port/doc
 * @contact  victordasilvafernandes@gmail.com
 * @see      https://github.com/VictordaSilvaf/hyperf_port.git
 */

namespace App\Infrastructure\Persistence\Contact;

use App\Domain\Contact\Entity\ContactMessage;
use App\Domain\Contact\Repository\ContactMessageRepositoryInterface;
use App\Domain\Contact\ValueObject\ContactMessageId;
use App\Domain\Contact\ValueObject\ContactMessageStatus;
use Hyperf\DbConnection\Db;

final class DbContactMessageRepository implements ContactMessageRepositoryInterface
{
    private const TABLE = 'contact_messages';

    public function save(ContactMessage $message): void
    {
        $row = ContactMessagePersistenceMapper::toRow($message);
        $exists = Db::table(self::TABLE)->where('id', $row['id'])->exists();
        if ($exists) {
            Db::table(self::TABLE)->where('id', $row['id'])->update([
                'status' => $row['status'],
            ]);
        } else {
            Db::table(self::TABLE)->insert($row);
        }
    }

    public function findById(ContactMessageId $id): ?ContactMessage
    {
        $row = Db::table(self::TABLE)->where('id', $id->value())->first();

        return $row === null ? null : ContactMessagePersistenceMapper::toDomain((array) $row);
    }

    public function paginate(int $page, int $perPage, ?ContactMessageStatus $status = null): array
    {
        $builder = Db::table(self::TABLE)
            ->select(['id', 'name', 'email', 'subject', 'status', 'created_at']);

        if ($status !== null) {
            $builder->where('status', $status->value);
        }

        $total = (clone $builder)->count();
        $rows = $builder->orderByDesc('created_at')->forPage($page, $perPage)->get();

        $items = [];
        foreach ($rows as $row) {
            $data = (array) $row;
            $items[] = [
                'id' => (string) $data['id'],
                'name' => (string) $data['name'],
                'email' => (string) $data['email'],
                'subject' => isset($data['subject']) ? (string) $data['subject'] : null,
                'status' => (string) $data['status'],
                'created_at' => isset($data['created_at']) ? (string) $data['created_at'] : null,
            ];
        }

        return ['total' => $total, 'items' => $items];
    }
}
