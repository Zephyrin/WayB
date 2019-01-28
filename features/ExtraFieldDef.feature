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
    Given there are ExtraFieldDefs with the following details:
      | type | name   | isPrice | isWeight | subcategory | linkTo | category |
      | 1    | Size   | False   | False    | 1           |        | 1        |
      | 0    | Price  | true    | false    | 1           | 1      | 1        |
      | 0    | Weight | false   | true     | 1           | 1      | 1        |

  Scenario: Can get a single ExtraFieldDef
    Given I request "/api/category/1/subcategory/1/extrafielddef/1" using HTTP GET
    Then the response body contains JSON:
    """
    {
      "id": 1,
      "type": 1,
      "name": "Size",
      "isPrice": false,
      "isWeight": false
    }
    """
    And the response code is 200

  Scenario: Can get a collection of ExtraFieldDef
    Given I request "/api/category/1/subcategory/1/extrafielddef" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    [
      {
        "id": 1,
        "type": 1,
        "name": "Size",
        "isPrice": false,
        "isWeight": false
      },
       {
          "id": 2,
          "type": 0,
          "name": "Price",
          "isPrice": true,
          "isWeight": false,
          "linkTo": {
              "id": 1,
              "type": 1,
              "name": "Size",
              "isPrice": false,
              "isWeight": false
            }
        },
        {
          "id": 3,
          "type": 0,
          "name": "Weight",
          "isPrice": false,
          "isWeight": true,
          "linkTo": {
              "id": 1,
              "type": 1,
              "name": "Size",
              "isPrice": false,
              "isWeight": false
            }
        }
    ]
    """

  Scenario: Can get a collection of Categories and its Sub Categories and its ExtraFieldDef
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
          "name": "Pants",
          "extraFieldDefs": [
          {
            "id": 1,
            "type": 1,
            "name": "Size",
            "isPrice": false,
            "isWeight": false
          },
          {
            "id": 2,
            "type": 0,
            "name": "Price",
            "isPrice": true,
            "isWeight": false,
            "linkTo":
              {
                "id": 1,
                "type": 1,
                "name": "Size",
                "isPrice": false,
                "isWeight": false
              }
          },
          {
            "id": 3,
            "type": 0,
            "name": "Weight",
            "isPrice": false,
            "isWeight": true,
            "linkTo":
              {
                "id": 1,
                "type": 1,
                "name": "Size",
                "isPrice": false,
                "isWeight": false
              }
          }
          ]
        },
        {
          "id": 2,
          "name": "T-shirt",
          "extraFieldDefs": []
        }
      ]
    },
    {
      "id": 2,
      "name": "Sleeping",
      "subCategories": [
        {
          "id": 3,
          "name": "Sleeping Bag",
          "extraFieldDefs": []
        },
        {
          "id": 4,
          "name": "Mattress",
          "extraFieldDefs": []
        }
      ]
    },
    {
      "id": 3,
      "name": "Accessory",
      "subCategories": [
        {
        "id": 5,
        "name": "Flash Light",
        "extraFieldDefs": []
        }
      ]
    }]
    """

  Scenario: Can add a new ExtraFieldDef
    Given the request body is:
      """
      {
        "type": 1,
        "name": "New field",
        "isPrice": false,
        "isWeight": true,
        "linkTo": 3
      }
      """
    When I request "/api/category/1/subcategory/2/extrafielddef" using HTTP POST
    Then the response code is 201
    When I request "/api/category/1/subcategory/2/extrafielddef/4" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 4,
      "type": 1,
      "name": "New field",
      "isPrice": false,
      "isWeight": true,
      "linkTo":
      {
        "id": 3,
        "type": 0,
        "name": "Weight",
        "isPrice": false,
        "isWeight": true,
        "linkTo":
        {
          "id": 1,
          "type": 1,
          "name": "Size",
          "isPrice": false,
          "isWeight": false
        }
      }
    }
    """

  Scenario: Can update an existing ExtraFieldDef - PUT
    Given the request body is:
      """
      {
        "type": 1,
        "name": "Size 2",
        "isPrice": false,
        "isWeight": false
      }
      """
    When I request "/api/category/1/subcategory/1/extrafielddef/1" using HTTP PUT
    Then the response code is 204
    When I request "/api/category/1/subcategory/1/extrafielddef/1" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 1,
      "type": 1,
      "name": "Size 2",
      "isPrice": false,
      "isWeight": false
    }
    """

  Scenario: Can update an existing ExtraFieldDef with LinkTo - PUT
    Given the request body is:
      """
      {
        "type": 0,
        "name": "Price 2",
        "isPrice": true,
        "isWeight": false,
        "linkTo": 3
      }
      """
    When I request "/api/category/1/subcategory/1/extrafielddef/2" using HTTP PUT
    Then the response code is 204
    When I request "/api/category/1/subcategory/1/extrafielddef/2" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 2,
      "type": 0,
      "name": "Price 2",
      "isPrice": true,
      "isWeight": false,
      "linkTo":
      {
        "id": 3,
        "type": 0,
        "name": "Weight",
        "isPrice": false,
        "isWeight": true,
        "linkTo": {
          "id": 1,
          "type": 1,
          "name": "Size",
          "isPrice": false,
          "isWeight": false
        }
      }
    }
    """

  Scenario: Cannot update an existing ExtraFieldDef with empty name - PUT
    Given the request body is:
      """
      {
        "type": 1,
        "name": "",
        "isPrice": false,
        "isWeight": false,
        "linkTo": 1
      }
      """
    When I request "/api/category/1/subcategory/1/extrafielddef/2" using HTTP PUT
    Then the response code is 422
    And the response body contains JSON:
    """
    {
        "status": "error",
        "errors": [{
            "children": {
                "type": [],
                "name": {
                    "errors": [
                        "This value should not be blank."
                    ]
                },
                "isPrice": [],
                "isWeight": [],
                "linkTo": []
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
      "type": 0,
      "name": "Price",
      "isPrice": true,
      "isWeight": false,
      "linkTo": {
        "id": 1,
        "type": 1,
        "name": "Size",
        "isPrice": false,
        "isWeight": false
      }
    }
    """

  Scenario: Can update an existing ExtraFieldDef - PATCH
    Given the request body is:
      """
      {
        "name": "Price 2"
      }
      """
    When I request "/api/category/1/subcategory/1/extrafielddef/2" using HTTP PATCH
    Then the response code is 204
    When I request "/api/category/1/subcategory/1/extrafielddef/2" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 2,
      "type": 0,
      "name": "Price 2",
      "isPrice": true,
      "isWeight": false,
      "linkTo":
      {
        "id": 1,
        "type": 1,
        "name": "Size",
        "isPrice": false,
        "isWeight": false
      }
    }
    """

  Scenario: Cannot update an existing SubCategory with empty name - PATCH
    Given the request body is:
      """
      {
        "name": ""
      }
      """
    When I request "/api/category/1/subcategory/1/extrafielddef/2" using HTTP PATCH
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
    When I request "/api/category/1/subcategory/1/extrafielddef/2" using HTTP GET
    Then the response code is 200
    And the response body contains JSON:
    """
    {
      "id": 2,
      "type": 0,
      "name": "Price",
      "isPrice": true,
      "isWeight": false,
      "linkTo":
      {
        "id": 1,
        "type": 1,
        "name": "Size",
        "isPrice": false,
        "isWeight": false
      }
    }
    """

  Scenario: Can delete an ExtraFieldDef
    Given I request "/api/category/1/subcategory/1/extrafielddef/1" using HTTP GET
    Then the response code is 200
    When I request "/api/category/1/subcategory/1/extrafielddef/1" using HTTP DELETE
    Then the response code is 204
    When I request "/api/category/1/subcategory/1/extrafielddef/1" using HTTP GET
    Then the response code is 404

  Scenario: Must have a non-blank name
    Given the request body is:
    """
    {
      "id": 1,
      "type": 1,
      "name": "",
      "isPrice": false,
      "isWeight": false
    }
    """
    When I request "/api/category/1/subcategory/2/extrafielddef" using HTTP POST
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

  Scenario: Can delete a Category and its Sub Category and its ExtraFieldDef
    Given I request "/api/category/1" using HTTP DELETE
    Then the response code is 204
    When I request "/api/category/1/subcategory/1" using HTTP GET
    Then the response code is 404
    When I request "/api/category/1/subcategory/1/extrafielddef/1" using HTTP GET
    Then the response code is 404
