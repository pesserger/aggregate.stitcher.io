<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PostsController;
use Domain\User\Events\CreateUserEvent;
use Domain\User\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $data = $request->all();

        $createUserEvent = CreateUserEvent::create($data['email'], bcrypt($data['password']));

        event($createUserEvent);

        $user = User::whereUuid($createUserEvent->user_uuid)->firstOrFail();

        $this->guard()->login($user);

        return redirect()->action([PostsController::class, 'index']);
    }
}
