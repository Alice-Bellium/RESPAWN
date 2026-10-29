<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'shop:create-admin {email} {--name=Administrator} {--password=}';

    protected $description = 'Создать администратора магазина через консоль';

    /** Создаёт новый административный аккаунт, не изменяя существующие аккаунты. */
    public function handle(): int
    {
        $password = $this->option('password');

        if (!$password && $this->input->isInteractive()) {
            $password = $this->secret('Пароль администратора');
        }

        $data = [
            'email' => mb_strtolower($this->argument('email')),
            'name' => $this->option('name'),
            'password' => $password,
        ];
        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'name' => ['required', 'string', 'max:100'],
            'password' => ['required', 'max:72', Password::min(10)->letters()->numbers()],
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $user = new User($data);
        $user->password = Hash::make($password);
        $user->is_admin = true;
        $user->save();
        $this->info('Администратор создан.');

        return self::SUCCESS;
    }
}
