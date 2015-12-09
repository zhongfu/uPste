<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Auth;
use Mail;

class AccountController extends Controller
{
    public function index()
    {
        $now = time();
        $registered_date = strtotime(Auth::user()->created_at);
        $datediff = $now - $registered_date;
        $days = floor($datediff / (60*60*24));
        $new = $days == 0;

        return view('account.index', ['new' => $new]);
    }

    public function resources()
    {
        return view('account.resources');
    }

    public function script()
    {
        return response()->view('account.script')->header('Content-Type', 'text/plain');
    }

    public function uploads()
    {
        $uploads = Auth::user()->uploads;
        return view('account.uploads', compact('uploads'));
    }

    public function resetKey()
    {
        Auth::user()->fill(['apikey' => str_random(64)])->save();
        Mail::queue('emails.user.api_key_reset', ['user' => Auth::user()], function($message)
        {
            $message->from(env('SITE_EMAIL_FROM'), env('SITE_NAME'));
            $message->subject(sprintf("[%s] API Key Reset", env('DOMAIN')));
            $message->to(Auth::user()->email);
        });
        return redirect()->route('account');
    }
}
