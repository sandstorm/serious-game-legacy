@playwright
@fixtures
@Breadcrumb
Feature: Breadcrumb rendering in frontend

    The Blog page shows a overview of blog posts.

    Background:
        Given I have a site for Site Node "site" with name "Website"
        Given I have the following nodes:
            | Path                                              | Node Type                                      | Properties                                                                                                  | HiddenInIndex | Language |
            | /sites                                            | unstructured                                   | []                                                                                                          | false         | de       |
            | /sites/site                                       | MyVendor.AwesomeNeosProject:Document.StartPage | {"uriPathSegment":"site","title":"AwesomeNeosProject","privacyPage":"b9d32958-9bc0-4502-bdd2-274b54f1777e"} | false         | de       |
            | /sites/site/node-ew3btfa0bg3rm                    | MyVendor.AwesomeNeosProject:Document.Page      | {"uriPathSegment":"page","title":"Page Level 1"}                                                            | false         | de       |
            | /sites/site/node-ew3btfa0bg3rm/node-ew3btfa0bg3ra | MyVendor.AwesomeNeosProject:Document.Page      | {"uriPathSegment":"another-page","title":"Page Level 2"}                                                    | false         | de       |
        And the content cache flush is executed
        And I accepted the Cookie Consent

    Scenario: Breadcrumb is not rendered on start page
        Given I access the URI path "/"
        Then the page title should be "AwesomeNeosProject - Website"
        And I must not see the breadcrumb

    Scenario: Breadcrumb renders correct 1
        Given I access the URI path "/page"
        Then the page title should be "Page Level 1 - Website"
        And the current element in the breadcrumb should be "Page Level 1"
        And the normal element in the breadcrumb should be "AwesomeNeosProject"

    Scenario: Breadcrumb renders correct 2
        Given I access the URI path "/page/another-page"
        Then the page title should be "Page Level 2 - Page Level 1 - Website"
        And the current element in the breadcrumb should be "Page Level 2"
        And the active element in the breadcrumb should be "Page Level 1"
        And the normal element in the breadcrumb should be "AwesomeNeosProject"
