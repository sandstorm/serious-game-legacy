@fixtures
@playwright
Feature: Testcase for Button Component

    Background:
        Given I have a site for Site Node "site"
        Given I have the following nodes:
            | Identifier                           | Path               | Node Type                                      | Properties                   |
            | 5cb3a5f7-b501-40b2-b5a8-9de169ef1105 | /sites             | unstructured                                   | {}                           |
            | 5e312d5b-9559-4bd2-8251-0182e11b4950 | /sites/site        | MyVendor.AwesomeNeosProject:Document.StartPage | {}                           |
            | 9cbaa2e2-d779-4936-aa02-0dab324da93e | /sites/site/nested | MyVendor.AwesomeNeosProject:Document.Page      | {"uriPathSegment": "nested"} |


        Given I get a node by path "/sites/site" with the following context:
            | Workspace |
            | live      |

    Scenario: Basic Button (external link)
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Component.Button {
              title = "External Link"
              href = "https://spiegel.de"
              isExternalLink = true
              type = "solid-blue"
              align = "left"
            }
            """
        Then in the fusion output, the inner HTML of CSS selector "a" matches "External Link"
        Then in the fusion output, the attributes of CSS selector "a" are:
            | Key   | Value                     |
            | class | button button--solid-blue |
            | href  | https://spiegel.de        |
        Then I store the Fusion output in the styleguide as "Button_Component_Basic"
