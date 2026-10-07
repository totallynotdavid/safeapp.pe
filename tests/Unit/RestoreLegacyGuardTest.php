<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * The guard runs before the script starts or touches any database, so the real
 * script can be executed here.
 */
class RestoreLegacyGuardTest extends TestCase
{
    /** @return iterable<string, array{string}> */
    public static function dumpsThatNameADatabase(): iterable
    {
        yield 'create database' => ["CREATE DATABASE `safeapp`;\n"];
        yield 'lowercase create database' => ["create database safeapp;\n"];
        yield 'create schema' => ["CREATE SCHEMA IF NOT EXISTS safeapp;\n"];
        yield 'conditional-comment create database' => ["/*!32312 CREATE DATABASE IF NOT EXISTS `safeapp` */;\n"];
        yield 'drop database' => ["DROP DATABASE IF EXISTS `safeapp`;\n"];
        yield 'alter database' => ["ALTER DATABASE safeapp CHARACTER SET utf8mb4;\n"];
        yield 'use with backticks' => ["USE `safeapp`;\n"];
        yield 'lowercase use' => ["use safeapp;\n"];
        yield 'indented use' => ["    USE `safeapp`;\n"];
        yield 'tab-indented lowercase use' => ["\tuse safeapp;\n"];
        yield 'use after other statements' => ["SET NAMES utf8mb4;\nuse safeapp;\nCREATE TABLE t (id INT);\n"];
        yield 'client use command' => ["\\u safeapp\n"];
        yield 'client connect command' => ["connect safeapp\n"];
    }

    #[DataProvider('dumpsThatNameADatabase')]
    public function test_a_dump_that_names_a_database_is_refused(string $sql): void
    {
        $dump = tempnam(sys_get_temp_dir(), 'dump');
        $this->assertIsString($dump);
        file_put_contents($dump, "SET NAMES utf8mb4;\n{$sql}");

        try {
            $proceso = new Process([dirname(__DIR__, 2).'/scripts/db.sh', 'restore-legacy', $dump]);
            $proceso->run();
        } finally {
            unlink($dump);
        }

        $this->assertSame(1, $proceso->getExitCode());
        $this->assertStringContainsString('names a database', $proceso->getErrorOutput());
        $this->assertStringContainsString('--no-create-db', $proceso->getErrorOutput());
    }
}
