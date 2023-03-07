@fixtures
@playwright
Feature: Testcase for Columns Component

    Background:
        Given I have a site for Site Node "site" with name "Website"
        Given I have the following nodes:
            | Path                                                                              | Node Type                                                 | Properties                                                                                                  | HiddenInIndex | Language |
            | /sites                                                                            | unstructured                                              | []                                                                                                          | false         | de       |
            | /sites/site                                                                       | MyVendor.AwesomeNeosProject:Document.StartPage            | {"uriPathSegment":"site","title":"AwesomeNeosProject","privacyPage":"b9d32958-9bc0-4502-bdd2-274b54f1777e"} | false         | de       |
            | /sites/site/main/node-vy26roea1l5o4                                               | MyVendor.AwesomeNeosProject:Content.SectionWithBackground | {"backgroundColor":"white"}                                                                                 | false         | de       |
            | /sites/site/main/node-vy26roea1l5o4/node-njyekkhq0q8vu                            | MyVendor.AwesomeNeosProject:Content.TwoColumn             | {"layout":"33-66"}                                                                                          | false         | de       |
            | /sites/site/main/node-vy26roea1l5o4/node-njyekkhq0q8vu/column0/node-tu7phohlefims | MyVendor.AwesomeNeosProject:Content.Headline              | {"title":"<h2>Headline<\/h2>"}                                                                              | false         | de       |
            | /sites/site/main/node-vy26roea1l5o4/node-njyekkhq0q8vu/column1/node-43bsb4ychm4rz | MyVendor.AwesomeNeosProject:Content.Text                  | {"text":"Some text"}                                                                                        | false         | de       |
        And the content cache flush is executed
        And I accepted the Cookie Consent

    Scenario: Two Columns
        Given I access the URI path "/"
        Then the page title should be "AwesomeNeosProject - Website"
        Then the size of column 1 should be 33
        Then the size of column 2 should be 66
