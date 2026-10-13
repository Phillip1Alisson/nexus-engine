<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class TenantContextMiddleware
{
    /**
     * Intercepta a requisição e valida a presença do cabeçalho obrigatório do Tenant.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Captura o cabeçalho injetado pela nossa infraestrutura/gateway de borda
        $tenantId = $request->header('X-Tenant-ID');

        // Cláusula de Guarda: Se a requisição não possui a identificação do inquilino, barra na borda
        if (empty($tenantId)) {
            return response()->json([
                'error' => 'Acesso negado: Contexto identificador de Tenant ausente.',
                'statusCode' => Response::HTTP_UNAUTHORIZED
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Compartilha o tenant ativo no container de serviços ou escopo da requisição de forma segura
        app()->instance('CurrentTenantId', $tenantId);

        return $next($request);
    }
}
