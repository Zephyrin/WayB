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
    Given there are User with the following details:
      | username | password | email     | gender |
      | a        | a        | a.b@c.com | MALE   |
      | b        | b        | b.b@c.com | MALE   |
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

  Scenario: Can get a collection of Equipment
    Given I request "/api/user/1/equipment" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    [
      {
        "id": 1,
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
          "extraFieldDefs": []
        }
      },
      {
        "id": 2,
        "name": "Men's Printed Cyclone Hoodie",
        "description": "Description 2",
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
    ]
    """

  Scenario: Can add a new Equipment using Id for subCategory and brand
    Given the request body is:
      """
      {
        "name": "Jacket",
        "description": "Titi",
        "extraFields": [ ],
        "subCategory": 2,
        "brand": 3
      }
      """
    When I request "/api/user/1/equipment" using HTTP POST
    Then the response code is 201
    And the response body contains JSON:
      """
      {
        "id": 3,
        "name": "Jacket",
        "description": "Titi",
        "extraFields": [ ],
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
        }
      }
      """
    When I request "/api/user/1/equipment/3" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 3,
      "name": "Jacket",
      "description": "Titi",
      "extraFields": [ ],
      "subCategory": {
        "id": 2,
        "name": "Jacket",
        "extraFieldDefs": []
      },
      "brand": {
        "id": 3,
        "name": "The north face",
        "uri": "www.thenorthface.com",
        "description": "The north face desc"
      }
    }
    """

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

  Scenario: Can update an existing Equipment - PUT
    Given the request body is:
      """
      {
        "name": "Jacket",
        "description": "Titi",
        "extraFields": [ ],
        "brand": 3,
        "subCategory": 2
      }
      """
    When I request "/api/user/1/equipment/1" using HTTP PUT
    Then the response code is 204
    When I request "/api/user/1/equipment/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
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

  Scenario: Can update an existing Equipment with a new Brand - PUT
    Given the request body is:
      """
      {
        "id": 1,
        "name": "Jacket",
        "description": "Titi",
        "extraFields": [ ],
        "brand": 2,
        "subCategory": 2
      }
      """
    When I request "/api/user/1/equipment/1" using HTTP PUT
    Then the response code is 204
    When I request "/api/user/1/equipment/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "name": "Jacket",
      "description": "Titi",
      "extraFields": [ ],
      "brand": {
        "id": 2,
        "name": "Mammut",
        "uri": "www.mammut.com",
        "description": "Mammut Desc"
      },
      "subCategory": {
        "id": 2,
        "name": "Jacket",
        "extraFieldDefs": [ ]
      }
    }
    """

  Scenario: Cannot update an existing Equipment with empty name - PUT
    Given the request body is:
      """
      {
        "id": 1,
        "name": "",
        "description": "Titi",
        "extraFields": [ ],
        "brand": 2,
        "subCategory": 2
      }
      """
    When I request "/api/user/1/equipment/1" using HTTP PUT
    Then the response code is 422
    And the response body contains JSON:
    """
    {
        "status": "error",
        "errors": [{
            "children": {
                "name": {
                    "errors": [
                        "This value should not be blank."
                    ]
                },
                "description": [],
                "extraFields": [],
                "brand": [],
                "subCategory": []
            }
        }]
    }
    """
    When I request "/api/category/1/subcategory/1/extrafielddef/2" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 2,
      "type": "NUMBER",
      "name": "Price",
      "isPrice": true,
      "isWeight": false,
      "linkTo": {
        "id": 1,
        "type": "ARRAY",
        "name": "Size",
        "isPrice": false,
        "isWeight": false
      }
    }
    """

  Scenario: Can update an existing Equipment - PATCH
    Given the request body is:
      """
      {
        "brand": 2
      }
      """
    When I request "/api/user/1/equipment/1" using HTTP PATCH
    Then the response code is 204
    When I request "/api/user/1/equipment/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "name": "Men's Zoomie Rain Jacket",
      "description": "Description 1",
      "extraFields": [ ],
      "brand": {
        "id": 2,
        "name": "Mammut",
        "uri": "www.mammut.com",
        "description": "Mammut Desc"
      },
      "subCategory": {
        "id": 2,
        "name": "Jacket",
        "extraFieldDefs": [ ]
      }
    }
    """

  Scenario: Cannot update an existing Equipment with empty name - PATCH
    Given the request body is:
      """
      {
        "name": ""
      }
      """
    When I request "/api/user/1/equipment/1" using HTTP PATCH
    Then the response code is 422
    And the response body contains JSON:
    """
    {
        "status": "error",
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
    When I request "/api/user/1/equipment/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
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
        "extraFieldDefs": []
      }
    }
    """

  Scenario: Can delete an ExtraFieldDef
    Given I request "/api/user/1/equipment/1" using HTTP GET
    Then the response code is 200
    When I request "/api/user/1/equipment/1" using HTTP DELETE
    Then the response code is 204
    When I request "/api/user/1/equipment/1" using HTTP GET
    Then the response code is 404
