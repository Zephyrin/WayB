Feature: Provide a consistent standard JSON API endpoint

  In order to build interchangeable front ends
  As a JSON API developer
  I need to allow Create, Read, Update, and Delete functionality

  Background:
    Given there are User with the following details:
      | username | password | email     | gender | ROLE            |
      | a        | a        | a.b@c.com | MALE   | ROLE_AMBASSADOR |
      | b        | b        | b.b@c.com | MALE   | ROLE_USER       |
    Given there are MediaObject with the following details:
      | description  | image |
      | Logo Katadyn | data:image/png;base64,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 |
      | Logo MSR     | data:image/png;base64,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 |

  Scenario: Can get a single MediaObject if I am connected
    Given I am Login As A
    Then I request "/api/mediaobject/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "description": "Logo Katadyn",
      "file_path": "@regExp(/.+\\.png/)"
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


    Given I am Login As A
    Then the request body is:
      """
      {
        "logo": {

        }
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
        "logo": "/media_objects/1"
      }
      """