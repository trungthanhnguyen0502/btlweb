<?php

namespace App\Http\Controllers\Auth;

use App\Employee;
use App\Http\Controllers\Controller;
use App\Session;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class LoginController extends Controller
{

    protected function create_login_key($emp_id, $ip_address, $request_time)
    {
        $random_key = mt_rand(1000, 9999);
        $login_string = "{$emp_id}/{$ip_address}/{$request_time}";
        return md5($login_string) . md5($random_key);
    }

    public function index(Request $request)
    {
        $email = $request->input('email');
        $employees = Employee::where('email', $email)->get();

        if ($employees->count() == 1) {

            $employee = $employees[0];
            $password = md5($request->input('password'));
            if ($employee->password != $password) {
                // Wrong password
                return [
                    'error' => true,
                    'status' => 'wrong-password'
                ];
            }

            $ip_address = $request->server('REMOTE_ADDR');
            $agent = new Agent();
            $agent->setUserAgent($request->server('HTTP_USER_AGENT'));
            $browser = $agent->browser();
            $platform = $agent->platform();

            $request_time = $request->server('REQUEST_TIME');

            // Generate login key
            $login_key = $this->create_login_key($employee->id, $ip_address, $request_time);

            // Save client session
            session([
                'login_key' => $login_key,
                'employee' => $employee,
            ]);

            // Cookie Time To Live
            $cookie_ttl = 43200; // in minutes

            // Save session to database
            $session = new Session();
            $session->employee_id = $employee->id;
            $session->key = $login_key;
            $session->ip_address = $ip_address;
            $session->browser = $browser;
            $session->platform = $platform;
            $session->expired = $request_time + $cookie_ttl * 60;
            $session->save();

            return response()
                ->json([
                    'login_key' => $login_key,
                    'employee_id' => $employee->id,
                ])
                ->cookie('login_key', $login_key, $cookie_ttl)
                ->cookie('employee_id', $employee->id, $cookie_ttl);
        } else {
            // Employee does not exist
            return [
                'error' => true,
                'status' => 'employee-does-not-exist'
            ];
        }
    }

    public function forgot_password()
    {

    }
}
