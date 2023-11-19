<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Admin\UserController;

class UsuarioMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //Verifica se o usuário está ativo no banco
        if(Auth::user()->desativado === false)
        {
            //Verifica o nível do usuário
            if(Auth::user()->autenticado === true)
            {
                return $next($request); //$next representa o próximo passo na cadeia do middleware
            }
        }
        else{
            return redirect()->route('logout');
        }

        return redirect()->route('logout');
    }
}
