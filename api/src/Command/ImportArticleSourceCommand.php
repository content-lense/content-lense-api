<?php

namespace App\Command;

use App\Entity\ArticleSource;
use App\Service\ArticleImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-articles',
    description: 'Takes an url of an article source that should be started',
)]
class ImportArticleSourceCommand extends Command
{
    private $importer;
    private $em;
    public function __construct(ArticleImportService $articleImportService, EntityManagerInterface $em)
    {
        $this->importer = $articleImportService;
        $this->em = $em;
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

        $source = $this->em->getRepository(ArticleSource::class)->findOneBy(["url" => $url]);
        if (!$source) {
            $io->error("No article source configuration found for URL " . $url);
            return Command::FAILURE;
        }

        $this->importer->importArticleSource($source);

        return Command::SUCCESS;
    }
}
