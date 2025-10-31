# Access Key Bundle

[English](README.md) | [中文](README.zh-CN.md)

用于 API 访问密钥管理的 Symfony Bundle，支持签名验证、IP 白名单和 AES 加密。

## 功能特性

- **访问密钥管理**：AppID/AppSecret 密钥对管理
- **签名验证**：可配置签名超时时间（默认 180 秒）
- **IP 白名单**：限制特定 IP 访问
- **AES 加密**：支持 AES 密钥配置
- **统计分析**：成功/失败调用统计
- **EasyAdmin 集成**：后台管理界面
- **访问控制**：需要 ROLE_ADMIN 权限

## 安装

```bash
composer require tourze/access-key-bundle
```

## 配置

在 `config/bundles.php` 中注册：

```php
return [
    // ...
    Tourze\AccessKeyBundle\AccessKeyBundle::class => ['all' => true],
];
```

执行数据库迁移：

```bash
php bin/console doctrine:migrations:migrate
```

## 使用方法

### 1. 获取有效的访问密钥

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
        
        // 使用 $accessKey
    }
}
```

### 2. 记录调用统计

```php
// 记录成功调用
$this->apiCallerService->recordSuccess($accessKey);

// 记录失败调用
$this->apiCallerService->recordFailure($accessKey);
```

### 3. 获取统计数据

```php
use Tourze\AccessKeyBundle\Service\StatisticsService;

class StatisticsController
{
    public function __construct(
        private StatisticsService $statisticsService
    ) {}

    public function getStats(AccessKey $accessKey): array
    {
        // 今日统计
        $todayStats = $this->statisticsService->getTodayStatistics($accessKey);
        
        // 本周统计
        $weeklyStats = $this->statisticsService->getWeeklyStatistics($accessKey);
        
        // 本月统计
        $monthlyStats = $this->statisticsService->getMonthlyStatistics($accessKey);
        
        // 自定义时间范围统计
        $customStats = $this->statisticsService->getSummary(
            $accessKey,
            new \DateTimeImmutable('2023-01-01'),
            new \DateTimeImmutable('2023-12-31')
        );
        
        return compact('todayStats', 'weeklyStats', 'monthlyStats', 'customStats');
    }
}
```

## 数据模型

### AccessKey 实体

- `title`：名称（必填，最大 60 字符）
- `appId`：应用 ID（必填，最大 64 字符，唯一）
- `appSecret`：应用密钥（可选，最大 120 字符）
- `allowIps`：允许访问的 IP 列表（JSON 数组）
- `signTimeoutSecond`：签名超时时间（秒，默认 180）
- `aesKey`：AES 加密密钥（可选）
- `remark`：备注信息（可选）
- `valid`：是否有效（布尔值，默认 false）
- `owner`：所有者（用户引用）

### AccessKeyStatistics 实体

- `accessKey`：关联的访问密钥
- `hour`：统计时间（按小时）
- `successCount`：成功次数
- `failureCount`：失败次数
- `totalCount`：总次数
- `successRate`：成功率

## 后台管理

Bundle 集成了 EasyAdmin 管理界面：

- 创建、编辑、删除访问密钥
- 查看使用统计
- 管理 IP 白名单
- 配置签名参数

访问路径：`/admin` -> Access Keys

## 权限要求

- 所有操作需要 `ROLE_ADMIN` 权限
- 确保用户具有相应权限才能访问管理功能

## Bundle 依赖

- `doctrine/orm`：数据库 ORM
- `easycorp/easyadmin-bundle`：后台管理
- `tourze/doctrine-snowflake-bundle`：Snowflake ID
- `tourze/doctrine-timestamp-bundle`：时间戳
- `tourze/doctrine-track-bundle`：变更跟踪

## 许可证

MIT