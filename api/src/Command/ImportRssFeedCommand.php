<?php

namespace App\Command;

use App\Service\RssImportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-rss-feed',
    description: 'Takes an url of an rss feed that should be imported',
)]
class ImportRssFeedCommand extends Command
{
    private $importer;
    public function __construct(RssImportService $rssImportService)
    {
        $this->importer = $rssImportService;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::REQUIRED, 'RSS Feed Url that we want to import articles from');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $url = $input->getArgument('url');

        $confirmed = $io->confirm("Do you want to import articles from " . $url, false);
        if (!$confirmed) {
            $io->info("No worries, we did not execute anything.");
            return Command::FAILURE;
        }

        $this->importer->importFromUrl($url);

        return Command::SUCCESS;
    }
}
