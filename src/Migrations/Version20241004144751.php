<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Migrations;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241004144751 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Unzer initial migrations';
    }

    /**
     * @param Schema $schema
     *
     * @return void
     *
     * @throws Exception
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.');
        $this->addSql("CREATE TABLE unzer_entity (
                            id INT AUTO_INCREMENT NOT NULL,
                            type VARCHAR(255),
                            index_1 VARCHAR(255),
                            index_2 VARCHAR(255),
                            index_3 VARCHAR(255),
                            index_4 VARCHAR(255),
                            index_5 VARCHAR(255),
                            index_6 VARCHAR(255),
                            index_7 VARCHAR(255),
                            index_8 VARCHAR(255),
                            data MEDIUMTEXT,
                            PRIMARY KEY (id)
            )");

        $this->addSql("CREATE TABLE unzer_transactions (
                            id INT AUTO_INCREMENT NOT NULL,
                            type VARCHAR(255),
                            index_1 VARCHAR(255),
                            index_2 VARCHAR(255),
                            index_3 VARCHAR(255),
                            index_4 VARCHAR(255),
                            index_5 VARCHAR(255),
                            index_6 VARCHAR(255),
                            index_7 VARCHAR(255),
                            index_8 VARCHAR(255),
                            data MEDIUMTEXT,
                            PRIMARY KEY (id)
            )");
    }

    /**
     * @param Schema $schema
     *
     * @return void
     *
     * @throws Exception
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(),
            'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE unzer_entity');
    }
}
