<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
/**
 * This context class contains the definitions of the steps used by the demo 
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 * 
 * @see http://behat.org/en/latest/quick_start.html
 */
class FeatureContext implements Context
{
    /**
     * @var ApiContextAuth apiContext
     */
    private $apiContext;
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /** @BeforeScenario
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(
        BeforeScenarioScope $scope
    )
    {
        $env = $scope
            ->getEnvironment();
        $this->apiContext = $env->getContext(
                ApiContextAuth::class
            )
        ;
        $files = glob("public/media/*"); // get all file names
        foreach($files as $file){ // iterate files
        if(is_file($file))
            unlink($file); // delete file
        }
    }

    /**
     * @BeforeScenario
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function cleanUpDatabase()
    {
        $metaData = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropDatabase();
        if (!empty($metaData)) {
            $schemaTool->createSchema($metaData);
        }
    }

    /**
     * @BeforeScenario
     * @login
     *
     * @see https://symfony.com/doc/current/security/entity_provider.html#creating-your-first-user
     */
    public function login()
    {
        $this->apiContext->setRequestBody(
            '{ 
                "username": "superadmin"
                , "password": "a" 
            }'
        );
        $this->apiContext->requestPath(
            '/api/auth/login',
            'POST'
        );

        $this->apiContext->getTokenFromLogin();
    }

    /**
     * @AfterScenario
     * @logout
     */
    public function logout() {
        /* $this->apiContext->requestPath(
            '/api/auth/logout',
            'POST'
        ); */
        $this->apiContext->logout();
    }

    /**
     * @Given there are Categories with the following details:
     * @param TableNode $categories
     * @throws \Imbo\BehatApiExtension\Exception\AssertionFailedException
     */
    public function thereAreCategoriesWithTheFollowingDetails(TableNode $categories)
    {
        $i = 1;
        $this->iAmLoginAsA();
        foreach ($categories->getColumnsHash() as $category) {
            $this->apiContext->setRequestBody(
                json_encode($category)
            );
            $this->apiContext->requestPath(
                '/api/category',
                'POST'
            );
            $expectedResult = [
                "{"
                , "\"id\": {$i},"
                , "\"name\": \"{$category['name']}\""
                , "}"
            ];
            $this->apiContext->assertResponseBodyContainsJson(
                new \Behat\Gherkin\Node\PyStringNode(
                    $expectedResult
                    , 0
                ));
            $i ++;
        }
        $this->logout();
    }

    /**
     * @Given there are SubCategories with the following details:
     * @param TableNode $table
     * @throws \Imbo\BehatApiExtension\Exception\AssertionFailedException
     */
    public function thereAreSubcategoriesWithTheFollowingDetails(TableNode $table)
    {
        $i = 1;
        $this->iAmLoginAsA();
        foreach ($table->getColumnsHash() as $subCategory) {

            $catId = $subCategory['category'];
            unset($subCategory["category"]);
            $this->apiContext->setRequestBody(
                json_encode($subCategory)
            );

            $this->apiContext->requestPath(
                "/api/category/{$catId}/subcategory",
                'POST'
            );

            $expectedResult = [
                "{"
                , "\"id\": {$i},"
                , "\"name\": \"{$subCategory['name']}\""
                , "}"
            ];
            $this->apiContext->assertResponseBodyContainsJson(
                new \Behat\Gherkin\Node\PyStringNode(
                    $expectedResult
                    , 0
                ));
            $i ++;
        }
        $this->logout();
    }

    /**
     * @Given there are Brands with the following details:
     * @param TableNode $brands
     * @throws \Imbo\BehatApiExtension\Exception\AssertionFailedException
     */
    public function thereAreBrandsWithTheFollowingDetails(TableNode $brands)
    {
        $this->iAmLoginAsA();
        $i = 1;
        foreach ($brands->getColumnsHash() as $brand) {
            $this->apiContext->setRequestBody(
                json_encode($brand)
            );
            $this->apiContext->requestPath(
                '/api/brand',
                'POST'
            );
            $expectedResult = [
                "{"
                , "\"id\": {$i},"
                , "\"name\": \"{$brand['name']}\","
                , "\"description\": \"{$brand['description']}\","
                , "\"uri\": \"{$brand['uri']}\""
                , "}"
            ];
            $this->apiContext->assertResponseBodyContainsJson(
                new \Behat\Gherkin\Node\PyStringNode(
                    $expectedResult
                    , 0
                ));
            $i ++;
        }
        $this->logout();
    }

    /**
     * @Given there are Equipments with the following details:
     * @param TableNode $equipments
     */
    public function thereAreEquipmentsWithTheFollowingDetails(TableNode $equipments)
    {
        $i = 1;
        $this->iAmLoginAsA();
        foreach ($equipments->getColumnsHash() as $equipment) {
            if(array_key_exists("validate", $equipment))
                $equipment['validate'] = $equipment['validate'] == 'true';
            $this->apiContext->setRequestBody(
                json_encode($equipment)
            );
            $this->apiContext->requestPath(
                '/api/equipment',
                'POST'
            );
            $i ++;
        }
        $this->logout();
    }

    /**
     * @Given /^there are Characteristic with the following details:$/
     * @param TableNode $characteristics
     */
    public function thereAreCharacteristicWithTheFollowingDetails(TableNode $characteristics)
    {
        foreach ($characteristics->getColumnsHash() as $characteristic) {
            $eqId = $characteristic["equipment"];
            unset($characteristic["equipment"]);
            if($eqId == "1")
                $this->iAmLoginAsA();
            else 
                $this->iAmLoginAsB();
            $price = $characteristic["price"];
            $characteristic["price"] = intval($price);
            $weight = $characteristic["weight"];
            $characteristic["weight"] = intval($weight);
            $this->apiContext->setRequestBody(
                json_encode($characteristic)
            );

            $this->apiContext->requestPath(
                "/api/equipment/{$eqId}/characteristic",
                'POST'
            );
            $this->logout();
        }
        
    }

    /**
     * @Given /^there are User with the following details:$/
     * @param TableNode $users
     */
    public function thereAreUserWithTheFollowingDetails(TableNode $users)
    {
        $i = 1;
        $this->logout();
        foreach ($users->getColumnsHash() as $user) {
            $role = $user['ROLE'];
            unset($user['ROLE']);
            $this->apiContext->setRequestBody(
                json_encode($user)
            );
            $this->apiContext->requestPath(
                "/api/auth/register",
                'POST'
            );
            $this->apiContext->getTokenFromLogin();
            if($role !== 'ROLE_USER')
            {
                $this->logout();
                $this->login();
                $this->apiContext->setRequestBody(
                    "{ \"roles\": [\"{$role}\"] }"
                );
                $this->apiContext->requestPath(
                    "/api/user/{$i}",
                    'PATCH'
                );
            }
            $i ++;
            $this->logout();
        }
    }

    /**
     * @Given /^there are Have with the following details:$/
     * @param TableNode $haves
     */
    public function thereAreHaveWithTheFollowingDetails(TableNode $haves)
    {
        foreach ($haves->getColumnsHash() as $have) {
            $eqId = $have["user"];
            unset($have["user"]);
            if($eqId == "1")
                $this->iAmLoginAsA();
            else 
                $this->iAmLoginAsB();
            $this->apiContext->setRequestBody(
                json_encode($have)
            );
            $this->apiContext->requestPath(
                "/api/user/{$eqId}/have",
                'POST'
            );
            $this->logout();
        }
    }

    /**
     * @Given /^there are MediaObject with the following details:$/
     * @param TableNode $mediaObjects
     */
    public function thereAreMediaObjectWithTheFollowingDetails(TableNode $mediaObjects) 
    {
        $this->iAmLoginAsA();
        foreach($mediaObjects->getColumnsHash() as $media) {
            $this->apiContext->setRequestBody(
                json_encode($media)
            );
            $this->apiContext->requestPath(
                "/api/mediaobject",
                'POST'
            );
        }
        $this->logout();
    }

    /**
     * @Given /^I am Login As A$/
     */
    public function iAmLoginAsA()
    {
        $this->apiContext->Logout();
        $this->apiContext->setRequestBody(
            '{ 
                "username": "a"
                , "password": "a" 
            }'
        );
        $this->apiContext->requestPath(
            '/api/auth/login',
            'POST'
        );

        $this->apiContext->getTokenFromLogin();
    }

    /**
     * @Given /^I am Login As B$/
     */
    public function iAmLoginAsB()
    {
        $this->apiContext->Logout();
        $this->apiContext->setRequestBody(
            '{ 
                "username": "b"
                , "password": "b" 
            }'
        );
        $this->apiContext->requestPath(
            '/api/auth/login',
            'POST'
        );

        $this->apiContext->getTokenFromLogin();
    }

    /**
     * @Then print last response
     */
    public function printLastResponse()
    {

    }
    
    /**
    * @When a demo scenario sends a request to :arg1
    */
    public function aDemoScenarioSendsARequestTo($arg1)
    {
    }

    /**
    * @Then the response should be received
    */
    public function theResponseShouldBeReceived()
    {
    }
}
