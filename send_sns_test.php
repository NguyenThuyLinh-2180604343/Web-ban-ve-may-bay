<?php
require 'vendor/autoload.php';

use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;

// Cấu hình kết nối AWS SNS
$snsClient = new SnsClient([
    'region' => 'ap-southeast-1', // Singapore
    'version' => 'latest',
    'credentials' => [
        'key' => 'YOUR_AWS_ACCESS_KEY',
        'secret' => 'YOUR_AWS_SECRET_KEY',
    ]
]);

$topicArn = 'arn:aws:sns:ap-southeast-1:xxxxxxxxxxxx:dat-ve-thanh-cong'; // thay ARN thật

$message = "Test: Khách hàng Nguyễn Văn A vừa đặt vé thành công.";

try {
    $snsClient->publish([
        'TopicArn' => $topicArn,
        'Message' => $message
    ]);
    echo "✅ Đã gửi SNS thành công.";
} catch (AwsException $e) {
    echo "❌ Lỗi gửi SNS: " . $e->getMessage();
}
