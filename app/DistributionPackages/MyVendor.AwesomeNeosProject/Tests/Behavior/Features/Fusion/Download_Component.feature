@playwright
@fixtures
Feature: Testcase for Download Component

    Background:
        Given I have a site for Site Node "site"
        Given I have the following nodes:
            | Identifier                           | Path        | Node Type                                      | Properties |
            | 5cb3a5f7-b501-40b2-b5a8-9de169ef1105 | /sites      | unstructured                                   | {}         |
            | 5e312d5b-9559-4bd2-8251-0182e11b4950 | /sites/site | MyVendor.AwesomeNeosProject:Document.StartPage | {}         |

        Given I get a node by path "/sites/site" with the following context:
            | Workspace |
            | live      |

    Scenario: Download rendering
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Component.Download {
                fileUrl = 'http://file.pdf'
                description = "Download button"
                align = "left"
                fileSize = "1 MB"
                type = "solid-blue"
            }
            """
        Then in the fusion output, the attributes of CSS selector ".button__wrapper" are:
            | Key   | Value                                 |
            | class | button__wrapper button__wrapper--left |
        Then in the fusion output, the inner HTML of CSS selector ".download" matches '<span class="icon-arrow-down-to-line download__icon" aria-hidden="true"></span><span>Download button</span><span class="download__size">(1 MB)</span>'
        Then in the fusion output, the attributes of CSS selector ".download" are:
            | Key    | Value                              |
            | class  | button button--solid-blue download |
            | href   | http://file.pdf                    |
            | target | _blank                             |
        Then I store the Fusion output in the styleguide as "Download_Component"

    Scenario: Download rendering without file size
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Component.Download {
                fileUrl = 'http://file.pdf'
                description = "Download button"
                align = "left"
                type = "solid-grey"
            }
            """
        Then in the fusion output, the attributes of CSS selector ".button__wrapper" are:
            | Key   | Value                                 |
            | class | button__wrapper button__wrapper--left |
        Then in the fusion output, the inner HTML of CSS selector ".download" matches '<span class="icon-arrow-down-to-line download__icon" aria-hidden="true"></span><span>Download button</span>'
        Then in the fusion output, the attributes of CSS selector ".download" are:
            | Key    | Value                              |
            | class  | button button--solid-grey download |
            | href   | http://file.pdf                    |
            | target | _blank                             |
        Then I store the Fusion output in the styleguide as "Download_Component_no-filesize"
