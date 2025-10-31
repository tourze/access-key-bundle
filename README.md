# Access Key Bundle

[English](README.md) | [中文](README.zh-CN.md)

Symfony Bundle for API access key management with signature verification, IP whitelist, and AES encryption support.

## Features

- **Access Key Management**: AppID/AppSecret key pair management
- **Signature Verification**: Configurable signature timeout (default 180 seconds)
- **IP Whitelist**: Restrict access to specific IPs
- **AES Encryption**: Support for AES key configuration
- **Statistics**: Success/failure call statistics
- **EasyAdmin Integration**: Admin panel interface
- **Access Control**: Requires ROLE_ADMIN permission

## Installation

```bash
composer require tourze/access-key-bundle
```

## Configuration

Register in `config/bundles.php`:

```php
return [
    // ...
    Tourze\AccessKeyBundle\AccessKeyBundle::class => ['all' => true],
];
```

Run database migrations:

```bash
php bin/console doctrine:migrations:migrate
```

## Usage

### 1. Get Valid Access Key

```php
use Tourze\AccessKeyBundle\Service\ApiCallerService;

class YourController
{
    public function __construct(
        private ApiCallerService $apiCallerService
    ) {}

    public function someAction(Request $request): Response
    {
        $appId = $request->headers->get('X-App-Id');
        $accessKey = $this->apiCallerService->findValidApiCallerByAppId($appId);
        
        if (!$accessKey) {
            throw new UnauthorizedHttpException('Invalid app id');
        }
        
        // Use $accessKey
    }
}
```

### 2. Record Call Statistics

```php
// Record successful call
$this->apiCallerService->recordSuccess($accessKey);

// Record failed call
$this->apiCallerService->recordFailure($accessKey);
```

### 3. Get Statistics Data

```php
use Tourze\AccessKeyBundle\Service\StatisticsService;

class StatisticsController
{
    public function __construct(
        private StatisticsService $statisticsService
    ) {}

    public function getStats(AccessKey $accessKey): array
    {
        // Today's statistics
        $todayStats = $this->statisticsService->getTodayStatistics($accessKey);
        
        // Weekly statistics
        $weeklyStats = $this->statisticsService->getWeeklyStatistics($accessKey);
        
        // Monthly statistics
        $monthlyStats = $this->statisticsService->getMonthlyStatistics($accessKey);
        
        // Custom date range statistics
        $customStats = $this->statisticsService->getSummary(
            $accessKey,
            new \DateTimeImmutable('2023-01-01'),
            new \DateTimeImmutable('2023-12-31')
        );
        
        return compact('todayStats', 'weeklyStats', 'monthlyStats', 'customStats');
    }
}
```

## Data Models

### AccessKey Entity

- `title`: Name (required, max 60 characters)
- `appId`: Application ID (required, max 64 characters, unique)
- `appSecret`: Application secret (optional, max 120 characters)
- `allowIps`: Allowed IP list (JSON array)
- `signTimeoutSecond`: Signature timeout in seconds (default 180)
- `aesKey`: AES encryption key (optional)
- `remark`: Remarks (optional)
- `valid`: Is valid (boolean, default false)
- `owner`: Owner (user reference)

### AccessKeyStatistics Entity

- `accessKey`: Associated access key
- `hour`: Statistics time (hourly)
- `successCount`: Success count
- `failureCount`: Failure count
- `totalCount`: Total count
- `successRate`: Success rate

## Admin Panel

Bundle integrates with EasyAdmin for management interface:

- Create, edit, delete access keys
- View usage statistics
- Manage IP whitelist
- Configure signature parameters

Access path: `/admin` -> Access Keys

## Permission Requirements

- All operations require `ROLE_ADMIN` permission
- Ensure users have appropriate permissions to access management features

## Bundle Dependencies

- `doctrine/orm`: Database ORM
- `easycorp/easyadmin-bundle`: Admin panel
- `tourze/doctrine-snowflake-bundle`: Snowflake ID
- `tourze/doctrine-timestamp-bundle`: Timestamps
- `tourze/doctrine-track-bundle`: Change tracking

## License

MIT