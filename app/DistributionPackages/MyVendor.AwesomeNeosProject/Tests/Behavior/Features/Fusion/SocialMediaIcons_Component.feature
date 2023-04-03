@fixtures
@playwright
Feature: Testcase for Social Media Icons Component

    Background:
        Given I have a site for Site Node "site"
        Given I have the following nodes:
            | Identifier                           | Path        | Node Type                                      | Properties                                  |
            | 5cb3a5f7-b501-40b2-b5a8-9de169ef1105 | /sites      | unstructured                                   | {}                                          |
            | 5e312d5b-9559-4bd2-8251-0182e11b4950 | /sites/site | MyVendor.AwesomeNeosProject:Document.StartPage | {"facebookLink": "https://www.facebook.de"} |

        Given I get a node by path "/sites/site" with the following context:
            | Workspace |
            | live      |

    Scenario: Social media icons integration component gets link from start page setting
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Content.SocialMediaIcons
            """
        Then in the fusion output, the inner HTML of CSS selector ".social-media-icons" matches '<a target="_blank" href="https://www.facebook.de" aria-label="Facebook" class="social-media-icons__link"><span class="icon-facebook" aria-hidden="true"></span></a>'

    Scenario: Social media icons rendering with non set
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Component.SocialMediaIcons
            """
        Then in the fusion output, the inner HTML of CSS selector ".social-media-icons" matches ''

    Scenario: Social media icons rendering
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Component.SocialMediaIcons {
                instagramLink = 'https://www.instagram.de'
                facebookLink = 'https://www.facebook.de'
                twitterLink = 'https://www.twitter.de'
                youtubeLink = 'https://www.youtube.de'
                mastodonLink = ''
            }
            """
        Then in the fusion output, the inner HTML of CSS selector ".social-media-icons" matches '<a target="_blank" href="https://www.instagram.de" aria-label="Instagram" class="social-media-icons__link"><span class="icon-instagram" aria-hidden="true"></span></a><a target="_blank" href="https://www.facebook.de" aria-label="Facebook" class="social-media-icons__link"><span class="icon-facebook" aria-hidden="true"></span></a><a target="_blank" href="https://www.twitter.de" aria-label="Twitter" class="social-media-icons__link"><span class="icon-twitter" aria-hidden="true"></span></a><a target="_blank" href="https://www.youtube.de" aria-label="YouTube" class="social-media-icons__link"><span class="icon-youtube" aria-hidden="true"></span></a>'

    Scenario: Social media icons rendering only mastodon
        When I render the Fusion object "/testcase" with the current context node:
            """fusion
            testcase = MyVendor.AwesomeNeosProject:Component.SocialMediaIcons {
                mastodonLink = 'https://www.mastodon.de'
            }
            """
        Then in the fusion output, the inner HTML of CSS selector ".social-media-icons" matches '<a target="_blank" href="https://www.mastodon.de" aria-label="Mastodon" class="social-media-icons__link"><span class="icon-mastodon" aria-hidden="true"></span></a>'

