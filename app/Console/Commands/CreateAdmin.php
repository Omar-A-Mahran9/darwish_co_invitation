<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:create-admin {email?} {--name=Admin} {--password=}')]
#[Description('Create (or reset the password of) a dashboard admin account')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('Admin email');
        $password = $this->option('password') ?: $this->secret('Password (min 8 chars)');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen((string) $password) < 8) {
            $this->error('Enter a valid email and a password of at least 8 characters.');

            return self::FAILURE;
        }

        $user = User::updateOrCreate(
            ['email' => strtolower($email)],
            ['name' => $this->option('name'), 'password' => $password],
        );

        $this->info(($user->wasRecentlyCreated ? 'Created' : 'Updated')." admin: {$user->email}");

        return self::SUCCESS;
    }
}
