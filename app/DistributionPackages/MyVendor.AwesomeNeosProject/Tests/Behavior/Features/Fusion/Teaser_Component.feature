@playwright
@fixtures
Feature: Testcase for Teaser Component

    Background:
        Given I have a site for Site Node "site"
        Given I have the following nodes:
            | Identifier                           | Path        | Node Type                                      | Properties |
            | 5cb3a5f7-b501-40b2-b5a8-9de169ef1105 | /sites      | unstructured                                   | {}         |
            | 5e312d5b-9559-4bd2-8251-0182e11b4950 | /sites/site | MyVendor.AwesomeNeosProject:Document.StartPage | {}         |

        Given I get a node by path "/sites/site" with the following context:
            | Workspace |
            | live      |

    Scenario: Teaser rendering
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Component.Teaser {
                image = '<img src="http://via.placeholder.com/640x360" />'
                content = '<h1>headline</h1>'
                height = "90vh"
                textColor = "dark"
            }
            """
        Then in the fusion output, the attributes of CSS selector ".teaser" are:
            | Key   | Value                                 |
            | class | teaser teaser--90vh teaser--text-dark |
        Then in the fusion output, the inner HTML of CSS selector ".teaser" matches '<img src="http://via.placeholder.com/640x360"><h1>headline</h1>'
        Then I store the Fusion output in the styleguide as "Teaser_Component"
