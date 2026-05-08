<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * cekirdex.com üzerinde kök URL'ler (/panel/..., /r/..., /) tek Laravel uygulamasında
 * /cekirdex prefix'li rotalarla eşleşsin diye isteği içeriden yeniden yazar.
 * Nginx sadece Host'u bu uygulamaya yönlendirir; path dönüşümü burada yapılır.
 *
 * Önemli: Bu middleware bootstrap/app.php içinde global yığına append edilir; web grubunda
 * olursa routing zaten yapılmış olur ve / hep Ininia anasayfasına düşer.
 */
class CekirdexDotComRequestRewrite
{
    private function isCekirdexHost(string $host): bool
    {
        $host = strtolower($host);

        return $host === 'cekirdex.com' || str_ends_with($host, '.cekirdex.com');
    }

    /** Bu yollar /cekirdex öneki almaz (public kök, Vite, sağlık, vb.). */
    private function shouldPassThrough(string $path): bool
    {
        if ($path === '/' || $path === '') {
            return false;
        }
        $prefixes = [
            '/cekirdex',
            '/build',
            '/storage',
            '/vendor',
            '/fonts',
            '/livewire',
            '/up',
        ];
        foreach ($prefixes as $p) {
            if ($path === $p || str_starts_with($path, $p.'/')) {
                return true;
            }
        }
        $files = ['/favicon.ico', '/robots.txt', '/sitemap.xml', '/.well-known'];
        foreach ($files as $f) {
            if ($path === $f || str_starts_with($path, $f.'/')) {
                return true;
            }
        }

        return false;
    }

    public function handle(Request $request, Closure $next)
    {
        if (! $this->isCekirdexHost($request->getHost())) {
            return $next($request);
        }

        $pathInfo = $request->getPathInfo();
        if ($pathInfo === '') {
            $pathInfo = '/';
        }

        if ($this->shouldPassThrough($pathInfo)) {
            return $next($request);
        }

        if ($pathInfo === '/cekirdex' || str_starts_with($pathInfo, '/cekirdex/')) {
            return $next($request);
        }

        $newPath = '/cekirdex'.($pathInfo === '/' ? '' : $pathInfo);
        $qs = $request->getQueryString();
        $newUri = $newPath.($qs ? '?'.$qs : '');

        $server = $request->server->all();
        $server['REQUEST_URI'] = $newUri;
        $server['PATH_INFO'] = $newPath;
        $server['SCRIPT_NAME'] = '/index.php';

        $symfony = SymfonyRequest::create(
            $request->getSchemeAndHttpHost().$newUri,
            $request->method(),
            $request->request->all(),
            $request->cookies->all(),
            $request->files->all(),
            $server,
            $request->getContent()
        );

        $newRequest = Request::createFromBase($symfony);
        $newRequest->headers->replace($request->headers->all());

        return $next($newRequest);
    }
}
