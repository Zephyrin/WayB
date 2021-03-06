Feature: Test POST Category with english result.

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
            "id": 1
        }
        """

    Scenario: Cannot post an empty array
        Given there are default users
        Given I am login as user
        Then the request body is:
        """
        """
        Then I request "/api/en/category" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
            "status": "Error.",
            "message": "Validation error.",
            "errors": "The body of the request does not contain a valid JSon."
        }
        """
    
    Scenario: Cannot post with invalid field
        Given there are default users
        Given I am login as user
        Then the request body is:
        """
        { "invalid" : "invalid" }
        """
        Then I request "/api/en/category" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {                                                                                                                                                      
            "status": "Error.",                                                                                                                                   
            "message": "Validation error.",                                                                                                                       
            "errors": [
                {
                "errors": [
                    "This form should not contain extra fields."
                ],
                "children": {
                        "name": {
                            "errors": [
                                "This value should not be null."
                            ]
                        },
                        "subCategories": [],
                        "validate": [],
                        "askValidate": []
                    }
                }
            ]
        }
        """