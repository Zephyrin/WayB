Feature: Provide a consistent standard JSON API endpoint

  In order to build interchangeable front ends
  As a JSON API developer
  I need to allow Create, Read, Update, and Delete functionality

  Background:
    Given there are Categories with the following details:
      | name     |
      | Clothe    |
      | Sleeping  |
      | Accessory |

  Scenario: Can get a single Category
    Given I request "/api/category/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "name": "Clothe",
      "subCategories": []
    }
    """

  Scenario: Can get a collection of Categories
    Given I request "/api/category" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    [
      {
        "id": 1,
        "name": "Clothe",
        "subCategories": []
      },
      {
        "id": 2,
        "name": "Sleeping",
        "subCategories": []
      },
      {
        "id": 3,
        "name": "Accessory",
        "subCategories": []
      }
    ]
    """

  Scenario: Can add a new Category
    Given the request body is:
      """
      {
        "name": "Kitchen",
        "subCategories": []
      }
      """
    When I request "/api/category" using HTTP POST
    Then the response code is 201
    And the response body contains JSON:
    """
      {
        "id": 4,
        "name": "Kitchen",
        "subCategories": []
      }
    """

  Scenario: Cannot add a new Category with an existing name
    Given the request body is:
      """
      {
        "name": "Clothe",
        "subCategories": []
      }
      """
    When I request "/api/category" using HTTP POST
    Then the response code is 422
    And the response body contains JSON:
    """
    {
        "status": "error",
        "errors": [{
            "children": {
                "name": {
                    "errors": [
                        "This category name is already in use."
                    ]
                },
                "subCategories": []
            }
        }]
    }
    """


  Scenario: Can update an existing Category - PUT
    Given the request body is:
      """
      {
        "name": "Cooking",
        "subCategories": []
      }
      """
    When I request "/api/category/2" using HTTP PUT
    Then the response code is 204

  Scenario: Cannot update a new Category with an existing name
    Given the request body is:
      """
      {
        "name": "Clothe",
        "subCategories": []
      }
      """
    When I request "/api/category/2" using HTTP PUT
    Then the response code is 422
    And the response body contains JSON:
    """
    {
        "status": "error",
        "errors": [{
            "children": {
                "name": {
                    "errors": [
                        "This category name is already in use."
                    ]
                },
                "subCategories": []
            }
        }]
    }
    """

  Scenario: Cannot update an unknown Category - PUT
    Given the request body is:
      """
      {
        "name": "Cooking",
        "subCategories": []
      }
      """
    When I request "category/4" using HTTP PUT
    Then the response code is 404

  Scenario: Can update an existing Category - PATCH
    Given the request body is:
      """
      {
        "name": "Sleep",
        "subCategories": []
      }
      """
    When I request "/api/category/2" using HTTP PATCH
    Then the response code is 204

  Scenario: Can delete an Category
    Given I request "/api/category/3" using HTTP GET
    Then the response code is 200
    When I request "/api/category/3" using HTTP DELETE
    Then the response code is 204
    When I request "/api/category/3" using HTTP GET
    Then the response code is 404

  Scenario: Must have a non-blank name
    Given the request body is:
      """
      {
        "name": "",
        "subCategories": []
      }
      """
    When I request "/api/category" using HTTP POST
    Then the response code is 422
    And the response body contains JSON:
    """
    {
        "status": "error",
        "message": "Validation failed",
        "errors": [{
            "children": {
                "name": {
                    "errors": [
                        "This value should not be blank."
                    ]
                }
            }
        }]
    }
    """
