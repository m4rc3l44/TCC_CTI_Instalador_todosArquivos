<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\UserController;

class CheckAuthenticated
{
    //Verifica se o usuário está logado
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            return $next($request);
        }

        return redirect()->route('aluno.login'); // Redireciona para a página de login
    }
}
