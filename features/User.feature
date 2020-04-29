Feature: Provide a consistent standard JSON API endpoint for USER

  In order to build interchangeable front ends
  As a JSON API developer
  I need to allow Create, Read, Update, and Delete functionality

  Background:
    Given there are User with the following details:
      | username | password | email     | gender | ROLE            |
      | a        | a        | a.b@c.com | MALE   | ROLE_AMBASSADOR |
      | b        | b        | b.b@c.com | MALE   | ROLE_USER       |

    Scenario: Cannot register a new user with the same name
      Given the request body is:
        """
        {
          "username": "a",
          "email": "a.b@c.com",
          "gender": "MALE",
          "password": "a"
        }
        """
        When I request "/api/auth/register" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
          "status":"error",
          "Message":"Validation error",
          "errors":[{
            "children":{
              "username":{"errors":["This value is already used."]},
              "password":[],
              "email":{"errors":["This value is already used."]},
              "gender":[]
            }
          }]
        }
        """

    Scenario: Cannot register a new user without name
      Given the request body is:
        """
        {
          "username": "",
          "email": "",
          "gender": "MALE",
          "password": "a"
        }
        """
        When I request "/api/auth/register" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
          "status":"error",
          "Message":"Validation error",
          "errors":[{
            "children":{
              "username":{"errors":["This value should not be blank."]},
              "password":[],
              "email":{"errors":["This value should not be blank."]},
              "gender":[]
            }
          }]
        }
        """
    
    Scenario: Cannot register a new user without a valide email
      Given the request body is:
        """
        {
          "username": "toto",
          "email": "bref",
          "gender": "MALE",
          "password": "a"
        }
        """
        When I request "/api/auth/register" using HTTP POST
        Then the response code is 422
        And the response body contains JSON:
        """
        {
          "status":"error",
          "Message":"Validation error",
          "errors":[{
            "children":{
              "username":[],
              "password":[],
              "email":{"errors":["This value is not a valid email address."]},
              "gender":[]
            }
          }]
        }
        """