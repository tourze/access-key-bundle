<?php

namespace Tourze\AccessKeyBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(
    name: 'access_key_statistics',
    options: ['comment' => 'AccessKey按小时统计'],
)]
#[ORM\UniqueConstraint(name: 'uk_access_key_hour', columns: ['access_key_id', 'hour'])]
class AccessKeyStatistics implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::BIGINT, options: ['comment' => '主键ID'])]
    private ?int $id = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne(targetEntity: AccessKey::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private AccessKey $accessKey;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false, options: ['comment' => '统计小时（精确到小时）'])]
    private \DateTimeImmutable $hour;

    #[Assert\Type(type: 'int')]
    #[Assert\PositiveOrZero]
    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '成功次数', 'default' => 0])]
    private int $successCount = 0;

    #[Assert\Type(type: 'int')]
    #[Assert\PositiveOrZero]
    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ['comment' => '失败次数', 'default' => 0])]
    private int $failureCount = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccessKey(): AccessKey
    {
        return $this->accessKey;
    }

    public function setAccessKey(AccessKey $accessKey): void
    {
        $this->accessKey = $accessKey;
    }

    public function getHour(): \DateTimeImmutable
    {
        return $this->hour;
    }

    public function setHour(\DateTimeImmutable $hour): void
    {
        $this->hour = $hour;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function setSuccessCount(int $successCount): void
    {
        $this->successCount = $successCount;
    }

    public function getFailureCount(): int
    {
        return $this->failureCount;
    }

    public function setFailureCount(int $failureCount): void
    {
        $this->failureCount = $failureCount;
    }

    public function incrementSuccess(): self
    {
        ++$this->successCount;

        return $this;
    }

    public function incrementFailure(): self
    {
        ++$this->failureCount;

        return $this;
    }

    public function getSuccessRate(): float
    {
        $total = $this->getTotalCount();

        return $total > 0 ? $this->successCount / $total : 0.0;
    }

    public function getTotalCount(): int
    {
        return $this->successCount + $this->failureCount;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s - %s (S:%d/F:%d)',
            $this->accessKey->getTitle(),
            $this->hour->format('Y-m-d H:00'),
            $this->successCount,
            $this->failureCount
        );
    }
}
