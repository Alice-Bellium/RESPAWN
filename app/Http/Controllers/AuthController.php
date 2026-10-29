<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** Возвращает пользователя и актуальный CSRF-токен сессии. */
    public function session(Request $request): JsonResponse
    {
        return response()->json($this->payload($request))->header('Cache-Control', 'no-store');
    }

    /** Регистрирует покупателя без возможности назначить себе роль администратора. */
    public function register(Request $request): JsonResponse
    {
        $this->normalizeEmail($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'max:72', Password::min(10)->letters()->numbers()],
        ]);
        $user = User::create($data);
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json($this->payload($request), 201);
    }

    /** Проверяет пароль и обновляет идентификатор сессии после входа. */
    public function login(Request $request): JsonResponse
    {
        $this->normalizeEmail($request);
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'max:72'],
        ]);

        if (!Auth::attempt($data)) {
            throw ValidationException::withMessages([
                'email' => 'Неверная почта или пароль.',
            ]);
        }

        $request->session()->regenerate();

        return response()->json($this->payload($request));
    }

    /** Завершает сессию и обновляет CSRF-токен. */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json($this->payload($request));
    }

    /** Обновляет имя покупателя; роль и почта этим запросом не меняются. */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);
        $request->user()->update($data);

        return response()->json($this->payload($request));
    }

    /** Меняет пароль только после проверки текущего. */
    public function password(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'confirmed', 'max:72', Password::min(10)->letters()->numbers()],
        ]);
        $request->user()->update([
            'password' => Hash::make($data['password']),
        ]);
        $request->session()->regenerate();

        return response()->json($this->payload($request));
    }

    /** Нормализует почту, не обходя проверку типа входного значения. */
    private function normalizeEmail(Request $request): void
    {
        if (is_string($request->input('email'))) {
            $request->merge([
                'email' => mb_strtolower(trim($request->input('email'))),
            ]);
        }
    }

    /** Публичные данные сессии без хеша пароля и служебных полей. */
    private function payload(Request $request): array
    {
        $user = Auth::guard('web')->user();

        return [
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
            ] : null,
            'csrf_token' => $request->session()->token(),
        ];
    }
}
