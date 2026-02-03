<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue as QueueShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;

class UsersImport implements
    ToCollection,
    WithHeadingRow,
    WithChunkReading,
    ShouldQueue
{
     use Importable;
    private ?array $roleIds;
    private array $existingEmails = [];

    public function __construct(?array $roleIds = null)
    {
        $this->roleIds = $roleIds;

        $this->existingEmails = User::whereNotNull('email')
            ->pluck('email')
            ->toArray();
    }

    public function collection(Collection $rows)
    {
        $now = now();
        $usersBatch = [];

        foreach ($rows as $row) {

            if (empty($row['nom']) || empty($row['prenom'])) {
                continue;
            }

            $email = $row['email'] ?? null;

            if ($email && in_array($email, $this->existingEmails)) {
                continue;
            }

            $usersBatch[] = [
                'nom'        => trim($row['nom']),
                'prenom'     => trim($row['prenom']),
                'email'      => $email,
                'password'   => Hash::make('password'),
                'genre'      => $this->parseGenre($row['genre'] ?? null),
                'slug'       => Str::uuid(),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($email) {
                $this->existingEmails[] = $email;
            }
        }

        if (empty($usersBatch)) {
            return;
        }

        DB::transaction(function () use ($usersBatch) {
            DB::table('users')->insert($usersBatch);
            if (!empty($this->roleIds)) {

                $userIds = User::orderByDesc('id')
                    ->take(count($usersBatch))
                    ->pluck('id');

                $roleUserBatch = [];

                foreach ($userIds as $userId) {
                    foreach ($this->roleIds as $roleId) {
                        $roleUserBatch[] = [
                            'user_id'   => $userId,
                            'user_type' => 'App\\Models\\User',
                            'role_id'   => $roleId,
                        ];
                    }
                }

                DB::table('role_user')->insert($roleUserBatch);
            }
        });
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private function parseGenre(?string $value): string
    {
        if (!$value) return 'Féminin';

        return str_starts_with(strtolower($value), 'm')
            ? 'Masculin'
            : 'Féminin';
    }
}
