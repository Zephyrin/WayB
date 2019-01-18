Feature: Provide a consistent standard JSON API endpoint

  In order to build interchangeable front ends
  As a JSON API developer
  I need to allow Create, Read, Update, and Delete functionality

  Background:
    Given there are Categories with the following details:
      | title      |
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
      "title": "Clothe"
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
        "title": "Clothe"
      },
      {
        "id": 2,
        "title": "Sleeping"
      },
      {
        "id": 3,
        "title": "Accessory"
      }
    ]
    """

  Scenario: Can add a new Category
    Given the request body is:
      """
      {
        "title": "Kitchen"
      }
      """
    When I request "/category" using HTTP POST
    Then the response code is 201

  Scenario: Can update an existing Category - PUT
    Given the request body is:
      """
      {
        "title": "Cooking"
      }
      """
    When I request "/category/2" using HTTP PUT
    Then the response code is 204

  Scenario: Can update an existing Category - PATCH
    Given the request body is:
      """
      {
        "title": "Sleep"
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
        "title": ""
      }
      """
    When I request "/category" using HTTP POST
    Then the response code is 400
    And the response body contains JSON:
    """
    {
        "status": "error",
        "errors": [{
            "children": {
                "title": {
                    "errors": [
                        "This value should not be blank."
                    ]
                }
            }
        }]
    }
    """
