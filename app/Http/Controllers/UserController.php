<?php

namespace App\Http\Controllers;

use App\Mail\ResetPassword;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth')->except(['login', 'refresh', 'store', 'forgotPassword', 'resetPassword', 'sendResetPasswordScreen']);
    }

    public function index(Request $request)
    {
        try {
            $user = User::where('uid', $request->header('user'))->first();

            if (!$user) {
                throw new \Exception('Usuário não encontrado.', 404);
            }
            return response()->json($user);
        } catch (\Throwable $th) {
            Log::error('UserController::index - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json($th->getMessage(), $th->getCode())->setStatusCode($th->getCode());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'documento' => 'required|string|unique:users,documento|max:18',
            'telefone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cryptography = Hash::make($request->password);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $cryptography,
                'documento' => $request->documento,
                'telefone' => $request->telefone,
            ]);

            return response()->json($user)->setStatusCode(201);
        } catch (\Throwable $th) {
            Log::error('UserController::store - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao criar usuário.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {

        try {
            $user = User::where('uid', $request->header('user'))->first();
            if (!$user) {
                throw new \Exception('Usuário não encontrado.', 404);
            }
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'documento' => [
                    'required',
                    'string',
                    'max:18',
                    Rule::unique('users', 'documento')->ignore($user->id),
                ],
                'telefone' => 'required|string|max:20',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users', 'email')->ignore($user->id),
                ],
                'moeda' => 'required|string|max:2',
            ]);

            if ($validator->fails()) {
                Log::info('UserController::update - Validation failed - ' . json_encode($validator->errors()));
                return response()->json([
                    'message' => 'Dados inválidos.',
                    'errors' => $validator->errors()
                ], 422);
            }
            $user->update([
                'name' => $request->name,
                'documento' => $request->documento,
                'telefone' => $request->telefone,
                'email' => $request->email,
                'moeda' => $request->moeda,
            ]);

            return response()->json($user);
        } catch (\Throwable $th) {
            Log::error('UserController::update - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao atualizar usuário.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dados inválidos.',
                'errors' => $validator->errors()
            ], 422);
        }

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT ? response()->json(['message' => __($status)])
                    : response()->json(['message' => __($status)], 500);
    }

    public function sendResetPasswordScreen(Request $request, string $token)
    {
        return response()->json([
            'message' => 'Token recebido.',
            'token' => $token,
        ], 200);
    }



    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'token' => 'required|string',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Dados inválidos.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $status = Password::reset(
                    $request->only(
                        'email',
                        'password',
                        'password_confirmation',
                        'token'
                    ),
                    function ($user, $password) {
                        $user->forceFill([
                            'password' => Hash::make($password),
                            'remember_token' => Str::random(60),
                        ])->save();
                    }
                );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'message' => 'Senha alterada com sucesso.',
                ]);
            }
            return response()->json([
                'message' => __($status),
            ], 400);
        } catch (\Throwable $th) {
            Log::error('UserController::resetPassword - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao redefinir senha.',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function changePasswordLogged(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Dados inválidos.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = User::where('uid', $request->header('user'))->first();
            if (!$user) {
                return response()->json([
                    'message' => 'Usuário não encontrado.'
                ], 404);
            }

            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return response()->json($user);
        } catch (\Throwable $th) {
            Log::error('UserController::changePasswordLogged - ' . $th->getMessage(). ' - ' . $th->getCode(). ' - ' . $th->getFile(). ' - ' . $th->getLine());
            return response()->json([
                'message' => 'Erro ao atualizar usuário.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        // 2. Valida se o usuário existe e se a senha está correta
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciais inválidas.'
            ], 401);
        }

        $accessToken = JWTAuth::fromUser($user);

        $refreshToken = Str::random(64);

        UserSession::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_id' => $request->device_id
            ],
            [
                'device_name' => $request->device_name,
                'refresh_token' => Hash::make($refreshToken),
                'refresh_token_expires_at' => now()->addDays(30),
                'last_access_at' => now(),
            ]
        );

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => config('jwt.ttl') * 60,
            'token_type' => 'Bearer'
        ]);
    }
    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        UserSession::where('user_id', auth()->id())
            ->where('device_id', $request->device_id)
            ->delete();

        return response()->json([
            'message' => 'Logout realizado.'
        ]);
    }

    public function refresh(Request $request)
    {
        $session = UserSession::where('device_id', $request->device_id)->first();

        if (! $session) {
            return response()->json(['message' => 'Nenhuma sessão encontrada. Tente novamente.'], 401);
        }

        if (! Hash::check($request->refresh_token, $session->refresh_token)) {
            return response()->json(['message' => 'Refresh token inválido.'], 401);
        }

        if ($session->refresh_token_expires_at < now()) {
            return response()->json(['message' => 'Refresh token expirado.'], 401);
        }

        $user = $session->user;

        // Gera um novo JWT
        $accessToken = JWTAuth::fromUser($user);

        // Rotaciona o refresh token
        $newRefresh = Str::random(64);

        $session->update([
            'refresh_token' => Hash::make($newRefresh),
            'refresh_token_expires_at' => now()->addDays(30),
            'last_access_at' => now(),
        ]);

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $newRefresh,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60
        ]);
    }
}
