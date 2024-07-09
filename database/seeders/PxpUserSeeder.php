<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PxpUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('pxpuser')->truncate();

        DB::table('pxpuser')->insert(
            [
                [
                    'pharmacy_id' => 1,
                    'pharmacy_id2' => 1,
                    'role' => '50',
                    'name' => 'Admin',
                    'surname' => 'System',
                    'email' => 'admin@goodcareit.com',
                    'password' => Hash::make('password'),
                    'remember_token' => 'NLZoOSnj1ObGfwuylikX3FpszKslp5f65TNlgWxNlOE8eWOGA99LAr8Sfhaj',
                    'code' => 'lE6uvQLy][4dgo',
                    'token' => 'ZfHilO9u224bxglBdLiCh3ZaVSWKMIUY',
                    'created_at' => date('Y-m-d H:i:s'),
                    'deleted_at' => NULL,
                    'updated_at' => date('Y-m-d H:i:s'),
                ],
            ]
        );
    }
}
