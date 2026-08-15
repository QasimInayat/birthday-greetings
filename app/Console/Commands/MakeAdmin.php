<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin
                            {--name= : Full name of the admin}
                            {--email= : Login email address}
                            {--password= : Password (prompted for if omitted)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create (or update the password of) an admin account for the portal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name  = $this->option('name') ?: $this->ask('Full name');
        $email = $this->option('email') ?: $this->ask('Email address');

        $existing = User::where('email', $email)->first();

        if ($existing && !$this->confirm("An account already exists for {$email}. Reset its password?", false)) {
            $this->warn('Cancelled.');
            return self::FAILURE;
        }

        $password = $this->option('password') ?: $this->secret('Password (minimum 8 characters)');

        $validator = Validator::make(
            compact('name', 'email', 'password'),
            [
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|max:255',
                'password' => 'required|string|min:8',
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        if ($existing) {
            $existing->update([
                'name'     => $name,
                'password' => Hash::make($password),
            ]);

            $this->info("Password updated for {$email}.");

            return self::SUCCESS;
        }

        User::create([
            'name'              => $name,
            'email'             => $email,
            'password'          => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $this->info("Admin account created for {$email}.");

        return self::SUCCESS;
    }
}
