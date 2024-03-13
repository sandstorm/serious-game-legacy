@playwright
@fixtures
@Button
Feature: Testcase for Button Component

    Background:
        Given I have a site for Site Node "site"
        Given I have the following nodes:
            | Identifier                           | Path        | Node Type                                      | Properties |
            | 5cb3a5f7-b501-40b2-b5a8-9de169ef1105 | /sites      | unstructured                                   | {}         |
            | 5e312d5b-9559-4bd2-8251-0182e11b4950 | /sites/site | MyVendor.AwesomeNeosProject:Document.StartPage | {}         |

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
        Then in the fusion output, the inner HTML of CSS selector ".button" matches "External Link"
        Then in the fusion output, the attributes of CSS selector ".button__wrapper" are:
            | Key   | Value                                 |
            | class | button__wrapper button__wrapper--left |
        Then in the fusion output, the attributes of CSS selector ".button" are:
            | Key    | Value                     |
            | class  | button button--solid-blue |
            | href   | https://spiegel.de        |
            | target | _blank                    |
        Then I store the Fusion output in the styleguide as "Button_Component_Basic"

    Scenario: Link Button centered
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Component.Button {
              title = "Link"
              href = "https://sandstorm.de"
              type = "link"
              align = "center"
            }
            """
        Then in the fusion output, the inner HTML of CSS selector ".button" matches "Link"
        Then in the fusion output, the attributes of CSS selector ".button__wrapper" are:
            | Key   | Value                                   |
            | class | button__wrapper button__wrapper--center |
        Then in the fusion output, the attributes of CSS selector ".button" are:
            | Key    | Value                |
            | class  | button button--link  |
            | href   | https://sandstorm.de |
            | target |                      |
        Then I store the Fusion output in the styleguide as "Button_Component_Link"
