<?php

namespace Oro\Bundle\SoapBundle\Tests\Unit\Serializer;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

use Oro\Bundle\SoapBundle\Serializer\EntityDataAccessor;
use Oro\Bundle\SoapBundle\Serializer\EntityDataTransformer;
use Oro\Bundle\SoapBundle\Serializer\EntitySerializer;
use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\OrmTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\Doctrine\ORM\Mocks\EntityManagerMock;

abstract class EntitySerializerTestCase extends OrmTestCase
{
    /** @var EntityManagerMock */
    protected $em;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $configManager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $container;

    /** @var EntitySerializer */
    protected $serializer;

    protected function setUp()
    {
        $reader         = new AnnotationReader();
        $metadataDriver = new AnnotationDriver(
            $reader,
            'Oro\Bundle\SoapBundle\Tests\Unit\Serializer\Fixtures\Entity'
        );

        $this->em = $this->getTestEntityManager();
        $this->em->getConfiguration()->setMetadataDriverImpl($metadataDriver);
        $this->em->getConfiguration()->setEntityNamespaces(
            [
                'Test' => 'Oro\Bundle\SoapBundle\Tests\Unit\Serializer\Fixtures\Entity'
            ]
        );

        $doctrine = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrine->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($this->em));
        $doctrine->expects($this->any())
            ->method('getAliasNamespace')
            ->will(
                $this->returnValueMap(
                    [
                        ['Test', 'Oro\Bundle\SoapBundle\Tests\Unit\Serializer\Fixtures\Entity']
                    ]
                )
            );

        $this->configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $queryHintResolver = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\QueryHintResolver')
            ->disableOriginalConstructor()
            ->getMock();

        $this->serializer = new EntitySerializer(
            $doctrine,
            $this->configManager,
            new EntityDataAccessor(),
            new EntityDataTransformer($this->container),
            $queryHintResolver
        );
    }

    /**
     * @param array  $expected
     * @param array  $actual
     * @param string $message
     */
    protected function assertArrayEquals(array $expected, array $actual, $message = '')
    {
        $this->sortByKeyRecursive($expected);
        $this->sortByKeyRecursive($actual);
        $this->assertSame($expected, $actual, $message);
    }

    /**
     * @param string $expected
     * @param string $actual
     * @param string $message
     */
    protected function assertDqlEquals($expected, $actual, $message = '')
    {
        $expected = str_replace('Test:', 'Oro\Bundle\SoapBundle\Tests\Unit\Serializer\Fixtures\Entity\\', $expected);
        $this->assertEquals($expected, $actual, $message);
    }

    /**
     * @param array $array
     */
    protected function sortByKeyRecursive(array &$array)
    {
        ksort($array);
        foreach ($array as &$val) {
            if ($val && is_array($val)) {
                $this->sortByKeyRecursive($val);
            }
        }
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $conn
     * @param string                                   $sql
     * @param array                                    $result
     * @param array                                    $params
     * @param array                                    $types
     */
    protected function setQueryExpectation(
        \PHPUnit_Framework_MockObject_MockObject $conn,
        $sql,
        $result,
        $params = [],
        $types = []
    ) {
        $stmt = $this->createFetchStatementMock($result, $params, $types);
        if ($params) {
            $conn->expects($this->once())
                ->method('prepare')
                ->with($sql)
                ->will($this->returnValue($stmt));
        } else {
            $conn
                ->expects($this->once())
                ->method('query')
                ->with($sql)
                ->will($this->returnValue($stmt));
        }
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $conn
     * @param int                                      $expectsAt
     * @param string                                   $sql
     * @param array                                    $result
     * @param array                                    $params
     * @param array                                    $types
     */
    protected function setQueryExpectationAt(
        \PHPUnit_Framework_MockObject_MockObject $conn,
        $expectsAt,
        $sql,
        $result,
        $params = [],
        $types = []
    ) {
        $stmt = $this->createFetchStatementMock($result, $params, $types);
        if ($params) {
            $conn->expects($this->at($expectsAt))
                ->method('prepare')
                ->with($sql)
                ->will($this->returnValue($stmt));
        } else {
            $conn
                ->expects($this->at($expectsAt))
                ->method('query')
                ->with($sql)
                ->will($this->returnValue($stmt));
        }
    }
}
