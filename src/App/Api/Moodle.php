<?php

namespace App\Api;

use Symfony\Component\HttpFoundation\JsonResponse;

class Moodle
{


    public function doCourseViewed(): JsonResponse
    {
        vd($_GET, $_POST);
        $data = [
            'var1' => 'value one',
            'var2' => 'value two',
            'var3' => 'value three',
        ];

        return new JsonResponse($data);
    }

}