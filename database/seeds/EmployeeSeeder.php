<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('employees')->insert([
        	'id' => 1,
        	'email' => 'thanhtung.uet@gmail.com',
        	'password' => md5('12345678'),
        	'gender' => 0,
        	'birthday' => strtotime('Nov 1, 1997'),
        	'first_name' => 'Thanh Tùng',
        	'last_name' => 'Phạm',
        	'display_name' => 'Phạm Thanh Tùng',
        	'title' => 'Developer',
        	'roles' => '[5]',
        	'branch' => 1,
        	'is_leader_of' => ''
        ]);
    }
}
