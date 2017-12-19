<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            'id' => 1,
            'title' => 'Employee',
            'description' => 'An employee who works at company'
        ])->insert([
            'id' => 2,
            'title' => 'Team Leader',
            'description' => 'An employee who leads an IT teams'
        ])->insert([
            'id' => 3,
            'title' => 'IT Department Manager',
            'description' => 'An employee who manages IT departments',
        ])->insert([
            'id' => 4,
            'title' => 'Branch'
        ]);

    }
}
