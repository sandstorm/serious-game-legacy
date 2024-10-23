<?php


use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\assertFalse;
use Neos\Neos\Fusion\Cache\ContentCacheFlusher;
use Neos\Utility\ObjectAccess;

trait FrontendControlTrait
{
    /**
     * @When I reload the current page
     */
    public function iReloadTheCurrentPage(): void
    {
        $this->playwrightConnector->execute($this->playwrightContext,
            // language=JavaScript
            '
            // sleep time expects milliseconds
            await vars.page.reload();
        '// language=PHP
        );
    }

    /**
     * @Given I accepted the Cookie Consent
     */
    public function iAcceptedTheCookieConsent(): void
    {
        $this->playwrightConnector->execute($this->playwrightContext,
            // language=JavaScript
            '
            await context.addCookies([{
                name: "cookie_punch",
                value: "%7B%22default%22%3Atrue%2C%22media%22%3Atrue%7D",
                url: "BASEURL"
            }]);
        '// language=PHP
        );
    }

    /**
     * @When the content cache flush is executed
     */
    public function theContentCacheFlushIsExecuted(): void
    {
        /**
         * @var ContentCacheFlusher $contentCacheFlusher
         */
        $contentCacheFlusher = $this->getObjectManager()->get(ContentCacheFlusher::class);
        $contentCacheFlusher->shutdownObject();
        ObjectAccess::setProperty($contentCacheFlusher, 'tagsToFlush', [], true);
    }

    /**
     * @When /^I pause for debugging$/
     */
    public function iPauseForDebugging(): mixed
    {
        return $this->playwrightConnector->execute(
            $this->playwrightContext,
            // language=JavaScript
            '
                await vars.page.pause();
            '
        );
    }

    /**
     * @Then the page title should be :title
     */
    public function thePageTitleShouldBe(string $title): void
    {
        $actualTitle = $this->playwrightConnector->execute($this->playwrightContext,
            // language=JavaScript
            '
                return await vars.page.textContent(`head title`);
        ');// language=PHP
        assertEquals($title, $actualTitle, 'page title mismatch');
    }

    /**
     * @Then I must not see the breadcrumb
     */
    public function iMustNotSeeTheBreadcrumb(): void
    {
        $isVisible = $this->playwrightConnector->execute($this->playwrightContext,
            // language=JavaScript
            '
            return await vars.page.getByTestId(`breadcrumb`).isVisible()
        ');// language=PHP
        assertFalse($isVisible, "breadcrumb is not visible");
    }

    /**
     * @Then the :level element in the breadcrumb should be :elementName
     */
    public function theCurrentElementInTheBreadcrumbShouldBe(string $level, string $elementName): void
    {
        /**
         * @var string $actualCurrentElement
         */
        $actualCurrentElement = $this->playwrightConnector->execute($this->playwrightContext,  sprintf(
        // language=JavaScript
            '
                const currentBreadcrumbContent = await vars.page.getByTestId(`breadcrumb`).locator(`li.%s`).textContent();
                return currentBreadcrumbContent.trim();
        ', $level));// language=PHP

        assertEquals($elementName, $actualCurrentElement, 'element mismatch');
    }

    /**
     * @Then there must be a blog post overview
     */
    public function thereMustBeABlogPostOverview(): void
    {
        $this->playwrightConnector->execute($this->playwrightContext,
            // language=JavaScript
            '
            await vars.page.locator(`.blog-post-overview`).waitFor();
        '// language=PHP
        );
    }

    /**
     * @Then there must be :blogPostCount blog-posts inside the blog overview
     */
    public function thereMustBeXBlogPostsInsideTheBlogOverview(int $blogPostCount): void
    {
        /**
         * @var int $actualBlogPostCount
         */
        $actualBlogPostCount = $this->playwrightConnector->execute($this->playwrightContext,
            // language=JavaScript
            '
            return await vars.page.getByTestId("blogPost").count();
        '// language=PHP
        );
        assertEquals($blogPostCount, $actualBlogPostCount, "expected $blogPostCount blog posts inside the overview, but found $actualBlogPostCount");
    }

    /**
     * @Then there must be :tagCount tags inside the blog overview
     */
    public function thereMustBeXTagsInsideTheBlogOverview(int $tagCount): void
    {
        /**
         * @var int $actualTagCount
         */
        $actualTagCount = $this->playwrightConnector->execute($this->playwrightContext,
            // language=JavaScript
            '
            return await vars.page.getByTestId("blogTag").count();
        '// language=PHP
        );
        assertEquals($tagCount, $actualTagCount, "expected $tagCount tags inside the overview, but found $actualTagCount");
    }

    /**
     * @Then I must see a blog post with the headline :expectedHeadline inside the blog overview
     */
    public function iMustSeeABlogPostWithTheHeadlineXInsideTheBlogOverview(string $expectedHeadline): void
    {
        /**
         * @var bool $isVisible
         */
        $isVisible = $this->playwrightConnector->execute($this->playwrightContext, sprintf(
        // language=JavaScript
            '
            return await vars.page.isVisible(`.blog-post-overview h3:text-matches("^%s$", "i")`)
        '// language=PHP
            , $expectedHeadline));
        assertTrue($isVisible, "expected blog post with headline '$expectedHeadline' inside the overview not found");
    }

    /**
     * @Then the size of column :column should be :size
     */
    public function theSizeOfColumnXShouldBeY(int $column, string $size): void
    {
        $classList = $this->getClassListForColumn($column);
        $expectedSizeClassName = "columns__cell--size-$size";
        assertEquals(true, in_array($expectedSizeClassName, $classList), "expected column '$column' to have size '$size'. But class list is: ". implode(",", $classList));
    }

    /**
     * @return array<string>
     */
    private function getClassListForColumn(int $column): array
    {
        /**
         * @var array<string> $classList
         */
        $classList = $this->playwrightConnector->execute($this->playwrightContext, sprintf(
            // language=JavaScript
            '
                    return await vars.page.$eval(".columns .columns__cell:nth-of-type(%s)", el => el.classList);
            ',
            // language=PHP
            $column)
        );

        return $classList;
    }

    /**
     * @When I wait for the url to be :url
     */
    public function iWaitForTheUrlToBe(string $url): void
    {
        $this->playwrightConnector->execute(
            $this->playwrightContext,
            <<<JS
                await vars.page.waitForURL("**$url**", { waitUntil: "domcontentloaded" });
            JS,
        );
    }
}
