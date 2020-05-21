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
      | name           | validate | uri                 |
      | MSR            | true     | www.msr.com         |
      | Mammut         | false    | www.mammut.com      |
      | The north face | false    | www.thenorthface.fr |

  Scenario: Can get a single Brand if I am connected
    Given I am Login As A
    Then I request "/api/brand/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "name": "MSR",
      "validate": true,
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
        "validate": true,
        "uri": "www.msr.com"
      },
      {
        "id": 2,
        "name": "Mammut",
        "validate": false,
        "uri": "www.mammut.com"
      },
      {
        "id": 3,
        "name": "The north face",
        "validate": false,
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
        "validate": false,
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
        "validate": false,
        "uri": "www.rab.fr"
      }
    """
  
  Scenario: Can add a new Brand (ROLE_USER)
    Given I am Login As B
    Then the request body is:
      """
      {
        "name": "Rab",
        "validate": true,
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
        "validate": false,
        "uri": "www.rab.fr"
      }
    """    

  Scenario: Cannot add a new Brand with an existing name
    Given I am Login As A
    Then the request body is:
      """
      {
        "name": "MSR",
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
                "validate": { },
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
        "validate": true,
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
        "validate": false,
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
        "validate": false,
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
                "validate": { },
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
        "validate": false,
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
        "validate": true
      }
      """
    When I request "/api/brand/1" using HTTP PATCH
    Then the response code is 204
  
  Scenario: Cannot update an existing Brand - PATCH ROLE_USER
    Given I am Login As B
    Then the request body is:
      """
      {
        "name": "MSR 2",
        "validate": true
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
        "validate": false,
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
        "validate": false,
        "uri": "blank desc"
      }
      """
    When I request "/api/brand" using HTTP POST
    Then the response code is 403

  Scenario: Add an image to the brand - PUT
    Given I am Login As A
    Then the request body is:
      """
      {
        "description": "MSR Logo",
        "image": "data:image/png;base64,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"
      }
      """
    When I request "/api/mediaobject" using HTTP POST
    And the response code is 201
    Then the request body is:
      """
      {
        "description": "MSR",
        "logo": 1
      }
      """
    When I request "/api/brand/1" using HTTP PATCH
    Then the response code is 204
    And I request "/api/brand/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "name": "MSR",
      "validate": true,
      "uri": "www.msr.com",
      "logo": {
        "id": 1,
        "file_path": "@regExp(/.+\\.png/)",
        "description": "MSR Logo"
      }
    }
    """

  Scenario: Add an image to the brand - POST
    Given I am Login As A
    Then the request body is:
      """
      {
        "description": "MSR Logo",
        "image": "data:image/png;base64,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"
      }
      """
      When I request "/api/mediaobject" using HTTP POST
      Then the response code is 201
      Then the request body is:
       """
      {
        "name": "Rab",
        "validate": false,
        "uri": "www.rab.fr",
        "logo": { "id": 1 }
      }
      """
      When I request "/api/brand" using HTTP POST
      Then the response code is 201
      And the response body contains JSON:
      """
      {
        "id": 4,
        "name": "Rab",
        "validate": false,
        "uri": "www.rab.fr",
        "logo": {
          "id": 1,
          "file_path": "@regExp(/.+\\.png/)",
          "description": "MSR Logo"
        }
      }
      """