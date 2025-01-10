<?php

namespace RalphJSmit\Packages;

use Closure;
use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PreFileDownloadEvent;
use Spatie\Url\Url;

class Plugin implements EventSubscriberInterface, PluginInterface
{
    public const PLUGIN_VERSION = '1.4.2';

    protected Closure $directoryResolver;

    public function __construct(
        protected string $directorySeparator = DIRECTORY_SEPARATOR,
        ?Closure $directoryResolver = null,
    ) {
        $this->directoryResolver = $directoryResolver ?? function (): string {
            return __DIR__;
        };
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PluginEvents::PRE_FILE_DOWNLOAD => [
                [
                    'onPreFileDownload',
                    9999,
                ],
            ],
        ];
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
        //
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        //
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        //
    }

    /**
     * Note for future self: update the `PLUGIN_VERSION` constant before releasing.
     */
    public function onPreFileDownload(PreFileDownloadEvent $event): void
    {
        $processedUrl = $event->getProcessedUrl();

        if (! str_contains($processedUrl, 'ralphjsmit')) {
            return;
        }

        $processedUrl = Url::fromString($processedUrl);

        $directory = ($this->directoryResolver)();

        if (str_contains($directory, 'releases')) {
            $directorySanitized = $this->getEnvoyerDirectorySanitized($directory);
            $directoryName = $this->getEnvoyerDirectoryName($directory);
        } elseif (str_contains($directory, 'ploi') && str_contains(
            $directory,
            "-deploy{$this->directorySeparator}"
        )) {
            $directorySanitized = $this->getPloiDirectorySanitized($directory);
            $directoryName = $this->getPloiDirectoryName($directory);
        } else {
            $directorySanitized = $directory;
            $directoryName = $this->getDefaultDirectoryName($directory);
        }

        $identifier = gethostname().'|'.sha1($directorySanitized).'|'.$directoryName;
        $ralphjsmitPackagesVersion = static::PLUGIN_VERSION;

        // Modifying this code is against the product license. Just buy the dang thing and save yourself the effort.
        $event->setProcessedUrl((string) $processedUrl->withQueryParameter('id', $identifier)->withQueryParameter('ralphjsmit-packages-version', $ralphjsmitPackagesVersion));
    }

    protected function getEnvoyerDirectorySanitized(string $directory): string
    {
        // Escape the directory separator for use in the regex string...
        $directorySeparator = preg_quote($this->directorySeparator, '#');

        return preg_replace(
            '#'.$directorySeparator.'releases'.$directorySeparator.'.*?'.$directorySeparator.'vendor'.$directorySeparator.'#',
            "{$directorySeparator}releases{$directorySeparator}{release}{$directorySeparator}vendor{$directorySeparator}",
            $directory
        );
    }

    protected function getEnvoyerDirectoryName(string $directory): string
    {
        // Str::before() implementation...
        $directoryBeforeReleases = strstr($directory, 'releases', true);

        if ($directoryBeforeReleases === false) {
            $directoryBeforeReleases = $directory;
        }

        // Trim the trailing directory separator.
        $directoryBeforeReleases = rtrim($directoryBeforeReleases, $this->directorySeparator);

        // Str::afterLast() implementation...
        $positionLastDirectorySeparator = strrpos($directoryBeforeReleases, (string) $this->directorySeparator);

        if ($positionLastDirectorySeparator === false) {
            return $directoryBeforeReleases;
        }

        return substr($directoryBeforeReleases, $positionLastDirectorySeparator + strlen($this->directorySeparator));
    }

    protected function getPloiDirectorySanitized(string $directory): string
    {
        $directorySeparator = preg_quote($this->directorySeparator, '#');

        // Pattern that will match the Ploi timestamp format of `ddmmyyyy_hhmmss`...
        return preg_replace(
            '#'.$directorySeparator.'\d{8}_\d{6}'.$directorySeparator.'#',
            $directorySeparator.'{release}'.$directorySeparator,
            $directory
        );
    }

    protected function getPloiDirectoryName(string $directory): string
    {
        // Str::after() implementation...
        $partAfterDeployDash = array_reverse(explode('-deploy'.$this->directorySeparator, $directory, 2))[0];

        // Str::before() implementation...
        return strstr($partAfterDeployDash, $this->directorySeparator, true);
    }

    protected function getDefaultDirectoryName(string $directory): string
    {
        $directorySeparator = $this->directorySeparator;

        // Windows uses backslashes as directory separators. If we use a backslash in the string for regex, we need to escape this one double.
        if ($directorySeparator === '\\') {
            $directorySeparator = '\\\\';
        }

        preg_match(
            '#'.$directorySeparator.'([^'.$directorySeparator.']+)'.$directorySeparator.'vendor'.$directorySeparator.'#',
            $directory,
            $matches
        );

        return $matches[1] ?? $directory;
    }

    protected function isDocker(): bool
    {
        return file_exists(($this->directoryResolver)().'/../../../../.dockerenv');
    }
}
