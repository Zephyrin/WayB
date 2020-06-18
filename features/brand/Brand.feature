Feature: Test Brand JSON API endpoint

  In order to build interchangeable front ends
  As a JSON API developer
  I need to allow Create, Read, Update, and Delete functionality

  Background:
    Given there are default users

  Scenario: Can I create a Brand if I am connected as normal user
    Given I am login as user
    Then the request body is:
    """
    {
        "name": "Rab",
        "uri": "www.rab.fr"
    }
    """
    When I request "/api/brand" using HTTP POST
    Then the response code is 201
    And the response body contains JSON:
    """
    {
        "id": 1,
        "createdBy": {
            "id": "@regExp(/[0-9]+/)",
            "username": "b",
            "roles": [
                "ROLE_USER"
            ],
            "gender": "MALE",
            "haves": [],
            "email": "b.b@c.com",
            "lastLogin": "@regExp(/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}.*/)"
        },
        "validate": false,
        "askValidate": false,
        "name": "Rab",
        "uri": "www.rab.fr"
    }
    """
    And the response body has 6 fields

  Scenario: Can I create a Brand as user. Then I cannot set it as validate.
    Given I am login as user
    Then the request body is:
    """
    { "name": "Rab", "uri": "www.rab.fr" }
    """
    When I request "/api/brand" using HTTP POST
    Then the response code is 201
    And the request body is:
    """
    { "askValidate": true }
    """
    When I request "/api/brand/1" using HTTP PATCH
    Then the response code is 204
    When the request body is:
    """
    { "validate": true}
    """
    When I request "/api/brand/1" using HTTP PATCH
    Then the response code is 403
    When I request "/api/brand/1" using HTTP GET
    Then the response code is 200
    And the response body is:
    """
    {
        "id": 1,
        "createdBy": {
            "id": "@regExp(/[0-9]+/)",
            "username": "b",
            "roles": [
                "ROLE_USER"
            ],
            "gender": "MALE",
            "haves": [],
            "email": "b.b@c.com",
            "lastLogin": "@regExp(/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}.*/)"
        },
        "validate": false,
        "askValidate": true,
        "name": "Rab",
        "uri": "www.rab.fr"
    }
    """
    And the response body has 6 fields

  Scenario: Can I create a Brand as user. Then I connect as AMBASSADOR and set validate to true.
    Given I am login as user
    Then the request body is:
    """
    { "name": "Rab", "uri": "www.rab.fr" }
    """
    When I request "/api/brand" using HTTP POST
    Then the response code is 201
    And the request body is:
    """
    { "askValidate": true }
    """
    Then I am login as admin
    When I request "/api/brand/1" using HTTP PATCH
    Then the response code is 204
    When the request body is:
    """
    { "validate": true}
    """
    When I request "/api/brand/1" using HTTP PATCH
    Then the response code is 403
    When I request "/api/brand/1" using HTTP GET
    Then the response code is 200
    And the response body is:
    """
    {
        "id": 1,
        "createdBy": {
            "id": "@regExp(/[0-9]+/)",
            "username": "b",
            "roles": [
                "ROLE_USER"
            ],
            "gender": "MALE",
            "haves": [],
            "email": "b.b@c.com",
            "lastLogin": "@regExp(/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}.*/)"
        },
        "validate": false,
        "askValidate": true,
        "name": "Rab",
        "uri": "www.rab.fr"
    }
    """
    And the response body has 6 fields
   