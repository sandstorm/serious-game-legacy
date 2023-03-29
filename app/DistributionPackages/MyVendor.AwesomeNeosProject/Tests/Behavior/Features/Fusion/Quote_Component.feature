@fixtures
@playwright
Feature: Testcase for Quote Component

    Background:
        Given I have a site for Site Node "site"
        Given I have the following nodes:
            | Identifier                           | Path               | Node Type                                      | Properties                   |
            | 5cb3a5f7-b501-40b2-b5a8-9de169ef1105 | /sites             | unstructured                                   | {}                           |
            | 5e312d5b-9559-4bd2-8251-0182e11b4950 | /sites/site        | MyVendor.AwesomeNeosProject:Document.StartPage | {}                           |

        Given I get a node by path "/sites/site" with the following context:
            | Workspace |
            | live      |

    Scenario: Quote rendering without image
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Component.Quote {
                text = "the quote"
                person = "the person"
            }
            """
        Then in the fusion output, the inner HTML of CSS selector ".quote" matches '<figure class="quote__content"><blockquote class="quote__text">the quote</blockquote><figcaption class="quote__person">the person</figcaption></figure>'
        Then I store the Fusion output in the styleguide as "Quote_Component"

    Scenario: Quote rendering with image
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Component.Quote {
                text = "the quote"
                person = "the person"
                image = '<img src="http://via.placeholder.com/640x360" />'
                hasImageSource = true
            }
            """
        Then in the fusion output, the inner HTML of CSS selector ".quote" matches '<div class="quote__image"><img src="http://via.placeholder.com/640x360"></div><figure class="quote__content"><blockquote class="quote__text">the quote</blockquote><figcaption class="quote__person">the person</figcaption></figure>'
        Then I store the Fusion output in the styleguide as "QuoteWithImage_Component"
