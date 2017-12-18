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
        	'password' => md5('123456'),
        	'gender' => 0,
        	'birthday' => strtotime('Nov 1, 1997'),
        	'first_name' => 'Thanh Tung',
        	'last_name' => 'Pham',
        	'display_name' => 'thanhtunguet',
        	'title' => 'Developer',
        	'roles' => '[5]',
        	'is_leader_of' => ''
        ]);
    }
}
