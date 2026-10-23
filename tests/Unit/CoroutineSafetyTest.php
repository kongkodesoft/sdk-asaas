<?php

declare(strict_types=1);

namespace Kongkodesoft\Asaas\Tests\Unit;

use Kongkodesoft\Asaas\Tests\Support\Factory;
use Kongkodesoft\Asaas\Tests\Support\FakeClient;
use Kongkodesoft\Asaas\Tests\Support\RecordingSleeper;
use PHPUnit\Framework\TestCase;

final class CoroutineSafetyTest extends TestCase
{
    public function testNoMutableStaticOrWritableStateInSrc(): void
    {
        $root = dirname(__DIR__, 2) . '/src';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        $checked = 0;
        foreach ($files as $file) {
            if (!$file instanceof \SplFileInfo || $file->getExtension() !== 'php') {
                continue;
            }
            $class = 'Kongkodesoft\\Asaas\\' . str_replace(['/', '.php'], ['\\', ''], substr($file->getPathname(), strlen($root) + 1));
            if (!class_exists($class) && !interface_exists($class) && !enum_exists($class)) {
                continue;
            }
            $ref = new \ReflectionClass($class);
            self::assertSame([], $ref->getStaticProperties(), "$class possui propriedades estáticas.");
            if ($ref->isInterface() || $ref->isEnum()) {
                continue;
            }
            foreach ($ref->getProperties() as $prop) {
                if ($prop->getDeclaringClass()->getName() !== $class) {
                    continue;
                }
                if ($ref->isSubclassOf(\Throwable::class) && $prop->getDeclaringClass()->isInternal()) {
                    continue;
                }
                self::assertTrue($prop->isReadOnly(), "$class::\${$prop->getName()} deve ser readonly.");
            }
            $checked++;
        }
        self::assertGreaterThan(40, $checked);
    }

    public function testInterleavedFibersKeepResponsesIsolated(): void
    {
        $client = new FakeClient();
        // Respostas consumidas na ordem de envio; cada fiber suspende após enviar e valida a sua.
        $client->json(200, ['id' => 'cus_A'])->json(200, ['id' => 'cus_B'])->json(200, ['id' => 'cus_C']);
        $sdk = Factory::sdk($client, new RecordingSleeper());

        $results = [];
        $fibers = [];
        foreach (['A', 'B', 'C'] as $name) {
            $fibers[$name] = new \Fiber(function () use ($sdk, $name, &$results): void {
                \Fiber::suspend();
                $results[$name] = $sdk->customers->get('cus_' . $name)->id;
            });
            $fibers[$name]->start();
        }
        foreach ($fibers as $fiber) {
            $fiber->resume();
        }

        self::assertSame(['A' => 'cus_A', 'B' => 'cus_B', 'C' => 'cus_C'], $results);
        $uris = array_map(static fn ($r): string => basename((string) $r->getUri()), $client->requests);
        self::assertSame(['cus_A', 'cus_B', 'cus_C'], $uris);
    }
}
