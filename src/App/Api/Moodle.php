<?php

namespace App\Api;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Tk\Config;


/**
 * @deprecated Remove for release
 */
class Moodle
{


    public function doCourseViewed(): JsonResponse
    {
        if (!Config::isDev()) return new JsonResponse([], Response::HTTP_UNAUTHORIZED);

        vd($_GET, $_POST);
        $data = [
            'var1' => 'value one',
            'var2' => 'value two',
            'var3' => 'value three',
        ];

        return new JsonResponse($data);
    }

    public function doCompetencyGraded(): JsonResponse
    {
        if (!Config::isDev()) return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
        $data = $_REQUEST['competency'] ?? '';
        vd('tk8base', $data);
        return new JsonResponse($data);
    }

}