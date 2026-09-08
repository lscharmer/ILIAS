<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Database\Setup;

use ilDBInterface;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;
use ilIniFilesLoadedObjective;
use ilDatabaseInitializedObjective;
use ilDatabaseUpdatedObjective;
use ilDBPdoInterface;
use ilDBConstants;
use ILIAS\Database\PDO\Internal;

class MB4Migration implements Migration
{
    private const CHARSET = 'utf8mb4';

    private Internal $db;
    private string $dbName;
    private string $collation;

    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Environment::RESOURCE_DATABASE);
        $this->dbName = $this->db->getDbName();
        $this->collation = $this->selectCollation();
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilIniFilesLoadedObjective(),
            new ilDatabaseInitializedObjective(),
            new ilDatabaseUpdatedObjective()
        ];
    }

    public function step(Environment $environment): void
    {
        $this->convertCharsetDatabase();
        // $this->convertCharsetColumnsSpecialCases();
        $this->convertCharsetTables();
    }

    public function getDefaultAmountOfStepsPerRun(): int
    {
        return Migration::INFINITE;
    }

    public function getRemainingAmountOfSteps(): int
    {
        return 1;
    }

    public function getLabel(): string
    {
        return 'Migrate all tables from utf8mb3 to utf8mb4';
    }

    /**
     * Checks if the database supports utf8mb4_unicode_520_ci.
     * Should be the case for MariaDB 10.x & MySQL 5.7 and 8.0
     *
     * @return string
     */
    private function selectCollation(): string
    {
        $q = 'SHOW COLLATION WHERE COLLATION LIKE "utf8mb4_unicode_520_ci"';
        if ($this->db->fetchAssoc($this->db->query($q))) {
            return 'utf8mb4_unicode_520_ci'; // uca1400_ai_ci
        }
        return 'utf8mb4_unicode_ci';
    }

    /**
     * ilDBInterface provides the method listTables, but it ignores sequence tables.
     *
     * @return string[]
     */
    private function findTables(): array
    {
        $s = $this->db->query('SHOW TABLES FROM ' . $this->db->quoteIdentifier($this->dbName));
        return array_map('current', $this->db->fetchAll($s));
    }

    private function findDBCharset(): ?string
    {
        $q = 'SELECT DEFAULT_CHARACTER_SET_NAME as charset ' .
             'FROM information_schema.SCHEMATA ' .
             'WHERE SCHEMA_NAME = %s';
        return $this->db->fetchAssoc($this->db->queryF($q, [ilDBConstants::T_TEXT], [$this->dbName]))['charset'] ?? null;
    }

    private function findTableCharset(string $table): ?string
    {
        $q = 'SELECT CCSA.CHARACTER_SET_NAME AS charset ' .
             'FROM information_schema.TABLES AS T ' .
             'JOIN information_schema.COLLATION_CHARACTER_SET_APPLICABILITY AS CCSA ' .
             'WHERE T.TABLE_COLLATION = CCSA.COLLATION_NAME ' .
             'AND TABLE_SCHEMA=%s AND TABLE_NAME=%s';
        return $this->db->fetchAssoc($this->db->queryF(
            $q,
            [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT],
            [$this->dbName, $table]
        ))['charset'] ?? null;
    }

    // /**
    //  * @return null|array{CHARACTER_SET_NAME: ?string, COLUMN_TYPE: string, IS_NULLABLE: string, COLUMN_DEFAULT: string}
    //  */
    // private function findColumnMetadata(string $table, string $column): ?array
    // {
    //     $q = 'SELECT * FROM information_schema.COLUMNS WHERE table_schema = %s ' .
    //          'AND table_name = %s AND column_name = %s';
    //     return $this->db->fetchAssoc($this->db->queryF(
    //         $q,
    //         array_fill(0, 3, ilDBConstants::T_TEXT),
    //         [$this->dbName, $table, $column]
    //     ));
    // }

    // private function convertColumn(string $table, string $column, string $type): void
    // {
    //     $meta = $this->findColumnMetadata($table, $column);
    //     // Table / Column does not exist or is a non text field.
    //     if ($meta === null || $meta['CHARACTER_SET_NAME'] === null) {
    //         return;
    //     }
    //     if ($meta['CHARACTER_SET_NAME'] === self::CHARSET && $meta['COLUMN_TYPE'] === $type) {
    //         return;
    //     }
    //     $this->db->manipulate(sprintf(
    //         'ALTER TABLE %s CHANGE %s %s %s CHARACTER SET %s COLLATE %s %s NULL DEFAULT %s',
    //         $this->db->quoteIdentifier($table),
    //         $this->db->quoteIdentifier($column),
    //         $this->db->quoteIdentifier($column),
    //         $type,
    //         $this->db->quoteIdentifier(self::CHARSET),
    //         $this->db->quoteIdentifier($this->collation),
    //         $meta['IS_NULLABLE'] ? '' : 'NOT',
    //         $meta['COLUMN_DEFAULT']
    //     ));
    // }

    private function convertCharsetDatabase(): void
    {
        $charset = $this->findDBCharset();
        if ($charset === null || $charset === self::CHARSET) {
            return;
        }
        try {
            $this->db->manipulate(sprintf(
                'ALTER DATABASE %s CHARACTER SET = %s COLLATE = %s',
                $this->db->quoteIdentifier($this->dbName),
                $this->db->quoteIdentifier(self::CHARSET),
                $this->db->quoteIdentifier($this->collation),
            ));
        } catch (\Exception $e) {
            var_dump($e);
        }
    }

    private function convertCharsetTables(): void
    {
        foreach ($this->findTables() as $table) {
            $table_charset = $this->findTableCharset($table);
            if ($table_charset === null || $table_charset === self::CHARSET) {
                continue;
            }
            $this->db->manipulate(sprintf(
                'ALTER TABLE %s CONVERT TO CHARACTER SET %s COLLATE %s',
                $this->db->quoteIdentifier($table),
                $this->db->quoteIdentifier(self::CHARSET),
                $this->db->quoteIdentifier($this->collation),
            ));
        }
    }

    // /**
    //  * Tables with (var)char(4000) throw an error because the max row size will be to large.
    //  * With the migration from mb3 to mb4 a space of 4000*4 instead of 4000*3 bytes is required.
    //  */
    // private function convertCharsetColumnsSpecialCases(): void
    // {
    //     $cases = [
    //         'event' => ['description', 'location', 'tutor_name', 'details'],
    //         'crs_settings' => ['syllabus', 'contact_consultation', 'important', 'target_group'],
    //     ];
    //     foreach ($cases as $table => $columns) {
    //         foreach ($columns as $column) {
    //             $this->convertColumn($table, $column, 'text');
    //         }
    //     }
    // }
}
