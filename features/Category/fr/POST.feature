Feature: Test POST Category with french result.

    Scenario:
        Given there are default users
        Given there are objects to post to "/api/en/category" with the following details:
        | #name  | 
        | en: Clothes, fr: Vétements |
        | en: Bags,  fr: Sacs |

    Scenario: Can get a single category if I am connected as user - GET
        Given I am login as user
        Then I request "/api/fr/category/1" using HTTP GET
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
        Then I request "/api/fr/category" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
            "status": "Erreur.",
            "message": "Erreur de validation.",
            "errors": "Le body de la requête ne contient pas de JSon valide."
        }
        """
    
    Scenario: Cannot post with invalid field
        Given there are default users
        Given I am login as user
        Then the request body is:
        """
        { "invalid" : "invalid" }
        """
        Then I request "/api/fr/category" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {                                                                                                                                                      
            "status": "Erreur.",                                                                                                                                   
            "message": "Erreur de validation.",                                                                                                                       
            "errors": [
                {
                "errors": [
                    "Ce formulaire ne doit pas contenir des champs supplémentaires."
                ],
                "children": {
                        "name": {
                            "errors": [
                                "Cette valeur ne doit pas être nulle."
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