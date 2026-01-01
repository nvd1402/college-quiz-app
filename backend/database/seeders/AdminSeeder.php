<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sử dụng giá trị mặc định nếu không có input từ command line
        $first_name = env('ADMIN_FIRST_NAME', 'Admin');
        $last_name = env('ADMIN_LAST_NAME', 'System');
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $birth_date = env('ADMIN_BIRTH_DATE', '1990-01-01');
        $gender = env('ADMIN_GENDER', 'male');
        $address = env('ADMIN_ADDRESS', 'Default Address');
        $password = env('ADMIN_PASSWORD', 'admin123');

        // Kiểm tra xem admin đã tồn tại chưa
        $existingAdmin = User::where('email', $email)->first();
        if ($existingAdmin) {
            if ($this->command) {
                $this->command->info('Admin user already exists. Skipping...');
            }
            return;
        }

        User::create([
            'role_id' => Role::where('name', '=', 'admin')->first()->id,
            'shortcode' => 'SYSADMIN',
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'gender' => $gender,
            'address' => $address,
            'birth_date' => $birth_date,
            'is_active' => true,
            'password' => Hash::make($password)
        ]);

        if ($this->command) {
            $this->command->info('Admin user created successfully!');
            $this->command->info('Email: ' . $email);
            $this->command->info('Password: ' . $password);
        }
    }
}
