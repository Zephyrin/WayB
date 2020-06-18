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

  Scenario: Can get a single Equipment
    Given I am login as admin
    Then I request "/api/equipment/1" using HTTP GET
    Then print last response
    Then the response body contains JSON:
    """
    {
      "id": 1,
      "name": "Men's Zoomie Rain Jacket",
      "description": "Description 1",
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
    """
    And the response code is 200
    Then I am login as user
    Then I request "/api/equipment/1" using HTTP GET
    Then the response body contains JSON:
    """
    {
      "id": 1,
      "name": "Men's Zoomie Rain Jacket",
      "description": "Description 1",
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
    """
    And the response code is 200

  Scenario: Can get a collection of Equipment
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
        "characteristics": [ ],
        "brand": {
          "id": 3,
          "name": "The north face",
          "uri": "www.thenorthface.com",
          "description": "The north face desc"
        },
        "subCategory": {
          "id": 2,
          "name": "Jacket",
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
    Then I am login as user
    Then I request "/api/equipment" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    [
    ]
    """

  Scenario: Can add a new Equipment using Id for subCategory and brand
    Given I am login as admin
    Then the request body is:
      """
      {
        "name": "Jacket",
        "description": "Titi",
        "characteristics": [ ],
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
        "characteristics": [ ],
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
    When I request "/api/equipment/3" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 3,
      "name": "Jacket",
      "description": "Titi",
      "characteristics": [ ],
      "subCategory": {
        "id": 2,
        "name": "Jacket"
      },
      "brand": {
        "id": 3,
        "name": "The north face",
        "uri": "www.thenorthface.com",
        "description": "The north face desc"
      }
    }
    """
    Then I request "/api/equipment" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    [
      {
        "id": 3,
        "name": "Jacket",
        "description": "Titi",
        "characteristics": [ ],
        "subCategory": {
          "id": 2,
          "name": "Jacket"
        },
        "brand": {
          "id": 3,
          "name": "The north face",
          "uri": "www.thenorthface.com",
          "description": "The north face desc"
        }
      }
    ]
    """
    Then I am login as admin
    Then I request "/api/equipment" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    [
      {
        "id": 1,
        "name": "Men's Zoomie Rain Jacket",
        "description": "Description 1",
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
      },
      {
        "id": 3,
        "name": "Jacket",
        "description": "Titi",
        "characteristics": [ ],
        "subCategory": {
          "id": 2,
          "name": "Jacket"
        },
        "brand": {
          "id": 3,
          "name": "The north face",
          "uri": "www.thenorthface.com",
          "description": "The north face desc"
        }
      }
    ]
    """

  Scenario: Cannot add a new Equipment using Entity for subCategory and brand
    Given I am login as admin
    Then the request body is:
      """
      {
        "name": "Jacket",
        "description": "Titi",
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
      """
    When I request "/api/equipment" using HTTP POST
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
            "characteristics": []
          }
        }]
      }
      """
    When I request "/api/equipment/3" using HTTP GET
    Then the response code is 404

  Scenario: Cannot update an existing Equipment if it is not mine - PUT
    Given I am login as user
    Then the request body is:
      """
      {
        "name": "Jacket",
        "description": "Titi",
        "characteristics": [ ],
        "brand": 3,
        "subCategory": 2
      }
      """
    When I request "/api/equipment/1" using HTTP PUT
    Then the response code is 403
    When I request "/api/equipment/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "name": "Men's Zoomie Rain Jacket",
      "description": "Description 1",
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
    """

    Scenario: Can update an existing Equipment - PUT
    Given I am login as admin
    Then the request body is:
      """
      {
        "name": "Jacket",
        "description": "Titi",
        "characteristics": [ ],
        "brand": 3,
        "subCategory": 2
      }
      """
    When I request "/api/equipment/1" using HTTP PUT
    Then the response code is 204
    When I request "/api/equipment/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "name": "Jacket",
      "description": "Titi",
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
    """

  Scenario: Can update an existing Equipment using Id for subCategory and brand as AMBASSADOR
    Given I am login as admin
    Then the request body is:
      """
      {
        "name": "Jacket",
        "description": "Titi",
        "characteristics": [ ],
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
        "characteristics": [ ],
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
    Then I am login as admin
    Then the request body is:
      """
      {
        "id": 3,
        "name": "Jacket Update",
        "description": "Titi",
        "characteristics": [ ],
        "brand": 2,
        "subCategory": 2
      }
      """
    When I request "/api/equipment/3" using HTTP PUT
    Then the response code is 204
    When I request "/api/equipment/3" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 3,
      "name": "Jacket Update",
      "description": "Titi",
      "characteristics": [ ],
      "brand": {
        "id": 2,
        "name": "Mammut",
        "uri": "www.mammut.com",
        "description": "Mammut Desc"
      },
      "subCategory": {
        "id": 2,
        "name": "Jacket"
      }
    }
    """

  Scenario: Can update an existing Equipment with a new Brand - PUT
    Given I am login as admin
    Then the request body is:
      """
      {
        "id": 1,
        "name": "Jacket",
        "description": "Titi",
        "characteristics": [ ],
        "brand": 2,
        "subCategory": 2
      }
      """
    When I request "/api/equipment/1" using HTTP PUT
    Then the response code is 204
    When I request "/api/equipment/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "name": "Jacket",
      "description": "Titi",
      "characteristics": [ ],
      "brand": {
        "id": 2,
        "name": "Mammut",
        "uri": "www.mammut.com",
        "description": "Mammut Desc"
      },
      "subCategory": {
        "id": 2,
        "name": "Jacket"
      }
    }
    """

  Scenario: Cannot update an existing Equipment with empty name - PUT
    Given I am login as admin
    Then the request body is:
      """
      {
        "id": 1,
        "name": "",
        "description": "Titi",
        "characteristics": [ ],
        "brand": 2,
        "subCategory": 2
      }
      """
    When I request "/api/equipment/1" using HTTP PUT
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
                "characteristics": [],
                "brand": [],
                "subCategory": []
            }
        }]
    }
    """

  Scenario: Can update an existing Equipment - PATCH
    Given I am login as admin
    Then the request body is:
      """
      {
        "brand": 2
      }
      """
    When I request "/api/equipment/1" using HTTP PATCH
    Then the response code is 204
    When I request "/api/equipment/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "name": "Men's Zoomie Rain Jacket",
      "description": "Description 1",
      "characteristics": [ ],
      "brand": {
        "id": 2,
        "name": "Mammut",
        "uri": "www.mammut.com",
        "description": "Mammut Desc"
      },
      "subCategory": {
        "id": 2,
        "name": "Jacket"
      }
    }
    """

  Scenario: Cannot update an existing Equipment that is not mine - PATCH
    Given I am login as user
    Then the request body is:
      """
      {
        "brand": 2
      }
      """
    When I request "/api/equipment/1" using HTTP PATCH
    Then the response code is 403
    When I request "/api/equipment/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "name": "Men's Zoomie Rain Jacket",
      "description": "Description 1",
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
    """

  Scenario: Cannot update an existing Equipment with empty name - PATCH
    Given I am login as admin
    Then the request body is:
      """
      {
        "name": ""
      }
      """
    When I request "/api/equipment/1" using HTTP PATCH
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
    When I request "/api/equipment/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "name": "Men's Zoomie Rain Jacket",
      "description": "Description 1",
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
    """

  Scenario: Can delete an Equipment
    Given I am login as admin
    Then I request "/api/equipment/1" using HTTP GET
    Then the response code is 200
    When I request "/api/equipment/1" using HTTP DELETE
    Then the response code is 204
    When I request "/api/equipment/1" using HTTP GET
    Then the response code is 404

  Scenario: Cannot delete an Equipment that don't belong to the user
    Given I am login as user
    Then I request "/api/equipment/1" using HTTP GET
    Then the response code is 200
    When I request "/api/equipment/1" using HTTP DELETE
    Then the response code is 403
    When I request "/api/equipment/1" using HTTP GET
    Then the response code is 200