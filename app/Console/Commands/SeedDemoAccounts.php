<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SeedDemoAccounts extends Command
{
    protected $signature = 'shop:demo-accounts';

    protected $description = 'Create local demonstration accounts without changing existing users';

    /** Public demo credentials are allowed only in local and testing environments. */
    public function handle(): int
    {
        if (!app()->environment(['local', 'testing'])) {
            $this->error('Demo accounts may only be created locally.');

            return self::FAILURE;
        }

        foreach ([
            [
                'email' => 'admin@respawn.test',
                'name' => 'Администратор',
                'is_admin' => true,
            ],
            [
                'email' => 'buyer@respawn.test',
                'name' => 'Игрок',
                'is_admin' => false,
            ],
        ] as $account) {
            if (User::query()->where('email', $account['email'])->exists()) {
                $this->line('Already exists, unchanged: ' . $account['email']);

                continue;
            }

            $user = new User;
            $user->name = $account['name'];
            $user->email = $account['email'];
            $user->is_admin = $account['is_admin'];
            $user->password = Hash::make('RespawnDemo2026!');
            $user->save();
            $this->info('Created: ' . $account['email']);
        }

        return self::SUCCESS;
    }
}
