<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserRoleSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'admin@ystfamily.com')->first();
        if ($user) {
            $role = Role::where('name', "Super Admin")->first();
            $user->assignRole($role);
        }
    }
}
