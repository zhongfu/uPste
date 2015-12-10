<?php

namespace App\Http\Controllers;

use App\Upload;
use App\User;

use App\Http\Requests;
use Auth;
use File;
use Mail;
use Session;

class AdminController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.requests');
    }

    public function requests()
    {
        $users = User::where('enabled', false)->orderBy('created_at', 'asc')->paginate(15);
        return view('admin.requests', compact('users'));
    }

    public function users()
    {
        $users = User::where('enabled', true)->paginate(15);
        return view('admin.users', ['users' => $users]);
    }

    public function ban($user)
    {
        if ($user->id == 1) {
            Session::flash('alert',
                'You cannot ban the superuser account.');
            return redirect()->back();
        }
        $user->fill(['banned' => true])->save();
        return redirect()->back();
    }

    public function delete($user)
    {
        if ($user->id == 1) {
            Session::flash('alert',
                'You cannot delete the superuser account.');
            return redirect()->back();
        }
        $path = storage_path() . '/uploads/';
        foreach ($user->uploads as $upload) {
            $filePath = $path . $upload->name;
            if (File::isFile($filePath)) {
                File::delete($filePath);
            }
        }
        $user->forceDelete();
        return redirect()->back();
    }

    public function unban($user)
    {
        $user->fill(['banned' => false])->save();
        return redirect()->back();
    }

    public function uploads($user)
    {
        $uploads = Upload::where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.uploads', compact('uploads', 'user'));
    }

    public function accept($user)
    {
        $user->fill(['enabled' => true])->save();
        Mail::queue(['text' => 'emails.user.account_accepted'], ['user' => $user], function ($message) use ($user) {
            $message->from(env('SITE_EMAIL_FROM'), env('SITE_NAME'));
            $message->subject(sprintf("[%s] Account Request Accepted", env('DOMAIN')));
            $message->to($user->email);
        });
        return redirect()->back();
    }

    public function reject($user)
    {
        Mail::queue(['text' => 'emails.user.account_rejected'], ['user' => $user], function ($message) use ($user) {
            $message->from(env('SITE_EMAIL_FROM'), env('SITE_NAME'));
            $message->subject(sprintf("[%s] Account Request Rejected", env('DOMAIN')));
            $message->to($user->email);
        });
        $user->forceDelete();
        return redirect()->back();
    }
}
