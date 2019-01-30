<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
/**
 * This context class contains the definitions of the steps used by the demo 
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 * 
 * @see http://behat.org/en/latest/quick_start.html
 */
class FeatureContext implements Context
{


    private $apiContext;

    public function __construct()
    {
    }

    /** @BeforeScenario */
    public function gatherContexts(
        BeforeScenarioScope $scope
    )
    {
        $this->apiContext = $scope
            ->getEnvironment()
            ->getContext(
                \Imbo\BehatApiExtension\Context\ApiContext::class
            )
        ;
    }

    /**
     * @BeforeScenario
     */
    public function cleanUpDatabase()
    {
        $host = 'sqlite:///home/ptipc/Développement/WayB/var/app.db';
        $user = '';
        $pass = '';

        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($host, $user, $pass, $opt);
        $pdo->query('delete from category');
        $pdo->query('delete from sub_category');
        $pdo->query('delete from extra_field_def');
        $pdo->query('delete from sqlite_sequence where name=\'category\' or name=\'sub_category\' or name=\'extra_field_def\';');
    }

    /**
     * @Given there are Categories with the following details:
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

}
