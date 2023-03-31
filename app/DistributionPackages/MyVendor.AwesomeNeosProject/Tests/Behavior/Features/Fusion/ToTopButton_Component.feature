@fixtures
@playwright
Feature: Testcase for ToTopButton Component

    Background:
        Given I have a site for Site Node "site"
        Given I have the following nodes:
            | Identifier                           | Path               | Node Type                                      | Properties                   |
            | 5cb3a5f7-b501-40b2-b5a8-9de169ef1105 | /sites             | unstructured                                   | {}                           |
            | 5e312d5b-9559-4bd2-8251-0182e11b4950 | /sites/site        | MyVendor.AwesomeNeosProject:Document.StartPage | {}                           |

        Given I get a node by path "/sites/site" with the following context:
            | Workspace |
            | live      |

    Scenario: To Top Button
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Component.ToTopButton {
                x-data="toTopButton" 
                x-ref="toTopButton"
                title="To Top" 
                type="link"
                align="left"
                icon="icon-angle-up"
                additionalCssClasses="to-top-button"
            }
            """
        Then in the fusion output, the inner HTML of CSS selector ".button" matches '<span class="icon-angle-up"></span>Nach oben'
        Then in the fusion output, the attributes of CSS selector ".button__wrapper" are:
            | Key   | Value                                 |
            | class | button__wrapper button__wrapper--left |
        Then in the fusion output, the attributes of CSS selector ".button" are:
            | Key     | Value                                                                 |
            | class   | button button--link button--with-icon button--icon-left to-top-button |
            | x-data  | toTopButton                                                           |
            | x-ref   | toTopButton                                                           |
        Then in the fusion output, the attributes of CSS selector ".to-top-button__wrapper" are:
            | Key   | Value                                 |
            | class | to-top-button__wrapper content-width  |
        Then I store the Fusion output in the styleguide as "ToTopButton_Component"
