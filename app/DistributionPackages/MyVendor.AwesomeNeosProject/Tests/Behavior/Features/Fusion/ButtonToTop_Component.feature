@fixtures
@playwright
Feature: Testcase for ButtonToTop Component

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
            testcase = MyVendor.AwesomeNeosProject:Component.ButtonToTop {
                x-data="buttonToTop" 
                x-ref="buttonToTop"
                title="To Top" 
                type="link"
                align="center"
                icon="icon-angle-up"
                additionalCssClasses="button--to-top"
            }
            """
        Then in the fusion output, the inner HTML of CSS selector ".button" matches '<span class="icon-angle-up"></span>Nach oben'
        Then in the fusion output, the attributes of CSS selector ".button__wrapper" are:
            | Key   | Value                                 |
            | class | button__wrapper button__wrapper--center |
        Then in the fusion output, the attributes of CSS selector ".button" are:
            | Key     | Value                                                                  |
            | class   | button button--link button--with-icon button--icon-left button--to-top |
            | x-data  | buttonToTop                                                            |
            | x-ref   | buttonToTop                                                            |
        Then I store the Fusion output in the styleguide as "ButtonToTop_Component"
