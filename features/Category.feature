Feature: Provide a consistent standard JSON API endpoint

  In order to build interchangeable front ends
  As a JSON API developer
  I need to allow Create, Read, Update, and Delete functionality

  Background:
    Given there are Categories with the following details:
      | label     |
      | Clothe    |
      | Sleeping  |
      | Accessory |

  Scenario: Can get a single Category
    Given I request "/category/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "label": "Clothe",
      "sub_categories": []
    }
    """

  Scenario: Can get a collection of Categories
    Given I request "/category" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    [
      {
        "id": 1,
        "label": "Clothe",
        "sub_categories": []
      },
      {
        "id": 2,
        "label": "Sleeping",
        "sub_categories": []
      },
      {
        "id": 3,
        "label": "Accessory",
        "sub_categories": []
      }
    ]
    """

  Scenario: Can add a new Category
    Given the request body is:
      """
      {
        "label": "Kitchen",
        "sub_categories": []
      }
      """
    When I request "/category" using HTTP POST
    Then the response code is 201

  Scenario: Can update an existing Category - PUT
    Given the request body is:
      """
      {
        "label": "Cooking",
        "sub_categories": []
      }
      """
    When I request "/category/2" using HTTP PUT
    Then the response code is 204

  Scenario: Cannot update an unknown Category - PUT
    Given the request body is:
      """
      {
        "label": "Cooking",
        "sub_categories": []
      }
      """
    When I request "category/4" using HTTP PUT
    Then the response code is 404

  Scenario: Can update an existing Category - PATCH
    Given the request body is:
      """
      {
        "label": "Sleep",
        "sub_categories": []
      }
      """
    When I request "/category/2" using HTTP PATCH
    Then the response code is 204

  Scenario: Can delete an Category
    Given I request "/category/3" using HTTP GET
    Then the response code is 200
    When I request "/category/3" using HTTP DELETE
    Then the response code is 204
    When I request "/category/3" using HTTP GET
    Then the response code is 404

  Scenario: Must have a non-blank name
    Given the request body is:
      """
      {
        "label": "",
        "sub_categories": []
      }
      """
    When I request "/category" using HTTP POST
    Then the response code is 422
    And the response body contains JSON:
    """
    {
        "status": "error",
        "message": "Validation failed",
        "errors": [{
            "children": {
                "label": {
                    "errors": [
                        "This value should not be blank."
                    ]
                }
            }
        }]
    }
    """
