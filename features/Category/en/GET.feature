Feature: Test GET Category with english result.

    Scenario:
        Given there are default users
        Given there are objects to post to "/api/en/category" with the following details:
        | #name  | 
        | en: Clothes, fr: Vétements |
        | en: Bags,  fr: Sacs |

    Scenario: Can get a single category if I am connected as user - GET
        Given I am login as user
        Then I request "/api/en/category/1" using HTTP GET
        Then the response code is 200
        And the response body contains JSON:
        """
        {
            "id": 1,
            "name": "Clothes",
            "askValidate": false,
            "validate": false,
            "subCategories": [],
            "createdBy": {
                    "id": "@regExp(/[0-9]+/)"
                },
            "translations": { "en": { "name": "Clothes" }, "fr": { "name": "Vétements" } }
        }
        """
        And the response body has 7 fields
        Given I request "/api/fr/category/1" using HTTP GET
        Then the response code is 200
        And the response body contains JSON:
        """
        {
            "id": 1,
            "name": "Vétements",
            "askValidate": false,
            "validate": false,
            "subCategories": [],
            "createdBy": {
                    "id": "@regExp(/[0-9]+/)"
                },
            "translations": { "en": { "name": "Clothes" }, "fr": { "name": "Vétements" } }
        }
        """
        And the response body has 7 fields
        Given I request "/api/fr/categories" using HTTP GET
        Then the response code is 200
        And the response body is a JSON array of length 0
        Given I am login as admin
        Then I request "/api/fr/categories" using HTTP GET
        Then the response code is 200
        And the response body is a JSON array of length 2
        And the response body contains JSON:
        """
        [
            { "id": 1 },
            { "id": 2 }
        ]
        """
