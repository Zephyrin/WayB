Feature: Test PUT Category with french result.

    Scenario:
        Given there are default users
        Given there are objects to post to "/api/en/category" with the following details:
        | #name  | 
        | en: Clothes, fr: Vétements |
        | en: Bags,  fr: Sacs |

    Scenario: Put the first category
        Given I am login as admin
        And the request body is:
            """
            {
                "name": { "en": "Clothe", "fr": "Vétement" },
                "askValidate": false,
                "validate": false,
                "subCategories": []
            }
            """
        When I request "/api/en/category/1" using HTTP PUT
        Then the response code is 204
        Given I request "/api/en/category/1" using HTTP GET
        Then the response code is 200
        And the response body contains JSON:
        """
        {
            "name": "Clothe",
            "translations": { "en": { "name": "Clothe" }, "fr": { "name": "Vétement" } }
        }
        """
