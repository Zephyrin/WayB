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
    Given there are ExtraField with the following details:
      | type   | value | name   | isPrice | isWeight | equipment |
      | ARRAY  | M     | Size   | false   | false    | 1         |
      | NUMBER | 12    | Price  | true    | false    | 1         |
      | NUMBER | 500   | Weight | false   | true     | 1         |

  Scenario: Can get a single ExtraField
    Given I am Login As B
    Then I request "/api/equipment/1/extrafield/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "type": "ARRAY",
      "name": "Size",
      "isPrice": false,
      "isWeight": false,
      "value": "M"
    }
    """

  Scenario: Can get a collection of ExtraField
    Given I am Login As B
    Then I request "/api/equipment/1/extrafield" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    [
      {
        "id": 1,
        "type": "ARRAY",
        "name": "Size",
        "isPrice": false,
        "isWeight": false,
        "value": "M"
      },
      {
        "id": 2,
        "type": "NUMBER",
        "name": "Price",
        "isPrice": true,
        "isWeight": false,
        "value": "12"
      },
      {
        "id": 3,
        "type": "NUMBER",
        "name": "Weight",
        "isPrice": false,
        "isWeight": true,
        "value": "500"
      }
    ]
    """

  Scenario: Can get a collection of Equipment and its ExtraFields
    Given I am Login As A
    Then I request "/api/equipment" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    [
      {
        "id": 1,
        "name": "Men's Zoomie Rain Jacket",
        "description": "Description 1",
        "extraFields": [
            {
              "id": 1,
              "type": "ARRAY",
              "name": "Size",
              "isPrice": false,
              "isWeight": false,
              "value": "M"
            },
            {
              "id": 2,
              "type": "NUMBER",
              "name": "Price",
              "isPrice": true,
              "isWeight": false,
              "value": "12"
            },
            {
              "id": 3,
              "type": "NUMBER",
              "name": "Weight",
              "isPrice": false,
              "isWeight": true,
              "value": "500"
            }
          ],
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

  Scenario: Can add a new ExtraField
    Given I am Login As B
    Then the request body is:
      """
      {
        "type": "ARRAY",
        "name": "New field",
        "isPrice": false,
        "isWeight": true,
        "value": "7"
      }
      """
    When I request "/api/equipment/1/extrafield" using HTTP POST
    Then the response code is 201
    And the response body contains JSON:
    """
    {
        "id": 4,
        "type": "ARRAY",
        "name": "New field",
        "isPrice": false,
        "isWeight": true,
        "value": "7"
    }
    """

  Scenario: Cannot add a new ExtraField on unknown Equipment
    Given I am Login As B
    Then the request body is:
      """
      {
        "type": "ARRAY",
        "name": "New field",
        "isPrice": false,
        "isWeight": true,
        "value": "7"
      }
      """
    When I request "/api/equipment/3/extrafield" using HTTP POST
    Then the response code is 404

  Scenario: Can update an existing ExtraField - PUT
    Given I am Login As B
    Then the request body is:
      """
      {
        "type": "ARRAY",
        "name": "Size 2",
        "isPrice": false,
        "isWeight": false,
        "value": "S"
      }
      """
    When I request "/api/equipment/1/extrafield/1" using HTTP PUT
    Then the response code is 204
    When I request "/api/equipment/1/extrafield/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "type": "ARRAY",
      "name": "Size 2",
      "isPrice": false,
      "isWeight": false,
      "value": "S"
    }
    """

  Scenario: Cannot update an existing ExtraField with empty name - PUT
    Given I am Login As B
    Then the request body is:
      """
      {
        "type": "ARRAY",
        "name": "",
        "isPrice": false,
        "isWeight": false,
        "value": "S"
      }
      """
    When I request "/api/equipment/1/extrafield/1" using HTTP PUT
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
                "isPrice": [],
                "isWeight": [],
                "value": []
            }
        }]
    }
    """
    When I request "/api/equipment/1/extrafield/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "type": "ARRAY",
      "name": "Size",
      "isPrice": false,
      "isWeight": false,
      "value": "M"
    }
    """

  Scenario: Can update an existing ExtraField - PATCH
    Given I am Login As B
    Then the request body is:
      """
      {
        "name": "Video"
      }
      """
    When I request "/api/equipment/1/extrafield/1" using HTTP PATCH
    Then the response code is 204
    When I request "/api/equipment/1/extrafield/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "type": "ARRAY",
      "name": "Video",
      "isPrice": false,
      "isWeight": false,
      "value": "M"
    }
    """

  Scenario: Cannot update an existing ExtraField with empty name - PATCH
    Given I am Login As B
    Then the request body is:
      """
      {
        "name": ""
      }
      """
    When I request "/api/equipment/1/extrafield/1" using HTTP PATCH
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
    When I request "/api/equipment/1/extrafield/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "type": "ARRAY",
      "name": "Size",
      "isPrice": false,
      "isWeight": false,
      "value": "M"
    }
    """

  Scenario: Can delete an ExtraField
    Given I am Login As B
    Then I request "/api/equipment/1/extrafield/1" using HTTP GET
    Then the response code is 200
    When I request "/api/equipment/1/extrafield/1" using HTTP DELETE
    Then the response code is 204
    When I request "/api/equipment/1/extrafield/1" using HTTP GET
    Then the response code is 404

  Scenario: Can delete an Equipment and its ExtraField
    Given I am Login As A
    Then I request "/api/equipment/1" using HTTP DELETE
    Then the response code is 204
    When I request "/api/equipment/1/extrafield/1" using HTTP GET
    Then the response code is 404

  Scenario: Can add an Equipment and all its ExtraFields
    Given I am Login As B
    Then the request body is:
    """
    {
      "name": "Jacket",
      "description": "Titi",
      "extraFields": [
        {
          "type": "ARRAY",
          "name": "Size",
          "isPrice": false,
          "isWeight": false,
          "value": "S"
        },
        {
          "type": "NUMBER",
          "name": "Price",
          "isPrice": true,
          "isWeight": false,
          "value": "120"
        },
        {
          "type": "NUMBER",
          "name": "Weight",
          "isPrice": false,
          "isWeight": true,
          "value": "250"
        }
      ],
      "subCategory": 2,
      "brand": 3
    }
    """
    When I request "/api/equipment" using HTTP POST
    Then the response code is 201
    And the response body contains JSON:
    """
    {
      "id": 3,
      "name": "Jacket",
      "description": "Titi",
      "extraFields": [
        {
          "id": 4,
          "type": "ARRAY",
          "name": "Size",
          "isPrice": false,
          "isWeight": false,
          "value": "S"
        },
        {
          "id": 5,
          "type": "NUMBER",
          "name": "Price",
          "isPrice": true,
          "isWeight": false,
          "value": "120"
        },
        {
          "id": 6,
          "type": "NUMBER",
          "name": "Weight",
          "isPrice": false,
          "isWeight": true,
          "value": "250"
        }
      ],
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
