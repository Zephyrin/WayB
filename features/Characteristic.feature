Feature: Provide a consistent standard JSON API endpoint

  In order to build interchangeable front ends
  As a JSON API developer
  I need to allow Create, Read, Update, and Delete functionality

  Background:
    Given there are default users
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
    Given there are Brands with the following details:
      | name           | description         | uri                  |
      | MSR            | MSR Desc            | www.msr.com          |
      | Mammut         | Mammut Desc         | www.mammut.com       |
      | The north face | The north face desc | www.thenorthface.com |
    Given there are Equipments with the following details:
      | name                         | description   | brand | subCategory |
      | Men's Zoomie Rain Jacket     | Description 1 | 3     | 2           |
      | Men's Printed Cyclone Hoodie | Description 2 | 3     | 2           |
    Given there are Characteristic with the following details:
      | size | price | weight | gender | equipment |
      | M    | 300   | 400    | MALE   | 1         |
      | 12   | 350   | 350    | MALE   | 1         |
      | 500  | 440   | 700    | MALE   | 1         |

  Scenario: Can get a single Characteristic
    Given I am login as user
    Then I request "/api/equipment/1/characteristic/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "size": "M",
      "price": 300,
      "weight": 400,
      "gender": "MALE"
    }
    """

  Scenario: Can get a collection of Characteristic
    Given I am login as user
    Then I request "/api/equipment/1/characteristic" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    [
      {
        "id": 1,
        "size": "M",
        "price": 300,
        "weight": 400,
        "gender": "MALE"
      },
      {
        "id": 2,
        "size": "12",
        "price": 350,
        "weight": 350,
        "gender": "MALE"
      },
      {
        "id": 3,
        "size": "500",
        "price": 440,
        "weight": 700,
        "gender": "MALE"
      }
    ]
    """

  Scenario: Can get a collection of Equipment and its Characteristics
    Given I am login as admin
    Then I request "/api/equipment" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    [
      {
        "id": 1,
        "name": "Men's Zoomie Rain Jacket",
        "description": "Description 1",
        "characteristics": [
            {
              "id": 1,
              "size": "M",
              "price": 300,
              "weight": 400,
              "gender": "MALE"
            },
            {
              "id": 2,
              "size": "12",
              "price": 350,
              "weight": 350,
              "gender": "MALE"
            },
            {
              "id": 3,
              "size": "500",
              "price": 440,
              "weight": 700,
              "gender": "MALE"
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
          "name": "Jacket"
        }
      },
      {
        "id": 2,
        "name": "Men's Printed Cyclone Hoodie",
        "description": "Description 2",
        "characteristics": [ ],
        "brand": {
          "id": 3,
          "name": "The north face",
          "uri": "www.thenorthface.com",
          "description": "The north face desc"
        },
        "subCategory": {
          "id": 2,
          "name": "Jacket"
        }
      }
    ]
    """

  Scenario: Can add a new Characteristic
    Given I am login as user
    Then the request body is:
      """
      {
        "size": "L",
        "price": 500,
        "weight": 400,
        "gender": "FEMALE"
      }
      """
    When I request "/api/equipment/1/characteristic" using HTTP POST
    Then the response code is 201
    And the response body contains JSON:
    """
    {
        "id": 4,
        "size": "L",
        "price": 500,
        "weight": 400,
        "gender": "FEMALE"
    }
    """

  Scenario: Cannot add a new Characteristic on unknown Equipment
    Given I am login as user
    Then the request body is:
      """
      {
        "size": "L",
        "price": 500,
        "weight": 400,
        "gender": "FEMALE"
      }
      """
    When I request "/api/equipment/3/characteristic" using HTTP POST
    Then the response code is 404

  Scenario: Can update an existing Characteristic - PUT
    Given I am login as user
    Then the request body is:
      """
      {
        "size": "L",
        "price": 500,
        "weight": 400,
        "gender": "FEMALE"
      }
      """
    When I request "/api/equipment/1/characteristic/1" using HTTP PUT
    Then the response code is 204
    When I request "/api/equipment/1/characteristic/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "size": "L",
      "price": 500,
      "weight": 400,
      "gender": "FEMALE"
    }
    """

  Scenario: Can update an existing Characteristic - PATCH
    Given I am login as user
    Then the request body is:
      """
      {
        "size": "V"
      }
      """
    When I request "/api/equipment/1/characteristic/1" using HTTP PATCH
    Then the response code is 204
    When I request "/api/equipment/1/characteristic/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "size": "V",
      "price": 300,
      "weight": 400,
      "gender": "FEMALE"
    }
    """

  Scenario: Can delete an Characteristic
    Given I am login as user
    Then I request "/api/equipment/1/characteristic/1" using HTTP GET
    Then the response code is 200
    When I request "/api/equipment/1/characteristic/1" using HTTP DELETE
    Then the response code is 204
    When I request "/api/equipment/1/characteristic/1" using HTTP GET
    Then the response code is 404

  Scenario: Can delete an Equipment and its Characteristic
    Given I am login as admin
    Then I request "/api/equipment/1" using HTTP DELETE
    Then the response code is 204
    When I request "/api/equipment/1/characteristic/1" using HTTP GET
    Then the response code is 404

  Scenario: Can add an Equipment and all its Characteristics
    Given I am login as user
    Then the request body is:
    """
    {
      "name": "Jacket",
      "description": "Titi",
      "characteristics": [
        {
          "size": "L",
          "price": 500,
          "weight": 400,
          "gender": "FEMALE"
        },
        {
          "size": "M",
          "price": 600,
          "weight": 400,
          "gender": "FEMALE"
        },
        {
          "size": "C",
          "price": 400,
          "weight": 400,
          "gender": "FEMALE"
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
      "characteristics": [
        {
          "id": 4,
          "size": "L",
          "price": 500,
          "weight": 400,
          "gender": "FEMALE"
        },
        {
          "id": 5,
          "size": "M",
          "price": 600,
          "weight": 400,
          "gender": "FEMALE"
        },
        {
          "size": "C",
          "price": 400,
          "weight": 400,
          "gender": "FEMALE"
        }
      ],
      "subCategory": {
        "id": 2,
        "name": "Jacket"
      },
      "brand": {
          "id": 3,
          "name": "The north face",
          "description": "The north face desc",
          "uri": "www.thenorthface.com"
      }
    }
    """
