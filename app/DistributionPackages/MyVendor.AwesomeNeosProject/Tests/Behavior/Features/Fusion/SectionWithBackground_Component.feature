@playwright
@fixtures
Feature: Testcase for Section With Background Component

    Background:
        Given I have a site for Site Node "site"
        Given I have the following nodes:
            | Identifier                           | Path        | Node Type                                      | Properties |
            | 5cb3a5f7-b501-40b2-b5a8-9de169ef1105 | /sites      | unstructured                                   | {}         |
            | 5e312d5b-9559-4bd2-8251-0182e11b4950 | /sites/site | MyVendor.AwesomeNeosProject:Document.StartPage | {}         |

        Given I get a node by path "/sites/site" with the following context:
            | Workspace |
            | live      |

    Scenario: Section with background rendering
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Component.SectionWithBackground {
                content = "content"
                backgroundColor = "white"
            }
            """
        Then in the fusion output, the attributes of CSS selector ".background-section" are:
            | Key   | Value                                      |
            | class | background-section background-color--white |
        Then in the fusion output, the inner HTML of CSS selector ".background-section" matches '<div class="content-width">content</div>'
        Then I store the Fusion output in the styleguide as "SectionWithBackground_Component"

    Scenario: Section with background rendering and no padding
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Component.SectionWithBackground {
                content = "content"
                backgroundColor = "blue"
                contentWidth = "large"
                padding = "none"
            }
            """
        Then in the fusion output, the attributes of CSS selector ".background-section" are:
            | Key   | Value                                                                      |
            | class | background-section background-color--blue background-section--padding-none |
        Then in the fusion output, the inner HTML of CSS selector ".background-section" matches '<div class="content-width content-width--large">content</div>'
        Then I store the Fusion output in the styleguide as "LargeSectionWithBackground_Component"
