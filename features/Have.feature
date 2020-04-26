Feature: Provide a consistent standard JSON API endpoint

  In order to build interchangeable front ends
  As a JSON API developer
  I need to allow Create, Read, Update, and Delete functionality

  Background:
    Given there are User with the following details:
      | username | password | email     | gender | ROLE            |
      | a        | a        | a.b@c.com | MALE   | ROLE_AMBASSADOR |
      | b        | b        | b.b@c.com | MALE   | ROLE_USER       |
    Given there are Categories with the following details:
      | name     |
      | Clothe    |
      | Sleeping  |
      | Accessory |
    Given there are SubCategories with the following details:
      | name         | category  |
      | Pants        | 1         |
      | Jacket       | 1         |
      | Sleeping Bag | 2         |
      | Mattress     | 2         |
      | Flash Light  | 3         |
    Given there are ExtraFieldDefs with the following details:
      | type   | name   | isPrice | isWeight | subcategory | linkTo | category |
      | ARRAY  | Size   | false   | false    | 1           |        | 1        |
      | NUMBER | Price  | true    | false    | 1           | 1      | 1        |
      | NUMBER | Weight | false   | true     | 1           | 1      | 1        |
    Given there are Brands with the following details:
      | name           | description         | uri                  |
      | MSR            | MSR Desc            | www.msr.com          |
      | Mammut         | Mammut Desc         | www.mammut.com       |
      | The north face | The north face desc | www.thenorthface.com |
    Given there are Equipments with the following details:
      | name                         | description   | brand | subCategory |
      | Men's Zoomie Rain Jacket     | Description 1 | 3     | 2           |
      | Men's Printed Cyclone Hoodie | Description 2 | 3     | 2           |
      | Men's Rafale Jacket          | Description 3 | 3     | 2           |
    Given there are Have with the following details:
      | user     | ownQuantity | wantQuantity | equipment |
      | 1        | 0           | 0            | 1         |
      | 1        | 1           | 0            | 2         |
      | 2        | 1           | 0            | 1         |

  Scenario: Can get a single Equipment With User A
    Given I am Login As A
    And I request "/api/user/1/have/1" using HTTP GET
    Then the response body contains JSON:
    """
    {
      "id": 1,
      "ownQuantity": 0,
      "wantQuantity": 0,
      "equipment": {
        "name": "Men's Zoomie Rain Jacket",
        "description": "Description 1",
        "extraFields": [ ],
        "brand": {
          "id": 3,
          "name": "The north face",
          "uri": "www.thenorthface.com",
          "description": "The north face desc"
        },
        "subCategory": {
          "id": 2,
          "name": "Jacket",
          "extraFieldDefs": { }
        }
      }
    }
    """
    And the response code is 200

  Scenario: Can get a single Equipment With User B belong to A
    Given I am Login As B
    And I request "/api/user/1/have/1" using HTTP GET
    Then the response body contains JSON:
    """
    {
      "id": 1,
      "ownQuantity": 0,
      "wantQuantity": 0,
      "equipment": {
        "name": "Men's Zoomie Rain Jacket",
        "description": "Description 1",
        "extraFields": [ ],
        "brand": {
          "id": 3,
          "name": "The north face",
          "uri": "www.thenorthface.com",
          "description": "The north face desc"
        },
        "subCategory": {
          "id": 2,
          "name": "Jacket",
          "extraFieldDefs": { }
        }
      }
    }
    """
    And the response code is 200

  Scenario: Can get a collection of have from user A
    Given I am Login As A
    Then I request "/api/user/1/have" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    [
      {
        "id": 1,
        "ownQuantity": 0,
        "wantQuantity": 0,
        "equipment": {
          "id": 1,
          "name": "Men's Zoomie Rain Jacket",
          "description": "Description 1",
          "subCategory": {
            "id": 2,
            "name": "Jacket",
            "extraFieldDefs": []
          },
          "brand": {
            "id": 3,
            "name": "The north face",
            "description": "The north face desc",
            "uri": "www.thenorthface.com"
          },
          "extraFields": [],
          "haves": [
            {
              "id": 3,
              "ownQuantity": 1,
              "wantQuantity": 0
            }
          ],
          "created_by": {
            "id": 1,
            "username": "a",
            "email": "a.b@c.com",
            "enabled": true,
            "gender": "MALE"
          },
          "validate": false
        }
      },
      {
        "id": 2,
        "ownQuantity": 1,
        "wantQuantity": 0,
        "equipment": {
          "id": 2,
          "name": "Men's Printed Cyclone Hoodie",
          "description": "Description 2",
          "subCategory": {
            "id": 2,
            "name": "Jacket",
            "extraFieldDefs": []
          },
          "brand": {
            "id": 3,
            "name": "The north face",
            "description": "The north face desc",
            "uri": "www.thenorthface.com"
          },
          "extraFields": [],
          "haves": [],
          "created_by": {
            "id": 1,
            "username": "a",
            "email": "a.b@c.com",
            "enabled": true,
            "gender": "MALE"
          },
          "validate": false
        }
      }
    ]
    """

  Scenario: Can get a collection of have from user A as login B
    Given I am Login As B
    Then I request "/api/user/1/have" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    [
      {
        "id": 1,
        "ownQuantity": 0,
        "wantQuantity": 0,
        "equipment": {
          "id": 1,
          "name": "Men's Zoomie Rain Jacket",
          "description": "Description 1",
          "subCategory": {
            "id": 2,
            "name": "Jacket",
            "extraFieldDefs": []
          },
          "brand": {
            "id": 3,
            "name": "The north face",
            "description": "The north face desc",
            "uri": "www.thenorthface.com"
          },
          "extraFields": [],
          "haves": [
            {
              "id": 3,
              "ownQuantity": 1,
              "wantQuantity": 0
            }
          ],
          "created_by": {
            "id": 1,
            "username": "a",
            "email": "a.b@c.com",
            "enabled": true,
            "gender": "MALE"
          },
          "validate": false
        }
      },
      {
        "id": 2,
        "ownQuantity": 1,
        "wantQuantity": 0,
        "equipment": {
          "id": 2,
          "name": "Men's Printed Cyclone Hoodie",
          "description": "Description 2",
          "subCategory": {
            "id": 2,
            "name": "Jacket",
            "extraFieldDefs": []
          },
          "brand": {
            "id": 3,
            "name": "The north face",
            "description": "The north face desc",
            "uri": "www.thenorthface.com"
          },
          "extraFields": [],
          "haves": [],
          "created_by": {
            "id": 1,
            "username": "a",
            "email": "a.b@c.com",
            "enabled": true,
            "gender": "MALE"
          },
          "validate": false
        }
      }
    ]
    """

  Scenario: Can link an existing equipment to the user A:
    Given I am Login As A
    Then the request body is:
      """
      {
        "ownQuantity": 0,
        "wantQuantity": 1,
        "equipment": 3
      }
      """
    When I request "/api/user/1/have" using HTTP POST
    Then the response code is 201
    And the response body contains JSON:
    """
    {
      "ownQuantity": 0,
      "wantQuantity": 1,
      "equipment": {
        "id": 3,
        "name": "Men's Rafale Jacket",
        "description": "Description 3",
        "subCategory": {
            "id": 2,
            "name": "Jacket",
            "extraFieldDefs": []
          },
          "brand": {
            "id": 3,
            "name": "The north face",
            "description": "The north face desc",
            "uri": "www.thenorthface.com"
          },
          "extraFields": [],
          "haves": [],
          "created_by": {
            "id": 1,
            "username": "a",
            "email": "a.b@c.com",
            "enabled": true,
            "gender": "MALE"
          },
          "validate": false
      }
    }
    """

  Scenario: Cannot link an not full json to the user A - POST:
    Given I am Login As A
    Then the request body is:
      """
      {
        "ownQuantity": 0,
        "wantQuantity": 1
      }
      """
    When I request "/api/user/1/have" using HTTP POST
    Then the response code is 422
    Then the request body is:
      """
      {
        "ownQuantity": 0,
        "equipment": 3
      }
      """
    When I request "/api/user/1/have" using HTTP POST
    Then the response code is 422
  
   Scenario: Cannot link an existing equipment to the user A with user B:
    Given I am Login As B
    Then the request body is:
      """
      {
        "ownQuantity": 0,
        "wantQuantity": 1,
        "equipment": 3
      }
      """
    When I request "/api/user/1/have" using HTTP POST
    Then the response code is 403

  Scenario: Cannot add a new Equipment using Entity for subCategory and brand
    Given the request body is:
      """
      {
        "name": "Jacket",
        "description": "Titi",
        "extraFields": [ ],
        "brand": {
          "id": 3,
          "name": "The north face",
          "uri": "www.thenorthface.com",
          "description": "The north face desc"
        },
        "subCategory": {
          "id": 2,
          "name": "Jacket",
          "extraFieldDefs": [ ]
        }
      }
      """
    When I request "/api/user/1/equipment" using HTTP POST
    Then the response code is 422
    And the response body contains JSON:
      """
      {
        "status": "error",
        "Message": "Validation error",
        "errors": [{
          "children": {
            "name": [],
            "description": [],
            "brand": {
              "errors": ["This value is not valid."]
            },
            "subCategory": {
              "errors": ["This value is not valid."]
            },
            "extraFields": []
          }
        }]
      }
      """
    When I request "/api/user/1/equipment/3" using HTTP GET
    Then the response code is 404

  Scenario: Can update an existing Have - PUT
    Given I am Login As A
    Then the request body is:
      """
      {
        "ownQuantity": 0,
        "wantQuantity": 1,
        "equipment": 1
      }
      """
    When I request "/api/user/1/have/1" using HTTP PUT
    Then the response code is 204
    When I request "/api/user/1/have/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "ownQuantity": 0,
      "wantQuantity": 1,
      "equipment": {
        "name": "Men's Zoomie Rain Jacket",
        "description": "Description 1",
        "extraFields": [ ],
        "brand": {
          "id": 3,
          "name": "The north face",
          "uri": "www.thenorthface.com",
          "description": "The north face desc"
        },
        "subCategory": {
          "id": 2,
          "name": "Jacket",
          "extraFieldDefs": { }
        }
      }
    }
    """

  Scenario: Cannot update an existing Have from an another user - PUT
    Given I am Login As B
    Then the request body is:
      """
      {
        "ownQuantity": 0,
        "wantQuantity": 1,
        "equipment": 1
      }
      """
    When I request "/api/user/1/have/1" using HTTP PUT
    Then the response code is 403
    When I request "/api/user/1/have/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "ownQuantity": 0,
      "wantQuantity": 0,
      "equipment": {
        "name": "Men's Zoomie Rain Jacket",
        "description": "Description 1",
        "extraFields": [ ],
        "brand": {
          "id": 3,
          "name": "The north face",
          "uri": "www.thenorthface.com",
          "description": "The north face desc"
        },
        "subCategory": {
          "id": 2,
          "name": "Jacket",
          "extraFieldDefs": { }
        }
      }
    }
    """

  Scenario: Can update an existing Equipment - PATCH
    Given I am Login As A
    Then the request body is:
      """
      {
        "wantQuantity": 1
      }
      """
    When I request "/api/user/1/have/1" using HTTP PATCH
    Then the response code is 204
    When I request "/api/user/1/have/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "ownQuantity": 0,
      "wantQuantity": 1,
      "equipment": {
        "name": "Men's Zoomie Rain Jacket",
        "description": "Description 1",
        "extraFields": [ ],
        "brand": {
          "id": 3,
          "name": "The north face",
          "uri": "www.thenorthface.com",
          "description": "The north face desc"
        },
        "subCategory": {
          "id": 2,
          "name": "Jacket",
          "extraFieldDefs": { }
        }
      }
    }
    """

  Scenario: Canot update an existing Equipment of an another user - PATCH
    Given I am Login As B
    Then the request body is:
      """
      {
        "wantQuantity": 1
      }
      """
    When I request "/api/user/1/have/1" using HTTP PATCH
    Then the response code is 403
    When I request "/api/user/1/have/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "ownQuantity": 0,
      "wantQuantity": 0,
      "equipment": {
        "name": "Men's Zoomie Rain Jacket",
        "description": "Description 1",
        "extraFields": [ ],
        "brand": {
          "id": 3,
          "name": "The north face",
          "uri": "www.thenorthface.com",
          "description": "The north face desc"
        },
        "subCategory": {
          "id": 2,
          "name": "Jacket",
          "extraFieldDefs": { }
        }
      }
    }
    """

  Scenario: Can delete an have
    Given I am Login As A
    Then I request "/api/user/1/have/1" using HTTP GET
    Then the response code is 200
    When I request "/api/user/1/have/1" using HTTP DELETE
    Then the response code is 204
    When I request "/api/user/1/have/1" using HTTP GET
    Then the response code is 404
    When I request "/api/user/1/equipment/1" using HTTP GET
    Then the response code is 200

Scenario: Cannot delete an have
    Given I am Login As B
    Then I request "/api/user/1/have/1" using HTTP GET
    Then the response code is 200
    When I request "/api/user/1/have/1" using HTTP DELETE
    Then the response code is 403
    When I request "/api/user/1/have/1" using HTTP GET
    Then the response code is 200
    When I request "/api/user/1/equipment/1" using HTTP GET
    Then the response code is 200