<?php

namespace Tourze\AccessKeyBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\AccessKeyBundle\Repository\AccessKeyRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

/**
 * 主要用来识别接口调用方，目前只有签名业务用到
 *
 * @see https://happypeter.github.io/binfo/aes
 */
#[IsGranted(attribute: 'ROLE_ADMIN')]
#[ORM\Entity(repositoryClass: AccessKeyRepository::class)]
#[ORM\Table(name: 'api_caller', options: ['comment' => 'API调用者'])]
class AccessKey implements \Stringable
{
    use SnowflakeKeyAware;
    use TimestampableAware;
    use BlameableAware;

    #[Assert\NotBlank]
    #[Assert\Length(max: 60)]
    #[ORM\Column(type: Types::STRING, length: 60, unique: true, options: ['comment' => '名称'])]
    private string $title;

    #[Assert\NotBlank]
    #[Assert\Length(max: 64)]
    #[ORM\Column(type: Types::STRING, length: 64, unique: true, nullable: false, options: ['comment' => 'AppID'])]
    private string $appId;

    #[Assert\Length(max: 120)]
    #[ORM\Column(type: Types::STRING, length: 120, nullable: true, options: ['comment' => 'AppSecret'])]
    private ?string $appSecret = null;

    /**
     * @var array<string>|null
     */
    #[Assert\Type(type: 'array')]
    #[ORM\Column(type: Types::JSON, nullable: true, options: ['comment' => '允许调用IP'])]
    private ?array $allowIps = [];

    #[Assert\Type(type: 'int')]
    #[Assert\PositiveOrZero]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['comment' => '签名超时时间', 'default' => 180])]
    private ?int $signTimeoutSecond = 180;

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => 'AES Key'])]
    private ?string $aesKey = null;

    #[Assert\Type(type: 'string')]
    #[Assert\Length(max: 65535)]
    #[ORM\Column(type: Types::TEXT, nullable: true, options: ['comment' => '备注', 'default' => ''])]
    private ?string $remark = null;

    #[IndexColumn]
    #[TrackColumn]
    #[Assert\Type(type: 'bool')]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?UserInterface $owner = null;

    public function getAppId(): string
    {
        return $this->appId;
    }

    public function setAppId(string $appId): void
    {
        $this->appId = $appId;
    }

    public function getAppSecret(): ?string
    {
        return $this->appSecret;
    }

    public function setAppSecret(?string $appSecret): void
    {
        $this->appSecret = $appSecret;
    }

    /**
     * @return array<string>|null
     */
    public function getAllowIps(): ?array
    {
        return $this->allowIps;
    }

    /**
     * @param array<string>|null $allowIps
     */
    public function setAllowIps(?array $allowIps): void
    {
        $this->allowIps = $allowIps;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSignTimeoutSecond(): ?int
    {
        return $this->signTimeoutSecond;
    }

    public function setSignTimeoutSecond(?int $signTimeoutSecond): void
    {
        $this->signTimeoutSecond = $signTimeoutSecond;
    }

    public function getAesKey(): ?string
    {
        return $this->aesKey;
    }

    public function setAesKey(?string $aesKey): void
    {
        $this->aesKey = $aesKey;
    }

    public function getRemark(): ?string
    {
        return $this->remark;
    }

    public function setRemark(?string $remark): void
    {
        $this->remark = $remark;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getOwner(): ?UserInterface
    {
        return $this->owner;
    }

    public function setOwner(?UserInterface $owner): void
    {
        $this->owner = $owner;
    }

    public function __toString(): string
    {
        return $this->title;
    }
}
