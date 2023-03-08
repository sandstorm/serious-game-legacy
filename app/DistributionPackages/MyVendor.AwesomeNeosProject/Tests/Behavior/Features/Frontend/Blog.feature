@playwright
@fixtures
Feature: Blog post overview rendering in frontend

    The Blog page shows a overview of blog posts.

    Background:
        Given I have a site for Site Node "site" with name "Website"
        Given I have the following nodes:
            | Path                                                                      | Node Type                                                 | Properties                                                                                                                                                                                                                                                                                                                                                                           | HiddenInIndex | Language |
            | /sites                                                                    | unstructured                                              | []                                                                                                                                                                                                                                                                                                                                                                                   | false         | de       |
            | /sites/site                                                               | MyVendor.AwesomeNeosProject:Document.StartPage            | {"uriPathSegment":"site","title":"AwesomeNeosProject","privacyPage":"b9d32958-9bc0-4502-bdd2-274b54f1777e"}                                                                                                                                                                                                                                                                          | false         | de       |
            | /sites/site/node-ew3btfa0bg3rm                                            | MyVendor.AwesomeNeosProject:Document.Blog                 | {"uriPathSegment":"blog","title":"Blog"}                                                                                                                                                                                                                                                                                                                                             | false         | de       |
            | /sites/site/node-ew3btfa0bg3rm/main/node-vy26roea1l5o4                    | MyVendor.AwesomeNeosProject:Content.SectionWithBackground | {"backgroundColor":"white"}                                                                                                                                                                                                                                                                                                                                                          | false         | de       |
            | /sites/site/node-ew3btfa0bg3rm/main/node-vy26roea1l5o4/node-i7nznbi8848u9 | MyVendor.AwesomeNeosProject:Content.Headline              | {"title":"<h1>Blog<\/h1>"}                                                                                                                                                                                                                                                                                                                                                           | false         | de       |
            | /sites/site/node-ew3btfa0bg3rm/tags/node-abjc143jg57jx                    | MyVendor.AwesomeNeosProject:Document.Blog.Tag             | {"uriPathSegment":"some-tag","title":"Some Tag"}                                                                                                                                                                                                                                                                                                                                     | false         | de       |
            | /sites/site/node-ew3btfa0bg3rm/tags/node-bda0qyjey9ov2                    | MyVendor.AwesomeNeosProject:Document.Blog.Tag             | {"uriPathSegment":"another-tag","title":"Another Tag"}                                                                                                                                                                                                                                                                                                                               | false         | de       |
            | /sites/site/node-ew3btfa0bg3rm/node-42yguldjxluxp                         | MyVendor.AwesomeNeosProject:Document.Blog.Post            | {"uriPathSegment":"blog-post","title":"Blog Post","tags":["a12e12fd-57b3-42a7-a4c4-bc763d96667e","0f5c04ed-2975-476f-8854-31a4c4d4bffc"],"teaserText":"Description","datePublished":{"date":"2022-08-02 00:00:00.000000","timezone":"UTC","dateFormat":"Y-m-d H:i:s.u"},"image":{},"authors":["Michael Berhorst","LocalDev Admin"],"alternativeText":"Cat","imageDescription":"Cat"} | false         | de       |
            | /sites/site/node-ew3btfa0bg3rm/node-iol8xpm4s2du1                         | MyVendor.AwesomeNeosProject:Document.Blog.Post            | {"uriPathSegment":"another-blog-post","title":"Another Blog Post","image":{},"teaserText":"Description","tags":["0f5c04ed-2975-476f-8854-31a4c4d4bffc"],"authors":["LocalDev Admin"]}                                                                                                                                                                                                | false         | de       |
        And the content cache flush is executed
        And I accepted the Cookie Consent

    Scenario: Blog overview gets rendered
        Given I access the URI path "/blog"
        Then the page title should be "Blog - Website"
        Then there must be a blog post overview
        And there must be 2 blog-posts inside the blog overview
        And there must be 2 tags inside the blog overview
        And I must see a blog post with the headline "Another Blog Post" inside the blog overview

    Scenario: Changing a blog post invalidates reference content cache
        Given I access the URI path "/blog"
        And there must be 2 blog-posts inside the blog overview
        And there must be 2 tags inside the blog overview
        # node data change
        When I get a node by path "/sites/site/node-ew3btfa0bg3rm/node-iol8xpm4s2du1" with the following context:
            | Workspace | Language |
            | live      | de       |
        And I remove the node
        And I publish the node
        And the content cache flush is executed
        # now recheck the site output
        When I reload the current page
        And there must be 1 blog-posts inside the blog overview
        # TODO also test changing the title of the node
