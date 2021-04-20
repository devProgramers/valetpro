<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $name = ['admin','valet manager','valet','customer'];
        for ($i =0;$i<4;$i++){
            $role = new Role;
            $role->name = $name[$i];
            $role->save();
        }
    }
}
