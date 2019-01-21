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
        $pdo->query('delete from sqlite_sequence where name=\'category\' or name=\'sub_category\';');
    }

    /**
     * @Given there are Categories with the following details:
     */
    public function thereAreCategoriesWithTheFollowingDetails(TableNode $categories)
    {
        foreach ($categories->getColumnsHash() as $category) {
            $this->apiContext->setRequestBody(
                json_encode($category)
            );
            $this->apiContext->requestPath(
                '/category',
                'POST'
            );
            $expectedResult = ["{",'    "status": "ok"',"}"];
            $this->apiContext->assertResponseBodyIs(new \Behat\Gherkin\Node\PyStringNode($expectedResult,0));
            echo 'passed.';
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
        foreach ($table->getColumnsHash() as $category) {

            $this->apiContext->setRequestBody(
                json_encode($category)
            );

            $this->apiContext->requestPath(
                "/category/{$category['category']}/subcategory",
                'POST'
            );
            $expectedResult = ["{",'    "status": "ok"',"}"];
            $this->apiContext->assertResponseBodyIs(
                new \Behat\Gherkin\Node\PyStringNode(
                    $expectedResult,0
                ));
            echo 'ok passed.';
        }
    }

}
