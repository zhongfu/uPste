<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Auth;
use Mail;
use Session;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->redirectPath = route('index');
        $this->loginPath = route('login');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255|unique:users',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new account instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $firstUser = count(User::all()) == 0;
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'apikey' => str_random(64),
            'password' => bcrypt($data['password']),
            // First user registered should be enabled and admin
            'admin' => $firstUser,
            'enabled' => $firstUser
        ]);

        Mail::queue('emails.admin.new_registration', $data, function($message) use ($data)
        {
            $message->from(env('SITE_EMAIL_FROM'), env('SITE_NAME'));
            $message->subject(sprintf("[%s] New User Registration", env('DOMAIN')));
            $message->to(env('OWNER_EMAIL'));
        });

        Session::flash('info',
            'Your account request has successfully been registered. You will receive an email when an admin approves or denies your request.');
        return $user;
    }
}
