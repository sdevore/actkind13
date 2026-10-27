<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Livewire\Exceptions\MethodNotFoundException;
use Livewire\Features\SupportFileUploads\MissingFileUploadsTraitException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->map(fn (MissingFileUploadsTraitException $exception) => new BadRequestHttpException(previous: $exception));

        $exceptions->map(fn (MethodNotFoundException $exception) => new BadRequestHttpException(previous: $exception));

        $exceptions->dontReport([
            MissingFileUploadsTraitException::class,
            MethodNotFoundException::class,
        ]);
    })->create();
