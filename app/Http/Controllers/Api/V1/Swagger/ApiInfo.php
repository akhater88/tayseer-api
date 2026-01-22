<?php

namespace App\Http\Controllers\Api\V1\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Tayseer API - تطبيق تيسير",
    description: "Islamic Marriage App API with Guardian Approval System

## Overview / نظرة عامة
Tayseer is an Islamic marriage platform that implements proper guardian (ولي أمر) involvement for marriage conversations.

تيسير هو منصة زواج إسلامية تطبق مشاركة ولي الأمر بشكل صحيح في محادثات الزواج.

## Authentication / المصادقة
- Use `/auth/send-otp` and `/auth/verify-otp` for phone verification
- Registration returns a Bearer token
- Include token in header: `Authorization: Bearer {token}`

## Key Flows / المسارات الرئيسية
1. **Registration / التسجيل**: Phone OTP → Register → Active immediately
2. **Matching / التوافق**: Discover → Send Interest → Accept → Match created
3. **Chat Request / طلب المحادثة**: Request chat → Guardian approval (if applicable) → Chat enabled

## Guardian System / نظام ولي الأمر
- Non-convert females require guardian approval before chat
- Guardian is invited via SMS when first chat request is made
- Guardian approves/rejects each suitor individually

الإناث غير المحتديات يحتجن موافقة ولي الأمر قبل المحادثة
يتم دعوة ولي الأمر عبر رسالة نصية عند أول طلب محادثة
يوافق/يرفض ولي الأمر كل متقدم على حدة",
    contact: new OA\Contact(
        name: "Tayseer Support",
        email: "support@tayseer.app"
    ),
    license: new OA\License(
        name: "Proprietary",
        url: "https://tayseer.app/terms"
    )
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: "API Server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Enter your Bearer token from login/register response - أدخل رمز التوثيق من استجابة تسجيل الدخول/التسجيل"
)]
#[OA\Tag(
    name: "Authentication",
    description: "OTP verification, registration, login, logout - التحقق من OTP، التسجيل، تسجيل الدخول، تسجيل الخروج"
)]
#[OA\Tag(
    name: "Profile",
    description: "User profile management, photos, settings - إدارة الملف الشخصي، الصور، الإعدادات"
)]
#[OA\Tag(
    name: "Lookups",
    description: "Countries, cities, nationalities, enums - الدول، المدن، الجنسيات، القيم الثابتة"
)]
#[OA\Tag(
    name: "Discovery",
    description: "Browse and discover potential matches - تصفح واكتشاف التوافقات المحتملة"
)]
#[OA\Tag(
    name: "Search",
    description: "Advanced profile search with filters - البحث المتقدم مع الفلاتر"
)]
#[OA\Tag(
    name: "Interests",
    description: "Send and manage interests - إرسال وإدارة الإعجابات"
)]
#[OA\Tag(
    name: "Matches",
    description: "View matches and request chat - عرض التوافقات وطلب المحادثة"
)]
#[OA\Tag(
    name: "Conversations",
    description: "Active chat conversations - المحادثات النشطة"
)]
#[OA\Tag(
    name: "Guardian",
    description: "Guardian registration and management - تسجيل وإدارة ولي الأمر"
)]
#[OA\Tag(
    name: "Guardian Dashboard",
    description: "Guardian approval workflow - سير عمل موافقة ولي الأمر"
)]
#[OA\Tag(
    name: "Favorites",
    description: "Favorite profiles management - إدارة المفضلة"
)]
#[OA\Tag(
    name: "Blocks",
    description: "Block users - حظر المستخدمين"
)]
#[OA\Tag(
    name: "Reports",
    description: "Report inappropriate users - الإبلاغ عن المستخدمين غير المناسبين"
)]
class ApiInfo
{
}
