<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUserCommand extends Command
{
    protected $signature = 'admin:create-user
                            {email : Admin email address}
                            {--name= : Display name}
                            {--password= : Password (prompted if omitted)}';

    protected $description = 'Create or promote a local admin user account';

    public function handle(): int
    {
        $email = strtolower($this->argument('email'));
        $name = $this->option('name') ?: $email;
        $password = $this->option('password') ?: $this->secret('Password');

        $validator = Validator::make(
            ['email' => $email, 'password' => $password],
            ['email' => 'required|email', 'password' => 'required|string|min:12'],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'is_admin' => true,
            ],
        );

        $this->info($user->wasRecentlyCreated ? 'Admin user created.' : 'Admin user updated.');
        $this->line('Email: '.$user->email);

        return self::SUCCESS;
    }
}
