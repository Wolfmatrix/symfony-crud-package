# symfony-crud-package
This bundle provides crud operations for symfony projects.

## Requirements
* PHP 7+
* Symfony 4
* [Doctrine ORM](https://packagist.org/packages/symfony/orm-pack)
* [Symfony Validator](https://packagist.org/packages/symfony/validator)
* [Pagerfanta](https://packagist.org/packages/pagerfanta/pagerfanta)
* [Symfony Form](https://packagist.org/packages/symfony/form)

## Package Installation
To install the symfony-crud-package, configure **composer.json** file.
```
"require": {
    "wolfmatrix/restapibundle": "dev-master"
},
"repositories": [
    {
        "type": "git",
        "url": "https://github.com/Wolfmatrix/symfony-crud-package.git",
        "options": {
            "symlink": true
        }
    }
],
"autoload": {
    "psr-4": {
        "Wolfmatrix\\RestApiBundle\\": "wolfmatrix/restapibundle/"
    }
},
```
After configuring composer.json file, run the command
```
composer update
```
Updating composer might give Fatal error of TranslatorInterface. To solve this error, 
temporary add to requirements "symfony/translation": "4.2.*". i,e.
```
"require": {
    "symfony/translation": "4.2.*"
},
```

## Package Usage
In order to perform crud operations, follow three steps
* Create Entity
* Create FormType
* Create routes

### Create Entity
 For example, create user entity User.php inside app/Entity and generate getters & setters.
 Before creating migration file, install maker bundle i,e.
 ```
 composer require --dev symfony/maker-bundle
 ```
 After that create migration file. i,e.
 ```
 php bin/console make:migration
 ```
 And then migrate the migration file. i,e.
 ```
 php bin/console doctrine:migrations:migrate
 ```
 For search, sort and filter, create UserRepository.php at **app/Repository**. eg:
 ```
 <?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Wolfmatrix\RestApiBundle\Entity\BaseApiEntity;

class UserRepository extends BaseEntity
{
    public $searchable = [
        'id'
    ];

    public $filterable = [
        'id'
    ];

    public $sortable = [
        'id'
    ];

    public $alias = [];
}
```

 ### Create FormType
 Create UserType.php form at **app/Form**
 
 ### Create Routes
 Create routes for crud operations at **app/config/routes.yaml**.
 
 eg:
 ```
 user_create:
  path: api/users
  controller: Wolfmatrix\RestApiBundle\Controller\BaseController::saveResource
  methods: [POST]
```
