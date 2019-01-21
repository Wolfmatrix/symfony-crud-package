<?php

namespace Wolfmatrix\RestApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;


class BaseApiController extends AbstractController
{
    const OK           = 200;
    const CREATED      = 201;
    const NO_CONTENT   = 204;
    const BAD_REQUEST  = 400;
    const UNAUTHORIZED = 401;
    const NOT_FOUND    = 404;
    const FORBIDDEN    = 403;
    const SERVER_ERROR = 500;
    const CREATE = 'created';
    const UPDATE = 'updated';
    const DELETE = 'deleted';

    public function createResponse($callbacks)
    {
        $em = $this->getDoctrine()->getManager();

        if (isset($callbacks['validate'])) {
            $errors = $callbacks['validate']();
            if ($errors !== true) {
                return $this->json(['errors' => $errors], self::BAD_REQUEST);
            }
        }

        $em->getConnection()->beginTransaction();
        try {
            list($data, $statusCode) = $callbacks['response']();
            $em->getConnection()->commit();
            return $this->json($data, $statusCode);
        } catch (\Exception $ex) {
            $em->getConnection()->rollback();
            throw $ex;
        }
    }
}
