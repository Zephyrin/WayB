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
      | T-shirt      | 1         |
      | Sleeping Bag | 2         |
      | Mattress     | 2         |
      | Flash Light  | 3         |

  Scenario: Can get a single SubCategory
    Given I request "/api/category/1/subcategory/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "name": "Pants"
    }
    """

  Scenario: Can get a collection of SubCategories
    Given I request "/api/category/1/subcategory" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    [
      {
        "id": 1,
        "name": "Pants"
      },
      {
        "id": 2,
        "name": "T-shirt"
      }
    ]
    """

  Scenario: Can get a collection of Categories and its Sub Categories
    Given I request "/api/category" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    [{
      "id": 1,
      "name": "Clothe",
      "subCategories": [
        {
          "id": 1,
          "name": "Pants"
        },
        {
          "id": 2,
          "name": "T-shirt"
        }
      ]
    },
    {
      "id": 2,
      "name": "Sleeping",
      "subCategories": [
        {
          "id": 3,
          "name": "Sleeping Bag"
        },
        {
          "id": 4,
          "name": "Mattress"
        }
      ]
    },
    {
      "id": 3,
      "name": "Accessory",
      "subCategories": [
        {
        "id": 5,
        "name": "Flash Light"
        }
      ]
    }]
    """

  Scenario: Can add a new SubCategory
    Given the request body is:
      """
      {
        "name": "Camera"
      }
      """
    When I request "/api/category/3/subcategory" using HTTP POST
    Then the response code is 201
    And the response body contains JSON:
    """
    {
      "id": 6,
      "name": "Camera",
      "extraFieldDefs": [ ]
    }
    """

  Scenario: Can update an existing SubCategory - PUT
    Given the request body is:
      """
      {
        "name": "Video Camera"
      }
      """
    When I request "/api/category/3/subcategory/5" using HTTP PUT
    Then the response code is 204
    When I request "/api/category/3/subcategory/5" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 5,
      "name": "Video Camera"
    }
    """

  Scenario: Cannot update an existing SubCategory with empty name - PUT
    Given the request body is:
      """
      {
        "name": ""
      }
      """
    When I request "/api/category/3/subcategory/5" using HTTP PUT
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
    When I request "/api/category/3/subcategory/5" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 5,
      "name": "Flash Light"
    }
    """

  Scenario: Can update an existing SubCategory - PATCH
    Given the request body is:
      """
      {
        "name": "Video"
      }
      """
    When I request "/api/category/3/subcategory/5" using HTTP PATCH
    Then the response code is 204
    When I request "/api/category/3/subcategory/5" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 5,
      "name": "Video"
    }
    """

  Scenario: Cannot update an existing SubCategory with empty name - PATCH
    Given the request body is:
      """
      {
        "name": ""
      }
      """
    When I request "/api/category/3/subcategory/5" using HTTP PATCH
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
    When I request "/api/category/3/subcategory/5" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 5,
      "name": "Flash Light"
    }
    """

  Scenario: Can delete a Sub Category
    Given I request "/api/category/3/subcategory/5" using HTTP GET
    Then the response code is 200
    When I request "/api/category/3/subcategory/5" using HTTP DELETE
    Then the response code is 204
    When I request "/api/category/3/subcategory/5" using HTTP GET
    Then the response code is 404

  Scenario: Must have a non-blank name
    Given the request body is:
      """
      {
        "name": ""
      }
      """
    When I request "/api/category/2/subcategory" using HTTP POST
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

  Scenario: Can delete a Category and its Sub Category
    Given I request "/api/category/3" using HTTP DELETE
    Then the response code is 204
    When I request "/api/category/3/subcategory/5" using HTTP GET
    Then the response code is 404

  Scenario: Can update a Category without its Sub Categories - PUT
    Given the request body is:
      """
      {
        "name": "Cooking"
      }
      """
    When I request "/api/category/1" using HTTP PUT
    Then the response code is 204
    When I request "/api/category/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "name": "Cooking",
      "subCategories": []
    }
    """

  Scenario: Can add a Category and all its SubCategories
    Given the request body is:
    """
    {
      "name": "Test category",
      "subCategories": [
        {
          "name": "Test sub category"
        },
        {
          "name": "Test sub category 2"
        }
      ]
    }
    """
    When I request "/api/category" using HTTP POST
    Then the response code is 201
    And the response body contains JSON:
    """
    {
      "id": 4,
      "name": "Test category",
      "subCategories": [
        {
          "id": 6,
          "name": "Test sub category"
        },
        {
          "id": 7,
          "name": "Test sub category 2"
        }
      ]
    }
    """

  Scenario: Can update a Category and all its SubCategory using PUT
    Given the request body is:
    """
    {
      "name": "Clothe 2",
      "subCategories": [
        {
          "id": 1,
          "name": "Pants 2"
        },
        {
          "id": 2,
          "name": "T-Shirt"
        },
        {
          "name": "New Type"
        }
      ]
    }
    """
    When I request "/api/category/1" using HTTP PUT
    Then the response code is 204
    When I request "/api/category/1" using HTTP GET
    Then the response body contains JSON:
    """
    {
      "id": 1,
      "name": "Clothe 2",
      "subCategories": [
        {
          "id": 1,
          "name": "Pants 2",
          "extraFieldDefs": [

          ]
        },
        {
          "id": 2,
          "name": "T-Shirt",
          "extraFieldDefs": [

          ]
        },
        {
          "id": 6,
          "name": "New Type",
          "extraFieldDefs": [ ]
        }
      ]
    }
    """

  Scenario: Can update a Category and one of its SubCategory using PATCH
    Given the request body is:
    """
    {
      "subCategories": [
        {
          "id": 1,
          "name": "Pants 2"
        },
        {
          "name": "New Type"
        }
      ]
    }
    """
    When I request "/api/category/1" using HTTP PATCH
    Then the response code is 204
    When I request "/api/category/1" using HTTP GET
    Then the response body contains JSON:
    """
    {
      "id": 1,
      "name": "Clothe",
      "subCategories": [
        {
          "id": 1,
          "name": "Pants 2",
          "extraFieldDefs": [ ]
        },
        {
          "id": 2,
          "name": "New Type",
          "extraFieldDefs": [ ]
        }
      ]
    }
    """

  Scenario: Can update a Category without its SubCategory using PATCH
    Given the request body is:
    """
    {
      "name": "Clothe 2"
    }
    """
    When I request "/api/category/1" using HTTP PATCH
    Then the response code is 204
    When I request "/api/category/1" using HTTP GET
    Then the response body contains JSON:
    """
    {
      "id": 1,
      "name": "Clothe 2",
      "subCategories": [
        {
          "id": 1,
          "name": "Pants",
          "extraFieldDefs": [

          ]
        },
        {
          "id": 2,
          "name": "T-shirt",
          "extraFieldDefs": [ ]
        }
      ]
    }
    """