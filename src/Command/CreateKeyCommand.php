<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Command;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Key;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CreateKeyCommand
 */
final class CreateKeyCommand extends Command
{
    protected static $defaultName = 'sylius:unzer-key:create';


    /**
     * @inheritdoc
     * @throws EnvironmentIsBrokenException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Unzer encryption key creation');
        $key = Key::createNewRandomKey()->saveToAsciiSafeString();
        $io->table(
        [
            'encryption key ENV variable',
        ],
        [
            [
                'UNZER_ENCRYPTION_KEY='.$key
            ],
        ],
        );
        $io->success('Encryption key has been successfully created. Add environment variable in Sylius .env file');

        return Command::SUCCESS;
    }
}
