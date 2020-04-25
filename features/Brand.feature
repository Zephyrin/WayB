Feature: Provide a consistent standard JSON API endpoint

  In order to build interchangeable front ends
  As a JSON API developer
  I need to allow Create, Read, Update, and Delete functionality

  Background:
    Given there are User with the following details:
      | username | password | email     | gender | ROLE            |
      | a        | a        | a.b@c.com | MALE   | ROLE_AMBASSADOR |
      | b        | b        | b.b@c.com | MALE   | ROLE_USER       |
    Given there are Brands with the following details:
      | name           | description         | uri                 |
      | MSR            | MSR Desc            | www.msr.com         |
      | Mammut         | Mammut Desc         | www.mammut.com      |
      | The north face | The north face desc | www.thenorthface.fr |

  Scenario: Can get a single Brand if I am connected
    Given I am Login As A
    Then I request "/api/brand/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "name": "MSR",
      "description": "MSR Desc",
      "uri": "www.msr.com"
    }
    """

  Scenario: Cannot get a single Brand if I am not connected
    Given I request "/api/brand/1" using HTTP GET
    Then the response code is 401

  Scenario: Can get a collection of Brands
    Given I am Login As A 
    Then I request "/api/brand" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    [
      {
        "id": 1,
        "name": "MSR",
        "description": "MSR Desc",
        "uri": "www.msr.com"
      },
      {
        "id": 2,
        "name": "Mammut",
        "description": "Mammut Desc",
        "uri": "www.mammut.com"
      },
      {
        "id": 3,
        "name": "The north face",
        "description": "The north face desc",
        "uri": "www.thenorthface.fr"
      }
    ]
    """

  Scenario: Can add a new Brand (ROLE_AMBASSADOR)
    Given I am Login As A
    Then the request body is:
      """
      {
        "name": "Rab",
        "description": "Rab desc",
        "uri": "www.rab.fr"
      }
      """
    When I request "/api/brand" using HTTP POST
    Then the response code is 201
    And the response body contains JSON:
    """
      {
        "id": 4,
        "name": "Rab",
        "description": "Rab desc",
        "uri": "www.rab.fr"
      }
    """
  
  Scenario: Cannot add a new Brand (ROLE_USER)
    Given I am Login As B
    Then the request body is:
      """
      {
        "name": "Rab",
        "description": "Rab desc",
        "uri": "www.rab.fr"
      }
      """
    When I request "/api/brand" using HTTP POST
    Then the response code is 403

  Scenario: Cannot add a new Brand with an existing name
    Given I am Login As A
    Then the request body is:
      """
      {
        "name": "MSR",
        "description": "MSR descr 2",
        "uri": "www.msr2.fr"
      }
      """
    When I request "/api/brand" using HTTP POST
    Then the response code is 422
    And the response body contains JSON:
    """
    {
        "status": "error",
        "errors": [{
            "children": {
                "name": {
                    "errors": [
                        "This brand name is already in use."
                    ]
                },
                "description": { },
                "uri": { }
            }
        }]
    }
    """


  Scenario: Can update an existing Brand - PUT ROLE_AMBASSADOR
    Given I am Login As A
    Then the request body is:
      """
      {
        "name": "MSR",
        "description": "MSR description",
        "uri": "www.MSR.com"
      }
      """
    When I request "/api/brand/1" using HTTP PUT
    Then the response code is 204

  Scenario: Cannot update an existing Brand - PUT ROLE_USER
    Given I am Login As B
    Then the request body is:
      """
      {
        "name": "MSR",
        "description": "MSR description",
        "uri": "www.MSR.com"
      }
      """
    When I request "/api/brand/1" using HTTP PUT
    Then the response code is 403

  Scenario: Cannot update a new Brand with an existing name
    Given I am Login As A
    Then the request body is:
      """
      {
        "name": "MSR",
        "description": "MSR description",
        "uri": "www.MSR.com"
      }
      """
    When I request "/api/brand/2" using HTTP PUT
    Then the response code is 422
    And the response body contains JSON:
    """
    {
        "status": "error",
        "errors": [{
            "children": {
                "name": {
                    "errors": [
                        "This brand name is already in use."
                    ]
                },
                "description": { },
                "uri": { }
            }
        }]
    }
    """

  Scenario: Cannot update an unknown Brand - PUT
    Given I am Login As A
    Then the request body is:
      """
      {
        "name": "Brand",
        "description": "toto",
        "uri": "uri"
      }
      """
    When I request "api/brand/4" using HTTP PUT
    Then the response code is 404

  Scenario: Can update an existing Brand - PATCH ROLE_AMBASSADOR
    Given I am Login As A
    Then the request body is:
      """
      {
        "name": "MSR 2",
        "description": "Description MSR"
      }
      """
    When I request "/api/brand/1" using HTTP PATCH
    Then the response code is 204
  
  Scenario: Can update an existing Brand - PATCH ROLE_USER
    Given I am Login As B
    Then the request body is:
      """
      {
        "name": "MSR 2",
        "description": "Description MSR"
      }
      """
    When I request "/api/brand/1" using HTTP PATCH
    Then the response code is 403

  Scenario: Can delete an Brand ROLE_AMBASSADOR
    Given I am Login As A
    Then I request "/api/brand/3" using HTTP GET
    Then the response code is 200
    When I request "/api/brand/3" using HTTP DELETE
    Then the response code is 204
    When I request "/api/brand/3" using HTTP GET
    Then the response code is 404

  Scenario: Cannot delete an Brand ROLE_USER
    Given I am Login As B
    Then I request "/api/brand/3" using HTTP GET
    Then the response code is 200
    When I request "/api/brand/3" using HTTP DELETE
    Then the response code is 403
    When I request "/api/brand/3" using HTTP GET
    Then the response code is 200

  Scenario: Must have a non-blank name
    Given I am Login As A
    Then the request body is:
      """
      {
        "name": "",
        "description": "blank desc",
        "uri": "blank desc"
      }
      """
    When I request "/api/brand" using HTTP POST
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

  Scenario: Must have a non-blank name but first should be ROLE ok
    Given I am Login As B
    Then the request body is:
      """
      {
        "name": "",
        "description": "blank desc",
        "uri": "blank desc"
      }
      """
    When I request "/api/brand" using HTTP POST
    Then the response code is 403

