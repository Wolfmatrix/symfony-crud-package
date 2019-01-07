<?php

namespace App\Crud\ReusableBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Valid;

class FormHelper
{
    private $fields = [];

    public function __construct(FormFactoryInterface $builder, EntityManagerInterface $em)
    {
        $this->factory = $builder;
        $this->em      = $em;
    }

    public function organizeRequest($requestBody, $form, $repo, $currentUser, $updateFlag = false)
    {
        $organized = [];
        foreach ($requestBody as $key => $item) {

            if (is_array($item)) {
                if (isset($item['id'])) {
                    $organized[$key] = $item['id'];
                    $this->fields[] = $key;
                } else {

                    if (!$updateFlag) {
                        $organized[$key]['createdBy'] = $currentUser;
                    }
                    $organized[$key]['lastUpdatedBy'] = $currentUser;

                    $organized[$key] = $this->organizeRequest(
                        $item,
                        $form,
                        $repo,
                        $currentUser,
                        $updateFlag
                    );
                }
            } else {
                $organized[$key] = $item;
            }
        }
        return $organized;
    }

    public function handleRequest($formType, $entity, $requestBody, $additionalField = [])
    {

        $form = $this->factory->create($formType, $entity);

        $form->submit(array_merge($requestBody, $additionalField));

        if (!$form->isValid()) {
            $error = $this->getErrorsFromForm($form);
            return $error;
        }
        return true;
    }

    public function getErrorsFromForm(FormInterface $form)
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                //$viewData = $childForm->getViewData();
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    if (in_array($childForm->getName(), $this->fields)) {
                        //dump($viewData);
                        $errors[$childForm->getName()]       = [];
                        $errors[$childForm->getName()]['id'] = $childErrors;
                    } else {
                        $errors[$childForm->getName()] = $childErrors;
                    }
                }
            }
        }
        return $errors;
    }
}