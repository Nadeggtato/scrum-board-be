<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::updateOrCreate(['email' => $googleUser->getEmail()], [
            'name' => $googleUser->getName(),
            'google_id' => $googleUser->getId(),
        ]);

        $token = $user->createToken(
            'api',
            ['*'],
            Carbon::now()->addDays(4)
        )->plainTextToken;

        return Response::json([
            'token' => $token,
            'user' => $user,
        ]);
    }
}
