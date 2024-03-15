<?php

use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;
use function PHPUnit\Framework\assertFalse;
use Neos\Neos\Fusion\Cache\ContentCacheFlusher;
use Neos\Utility\ObjectAccess;

trait EventListTrait
{
    /**
     * @Then I see :amount events
     */
    public function iSeeXEvents(int $expectedAmount): void
    {
        /**
         * @var int $amount
         */
        $amount = $this->playwrightConnector->execute($this->playwrightContext,
            // language=JavaScript
            "
                return await vars.page.locator('.event-list__event').count()
            "// language=PHP
        );

        assertEquals($expectedAmount, $amount, "Expected $expectedAmount events, but got $amount.");
    }

    /**
     * @Then I click on the filter button
     */
    public function iClickOnTheFilterButton(): void
    {
        $this->playwrightConnector->execute($this->playwrightContext,
            // language=JavaScript
            "
                await vars.page.getByTestId('filterButton').click();
            "// language=PHP
        );
    }

    /**
     * @Then I click on the filter with title :title
     */
    public function iClickOnTheFilterWithTitleX(string $title): void
    {
        $this->playwrightConnector->execute($this->playwrightContext,
        sprintf(
            // language=JavaScript
            "
                await vars.page.locator('.event-list__filter .event-list__tag')
                    .filter({ hasText: '%s' })
                    .click()
            ",// language=PHP
            $title
            )
        );
    }

    /**
     * @Then I delete all filter
     */
    public function iDeleteAllFilter(): void
    {
        $this->playwrightConnector->execute($this->playwrightContext,
            // language=JavaScript
            "
                await vars.page.getByTestId('deleteFilterButton').click();
            "// language=PHP
        );
    }

}
