<?php

namespace Tourze\AccessKeyBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tourze\AccessKeyBundle\Entity\AccessKey;
use Tourze\AccessKeyBundle\Entity\AccessKeyStatistics;

final class AccessKeyStatisticsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $accessKey = $this->getReference(AccessKeyFixtures::DEFAULT_CALLER_REFERENCE, AccessKey::class);
        $baseTime = new \DateTimeImmutable('2023-10-15 00:00:00');

        for ($hour = 0; $hour < 24; ++$hour) {
            $hourTime = $baseTime->setTime($hour, 0, 0);
            $statistics = new AccessKeyStatistics();
            $statistics->setAccessKey($accessKey);
            $statistics->setHour($hourTime);
            $statistics->setSuccessCount(rand(50, 200));
            $statistics->setFailureCount(rand(0, 10));

            $manager->persist($statistics);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AccessKeyFixtures::class,
        ];
    }
}
