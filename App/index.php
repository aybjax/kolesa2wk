<?php
namespace App;

use App\Controller\PushTokenController;
use App\Database\Exception\StartServerException;
use App\Middleware\DeleteValidatorMiddleware;
use App\Middleware\GetValidatorMiddleware;
use App\Middleware\SaveValidatorMIddleware;
use Slim\Factory\AppFactory;

$app = AppFactory::create();

try {
    $app->addErrorMiddleware(true, true, true);
    $app->post('/save', PushTokenController::class . ":router")
        ->add(new SaveValidatorMiddleware());
    $app->post('/delete', PushTokenController::class . ":router")
        ->add(new DeleteValidatorMiddleware());
    $app->post('/get', PushTokenController::class . ":router")
        ->add(new GetValidatorMiddleware());
} catch (StartServerException $e) {
    die(PushTokenController::ERROR_MSG_JSON);
} catch (\Exception $e) {
}

$app->run();
