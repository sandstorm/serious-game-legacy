@fixtures
@playwright
Feature: Testcase for Column Component

    Background:
        Given I have a site for Site Node "site"
        Given I have the following nodes:
            | Identifier                           | Path        | Node Type                                      | Properties |
            | 5cb3a5f7-b501-40b2-b5a8-9de169ef1105 | /sites      | unstructured                                   | {}         |
            | 5e312d5b-9559-4bd2-8251-0182e11b4950 | /sites/site | MyVendor.AwesomeNeosProject:Document.StartPage | {}         |

        Given I get a node by path "/sites/site" with the following context:
            | Workspace |
            | live      |

    Scenario: Column rendering with size 33
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Component.Columns.Column {
                size = "33"
            }
            """
        Then in the fusion output, the attributes of CSS selector ".columns__cell" are:
            | Key   | Value                                |
            | class | columns__cell columns__cell--size-33 |

    Scenario: Column rendering with size 66 and content
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Component.Columns.Column {
                size = "66"
                content = "<p>content</p>"
            }
            """
        Then in the fusion output, the attributes of CSS selector ".columns__cell" are:
            | Key   | Value                                |
            | class | columns__cell columns__cell--size-66 |
        Then in the fusion output, the inner HTML of CSS selector ".columns__cell" matches '<p>content</p>'
