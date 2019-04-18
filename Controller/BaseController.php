<?php

namespace Wolfmatrix\RestApiBundle\Controller;

use Wolfmatrix\RestApiBundle\Service\FormHelper;
use Wolfmatrix\RestApiBundle\Controller\BaseApiController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends BaseApiController
{

    public function parseUrl ($em, $pathInfo)
    {

        $urlParts = array_filter(explode("/", $pathInfo));
        $flipUrlParts = array_flip($urlParts);
        $resource = array_search(2, $flipUrlParts);
        $entityName = (ucwords(rtrim($resource, "s")));
        $namespace = "App\\Entity\\$entityName";
        $entityRepo = $em->getRepository($namespace);

        return [$urlParts, $entityName, $entityRepo, $namespace];
    }

    public function saveResource (Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $formHelper = $this->container->get(FormHelper::class);
        list($urlParts, $entityName, $entityRepo, $namespace) = $this->parseUrl($em, $request->getPathInfo());
        $requestBody = json_decode($request->getContent(), 1) ?: [];

        if (sizeof($urlParts ) > 2) {
            $updateFlag = true;
            $entity = $em->getRepository($namespace)->find(array_shift($urlParts));

        } else {
            $entity = new $namespace;
            $updateFlag = false;
        }

        return $this->createResponse([
            'validate' => function () use ( $requestBody, $updateFlag, $em, $entityRepo, $formHelper,
                $entityName, $entity) {

                $formNamespace = "App\\Form\\{$entityName}Type";
                $form = $this->createForm($formNamespace, $entity);

                $organizedRequest = $formHelper->organizeRequest(
                    $requestBody,
                    $form,
                    $entityRepo,
                    $updateFlag
                );

                $form->submit($organizedRequest);
                if (!$form->isValid()) {
                    $error = $formHelper->getErrorsFromForm($form);
                    return $error;
                }
                return true;
            },
            'response' => function () use ($entity, $em, $updateFlag, $formHelper, $entityName) {

                $em->persist($entity);
                $em->flush();

                //dispatch event
                if ($updateFlag) {
                    $formHelper->dispatchEvent($entity, $entityName, self::UPDATE);
                } else {
                    $formHelper->dispatchEvent($entity, $entityName, self::CREATE);
                }

                return [$entity->toArray(), ($updateFlag ? self::OK : self::CREATED)];
            }

        ]);
    }

    public function listResource (Request $request)
    {
        return $this->createResponse([
            'response' => function () use ($request) {
                $em   = $this->getDoctrine()->getManager();
                list($urlParts, $entityName, $entityRepo, $namespace) = $this->parseUrl($em, $request->getPathInfo());
                $data = $em->getRepository($namespace)
                    ->searchFilterSort(
                        $request->get('search'),
                        $request->get('filters'),
                        $request->get('sort')
                    )
                    ->setPage($request->get('page', 1), $request->get('pageSize'))
                    ->getResults()
                ;

                return [$data, self::OK];
            }
        ]);
    }

    public function detailResource (Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        list($urlParts, $entityName, $entityRepo, $namespace) = $this->parseUrl($em, $request->getPathInfo());

        return $this->createResponse([
            'response' => function () use ($em, $namespace, $urlParts) {

                $entity = $em->getRepository($namespace)->find(array_shift($urlParts));

                return [$entity->toArray(), self::OK];
            }
        ]);
    }

    public function deleteResource (Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        list($urlParts, $entityName, $entityRepo, $namespace) = $this->parseUrl($em, $request->getPathInfo());
        $formHelper = $this->container->get(FormHelper::class);
        return $this->createResponse([
            'response' => function () use ($em, $namespace, $urlParts, $formHelper, $entityName) {

                $entity = $em->getRepository($namespace)->find(array_shift($urlParts));
                $id = $entity->getId();

                $em->remove($entity);
                $em->flush();

                //dispatch event
                $formHelper->dispatchEvent(array('id'=>$id), $entityName, self::DELETE);

                return [null, self::NO_CONTENT];
            }
        ]);
    }
}
