<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use NckRtl\Waymaker\Get;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Serves plain-text instructions to AI agents that were pointed at this site.
 *
 * `/llms.txt` is the discovery entry point (llmstxt.org); it stays short and links
 * to `/create.md`, which carries the complete setup guide.
 */
class AgentDocsController extends Controller
{
    #[Get(uri: '/llms.txt')]
    public function index(): Response
    {
        return $this->markdown('llms.txt', 'text/plain');
    }

    #[Get(uri: '/create.md')]
    public function create(): Response
    {
        return $this->markdown('create.md', 'text/markdown');
    }

    private function markdown(string $file, string $contentType): Response
    {
        $path = resource_path('markdown/'.$file);

        if (! is_file($path)) {
            throw new NotFoundHttpException;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new NotFoundHttpException;
        }

        // These routes sit in the `web` group, so Laravel attaches a session cookie.
        // `private` keeps shared caches from storing that cookie alongside the body.
        return response($contents, headers: [
            'Content-Type' => $contentType.'; charset=utf-8',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
