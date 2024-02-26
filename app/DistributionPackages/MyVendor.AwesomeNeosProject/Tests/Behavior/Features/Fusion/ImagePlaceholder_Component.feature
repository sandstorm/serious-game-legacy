@playwright
@fixtures
Feature: Testcase for Image Placeholder Component

    Background:
        Given I have a site for Site Node "site"
        Given I have the following nodes:
            | Identifier                           | Path        | Node Type                                      | Properties |
            | 5cb3a5f7-b501-40b2-b5a8-9de169ef1105 | /sites      | unstructured                                   | {}         |
            | 5e312d5b-9559-4bd2-8251-0182e11b4950 | /sites/site | MyVendor.AwesomeNeosProject:Document.StartPage | {}         |

        Given I get a node by path "/sites/site" with the following context:
            | Workspace |
            | live      |

    Scenario: Image placeholder rendering
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Component.ImagePlaceholder
            """
        Then in the fusion output, the attributes of CSS selector ".image-placeholder" are:
            | Key   | Value                         |
            | class | image-placeholder             |
            | style | aspect-ratio: 1.7777777777778 |
        Then in the fusion output, the inner HTML of CSS selector ".image-placeholder" matches '<div class="image-placeholder__inner"><small>Bitte ein Bild im Inspector auswählen</small></div>'
        Then I store the Fusion output in the styleguide as "ImagePlaceholder_Component"

    Scenario: Image placeholder rendering without placeholder
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Component.ImagePlaceholder {
                showPlaceholderText = false
            }
            """
        Then in the fusion output, the attributes of CSS selector ".image-placeholder" are:
            | Key   | Value                         |
            | class | image-placeholder             |
            | style | aspect-ratio: 1.7777777777778 |
        Then in the fusion output, the inner HTML of CSS selector ".image-placeholder" matches '<div class="image-placeholder__inner"></div>'
        Then I store the Fusion output in the styleguide as "ImagePlaceholder_Component"

