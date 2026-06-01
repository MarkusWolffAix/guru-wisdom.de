<?php

declare(strict_types=1);

namespace App\Console; 

use App\Service\WisdomCacheService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ClearWisdomCacheCommand extends Command
{
    protected static $defaultName = 'wisdom/clear-cache';
    protected static $defaultDescription = 'Clears the cache for parsed Markdown wisdoms.';

    public function __construct(
        private WisdomCacheService $wisdomCacheService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $io->info('Starting to clear the wisdom cache...');
            
            $this->wisdomCacheService->clearCache();
            
            $io->success('Success! The wisdom cache has been freshly cleared.');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error clearing the cache: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}