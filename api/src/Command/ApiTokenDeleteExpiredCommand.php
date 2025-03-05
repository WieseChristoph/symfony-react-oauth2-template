<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ApiTokenRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;

#[AsCommand(
    name: 'app:api-token:delete-expired',
    description: 'Deletes expired API tokens from the database.',
)]
class ApiTokenDeleteExpiredCommand extends Command
{
    public function __construct(
        private readonly ApiTokenRepository $apiTokenRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $deletedTokenCount = $this->apiTokenRepository->deleteExpiredTokens();

        $io->success(sprintf('Deleted %d expired API tokens.', $deletedTokenCount));

        return Command::SUCCESS;
    }
}
