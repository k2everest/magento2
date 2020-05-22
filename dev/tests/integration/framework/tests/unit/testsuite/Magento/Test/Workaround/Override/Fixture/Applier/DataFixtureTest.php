<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Workaround\Override\Fixture\Applier;

use Magento\TestFramework\Workaround\Override\Fixture\Applier\DataFixture;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for \Magento\TestFramework\Workaround\Override\Fixture\Applier\DataFixture
 */
class DataFixtureTest extends TestCase
{
    /** @var DataFixture */
    private $object;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new DataFixture();
    }

    /**
     * @return void
     */
    public function testGetPrioritizedConfig(): void
    {
        $this->object = $this->getMockBuilder(DataFixture::class)
            ->setMethods(['getClassConfig', 'getMethodConfig', 'getDataSetConfig'])
            ->getMock();
        $this->object->expects($this->once())
            ->method('getClassConfig')
            ->willReturn(['class_config']);
        $this->object->expects($this->once())
            ->method('getMethodConfig')
            ->willReturn(['method_config']);
        $this->object->expects($this->once())
            ->method('getDataSetConfig')
            ->willReturn(['data_set_config']);
        $expectedResult = [
            ['class_config'],
            ['method_config'],
            ['data_set_config'],
        ];
        $reflectionMethod = new \ReflectionMethod(DataFixture::class, 'getPrioritizedConfig');
        $reflectionMethod->setAccessible(true);
        $this->assertEquals($expectedResult, $reflectionMethod->invoke($this->object));
    }

    /**
     * @dataProvider fixturesProvider
     *
     * @param array $existingFixtures
     * @param array $config
     * @param array $expectedOrder
     * @return void
     */
    public function testSortFixtures(array $existingFixtures, array $config, array $expectedOrder): void
    {
        $fixtures = $this->processApply($existingFixtures, $config);
        $this->assertEquals($expectedOrder, $fixtures);
    }

    /**
     * @return array
     */
    public function fixturesProvider(): array
    {
        return [
            'sort_fixtures_before_all' => [
                'existing_fixtures' => ['fixture'],
                'config' => [
                    [
                        'path' => 'added_fixture',
                        'newPath' => null,
                        'before' => '-',
                        'after' => null,
                        'remove' => false,
                    ]
                ],
                'expected_order' => ['added_fixture', 'fixture'],
            ],
            'sort_fixtures_after_all' => [
                'existing_fixtures' => ['fixture'],
                'config' => [
                    [
                        'path' => 'added_fixture',
                        'newPath' => null,
                        'before' => null,
                        'after' => '-',
                        'remove' => false,
                    ]
                ],
                'expected_order' => ['fixture', 'added_fixture'],
            ],
            'sort_fixture_before_specific' => [
                'existing_fixtures' => ['fixture1', 'fixture2'],
                'config' => [
                    [
                        'path' => 'added_fixture',
                        'newPath' => null,
                        'before' => 'fixture2',
                        'after' => null,
                        'remove' => false,
                    ]
                ],
                'expected_order' => ['fixture1', 'added_fixture', 'fixture2'],
            ],
            'sort_fixture_after_specific' => [
                'existing_fixtures' => ['fixture1', 'fixture2', 'fixture3'],
                'config' => [
                    [
                        'path' => 'added_fixture',
                        'newPath' => null,
                        'before' => null,
                        'after' => 'fixture2',
                        'remove' => false,
                    ]
                ],
                'expected_order' => ['fixture1', 'fixture2', 'added_fixture', 'fixture3'],
            ],
        ];
    }

    /**
     * @dataProvider removeFixturesProvider
     *
     * @param array $existingFixtures
     * @param array $config
     * @param array $expectedOrder
     * @return void
     */
    public function testRemoveFixtures(array $existingFixtures, array $config, array $expectedOrder): void
    {
        $fixtures = $this->processApply($existingFixtures, $config);
        $this->assertEquals($expectedOrder, $fixtures);
    }

    /**
     * @return array
     */
    public function removeFixturesProvider(): array
    {
        return [
            'remove_fixture' => [
                'existing_fixtures' => ['fixture', 'fixture2'],
                'config' => [
                    [
                        'path' => 'fixture',
                        'newPath' => null,
                        'before' => null,
                        'after' => null,
                        'remove' => true,
                    ]
                ],
                'expected_order' => ['fixture2'],
            ],
            'remove_one_of_same_fixtures' => [
                'existing_fixtures' => ['fixture', 'fixture', 'fixture2'],
                'config' => [
                    [
                        'path' => 'fixture',
                        'newPath' => null,
                        'before' => null,
                        'after' => null,
                        'remove' => true,
                    ]
                ],
                'expected_order' => ['fixture', 'fixture2'],
            ],
            'remove_all_of_same_fixtures' => [
                'existing_fixtures' => ['fixture', 'fixture', 'fixture2'],
                'config' => [
                    [
                        'path' => 'fixture',
                        'newPath' => null,
                        'before' => null,
                        'after' => null,
                        'remove' => true,
                    ],
                    [
                        'path' => 'fixture',
                        'newPath' => null,
                        'before' => null,
                        'after' => null,
                        'remove' => true,
                    ]
                ],
                'expected_order' => ['fixture2'],
            ],
        ];
    }

    /**
     * @dataProvider replaceFixturesProvider
     *
     * @param array $existingFixtures
     * @param array $config
     * @param array $expectedOrder
     * @return void
     */
    public function testReplaceFixtures(array $existingFixtures, array $config, array $expectedOrder): void
    {
        $fixtures = $this->processApply($existingFixtures, $config);
        $this->assertEquals($expectedOrder, $fixtures);
    }

    /**
     * @return array
     */
    public function replaceFixturesProvider(): array
    {
        return [
            'replace_one_fixture' => [
                'existing_fixtures' => ['fixture', 'fixture2'],
                'config' => [
                    [
                        'path' => 'fixture',
                        'newPath' => 'new_fixture',
                        'before' => null,
                        'after' => null,
                        'remove' => false,
                    ]
                ],
                'expected_order' => ['new_fixture', 'fixture2'],
            ],
            'replace_all_fixture' => [
                'existing_fixtures' => ['fixture', 'fixture', 'fixture2'],
                'config' => [
                    [
                        'path' => 'fixture',
                        'newPath' => 'new_fixture',
                        'before' => null,
                        'after' => null,
                        'remove' => false,
                    ]
                ],
                'expected_order' => ['new_fixture', 'new_fixture', 'fixture2'],
            ],
        ];
    }

    /**
     * Process apply configurations
     *
     * @param array $existingFixtures
     * @param array $config
     * @return array
     */
    private function processApply(array $existingFixtures, array $config): array
    {
        $this->setConfig($config);
        $fixtures = $this->object->apply($existingFixtures);

        return array_values($fixtures);
    }

    /**
     * Set config to method scope
     *
     * @param array $config
     * @return void
     */
    private function setConfig(array $config): void
    {
        $this->object->setClassConfig([]);
        $this->object->setDataSetConfig([]);
        $this->object->setMethodConfig($config);
    }
}
