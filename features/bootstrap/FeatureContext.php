<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

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
        $this->apiContext = $env
            ->getContext(
                ApiContextAuth::class
            )
        ;
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
        $this->apiContext->logout('Authorization', '');
    }

    /**
     * @Given there are Categories with the following details:
     * @param TableNode $categories
     * @throws \Imbo\BehatApiExtension\Exception\AssertionFailedException
     */
    public function thereAreCategoriesWithTheFollowingDetails(TableNode $categories)
    {
        $i = 1;
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
    }

    /**
     * @When a demo scenario sends a request to :arg1
     * @param $arg1
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

    /**
     * @Given there are SubCategories with the following details:
     * @param TableNode $table
     * @throws \Imbo\BehatApiExtension\Exception\AssertionFailedException
     */
    public function thereAreSubcategoriesWithTheFollowingDetails(TableNode $table)
    {
        $i = 1;
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
    }

    /**
     * @Given there are ExtraFieldDefs with the following details:
     * @param TableNode $table
     * @throws \Imbo\BehatApiExtension\Exception\AssertionFailedException
     */
    public function thereAreExtrafielddefsWithTheFollowingDetails(TableNode $table)
    {
        $i = 1;
        foreach ($table->getColumnsHash() as $extraFieldDef) {
            $isPrice = $extraFieldDef['isPrice'];
            $extraFieldDef['isPrice'] = $extraFieldDef['isPrice'] == 'true';
            $isWeight = $extraFieldDef['isWeight'];
            $extraFieldDef['isWeight'] = $extraFieldDef['isWeight'] == 'true';
            $catId = $extraFieldDef["category"];
            unset($extraFieldDef["category"]);
            $subCatId = $extraFieldDef["subcategory"];
            unset($extraFieldDef["subcategory"]);
            $this->apiContext->setRequestBody(
                json_encode($extraFieldDef)
            );
            $this->apiContext->requestPath(
                "/api/category/{$catId}/subcategory/{$subCatId}/extrafielddef",
                'POST'
            );

            $expectedResult = [
                "{"
                , "\"id\": {$i}"
                , ",\"type\": \"{$extraFieldDef['type']}\""
                , ",\"name\": \"{$extraFieldDef['name']}\""
                , ",\"isPrice\": {$isPrice}"
                , ",\"isWeight\": {$isWeight}"
            ];
            if(isset($extraFieldDef['linkTo']) && $extraFieldDef['linkTo'] !== '')
            {
                $linkToRef = (int)$extraFieldDef['linkTo'];
                $linkTo = $table->getColumnsHash()[$linkToRef - 1];
                $expectedResult[] = ",\"linkTo\": {";
                $expectedResult[] = "\"id\": {$linkToRef}";
                $expectedResult[] = ", \"type\": \"{$linkTo['type']}\"";
                $expectedResult[] = ", \"name\": \"{$linkTo['name']}\"";
                $expectedResult[] = ", \"isPrice\": {$linkTo['isPrice']}";
                $expectedResult[] = ", \"isWeight\": {$linkTo['isWeight']}";
                $expectedResult[] = "}";
            }
            $expectedResult[] = "}";

            $this->apiContext->assertResponseBodyContainsJson(
                new \Behat\Gherkin\Node\PyStringNode(
                    $expectedResult,0
                ));

            $i ++;
        }
    }

    /**
     * @Given there are Brands with the following details:
     * @param TableNode $brands
     * @throws \Imbo\BehatApiExtension\Exception\AssertionFailedException
     */
    public function thereAreBrandsWithTheFollowingDetails(TableNode $brands)
    {
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
    }

    /**
     * @Given there are Equipments with the following details:
     * @param TableNode $equipments
     */
    public function thereAreEquipmentsWithTheFollowingDetails(TableNode $equipments)
    {
        $i = 1;
        foreach ($equipments->getColumnsHash() as $equipment) {
            $this->apiContext->setRequestBody(
                json_encode($equipment)
            );
            $this->apiContext->requestPath(
                '/api/user/1/equipment',
                'POST'
            );
            $i ++;
        }
    }

    /**
     * @Given /^there are ExtraField with the following details:$/
     * @param TableNode $extraFields
     */
    public function thereAreExtraFieldWithTheFollowingDetails(TableNode $extraFields)
    {
        $i = 1;
        foreach ($extraFields->getColumnsHash() as $extraField) {
            $eqId = $extraField["equipment"];
            unset($extraField["equipment"]);
            $extraField['isPrice'] = $extraField['isPrice'] == 'true';
            $extraField['isWeight'] = $extraField['isWeight'] == 'true';
            $this->apiContext->setRequestBody(
                json_encode($extraField)
            );
            $this->apiContext->requestPath(
                "/api/user/1/equipment/{$eqId}/extrafield",
                'POST'
            );
            $i ++;
        }
    }

    /**
     * @Given /^there are User with the following details:$/
     * @param TableNode $users
     */
    public function thereAreUserWithTheFollowingDetails(TableNode $users)
    {
        foreach ($users->getColumnsHash() as $user) {
            $this->apiContext->setRequestBody(
                json_encode($user)
            );
            $this->apiContext->requestPath(
                "/api/auth/register",
                'POST'
            );
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
            $this->apiContext->setRequestBody(
                json_encode($have)
            );
            $this->apiContext->requestPath(
                "/api/user/{$eqId}/have",
                'POST'
            );
        }
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
}
