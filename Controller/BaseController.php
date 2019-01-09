<?php

namespace Crud\ReusableBundle\Controller;

use Crud\ReusableBundle\Service\FormHelper;
use Crud\ReusableBundle\Controller\BaseApiController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends BaseApiController
{

    public function parseUrl ($em, $pathInfo)
    {

        $urlParts = array_filter(explode("/", $pathInfo));
        $resource = array_shift($urlParts);
        $entityName = (ucwords(rtrim($resource, "s")));
        $namespace = "App\\Entity\\$entityName";
        $entityRepo = $em->getRepository($namespace);

        return [$urlParts, $entityName, $entityRepo, $namespace];
    }

    public function saveResource (Request $request, FormHelper $formHelper)
    {
        $em = $this->getDoctrine()->getManager();
        list($urlParts, $entityName, $entityRepo, $namespace) = $this->parseUrl($em, $request->getPathInfo());
        $requestBody = json_decode($request->getContent(), 1) ?: [];
        $currentUser = 'abc@xyz.com';

        if (sizeof($urlParts ) > 0) {
            $updateFlag = true;
            $entity = $em->getRepository($namespace)->find(array_shift($urlParts));

        } else {
            $entity = new $namespace;
            $updateFlag = false;
        }

        return $this->createResponse([
            'validate' => function () use ( $requestBody, $currentUser, $updateFlag, $em, $entityRepo, $formHelper,
                $entityName, $entity) {

                $formNamespace = "App\\Form\\{$entityName}Type";
                $form = $this->createForm($formNamespace, $entity);

                $organizedRequest = $formHelper->organizeRequest(
                    $requestBody,
                    $form,
                    $entityRepo,
                    $currentUser,
                    $updateFlag
                );

                $form->submit($organizedRequest);
                if (!$form->isValid()) {
                    $error = $formHelper->getErrorsFromForm($form);
                    return $error;
                }
                return true;
            },
            'response' => function () use ($entity, $em, $updateFlag, $currentUser) {
                $entity->setUpdatedOn(new \DateTime());
                $entity->setUpdatedBy($currentUser);

                if (!$updateFlag) {
                    $entity->setCreatedBy($currentUser);
                    $entity->setCreatedOn(new \DateTime());
                }
                $em->persist($entity);
                $em->flush();

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
        return $this->createResponse([
            'response' => function () use ($em, $namespace, $urlParts) {

                $entity = $em->getRepository($namespace)->find(array_shift($urlParts));

                $em->remove($entity);
                $em->flush();

                return [null, self::NO_CONTENT];
            }
        ]);
    }
}
