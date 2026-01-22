<?php

namespace App\Http\Controllers\Api\V1\Swagger;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "User",
    type: "object",
    description: "Basic user information - معلومات المستخدم الأساسية",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "slug", type: "string", example: "ahmed-m4k9"),
        new OA\Property(property: "username", type: "string", example: "ahmed_m"),
        new OA\Property(property: "gender", type: "string", enum: ["male", "female"]),
        new OA\Property(property: "user_type", type: "string", enum: ["member", "guardian"]),
        new OA\Property(property: "status", type: "string", enum: ["pending", "active", "suspended", "banned"]),
        new OA\Property(property: "is_online", type: "boolean"),
        new OA\Property(property: "last_online_at", type: "string", format: "date-time"),
        new OA\Property(property: "created_at", type: "string", format: "date-time")
    ]
)]
#[OA\Schema(
    schema: "UserFull",
    type: "object",
    description: "Full user information with profile and photos - معلومات المستخدم الكاملة مع الملف الشخصي والصور",
    allOf: [
        new OA\Schema(ref: "#/components/schemas/User")
    ],
    properties: [
        new OA\Property(property: "profile", ref: "#/components/schemas/Profile"),
        new OA\Property(
            property: "photos",
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/Photo")
        ),
        new OA\Property(property: "guardian_status", type: "string", enum: ["not_required", "pending", "active"])
    ]
)]
#[OA\Schema(
    schema: "Profile",
    type: "object",
    description: "User profile details - تفاصيل الملف الشخصي",
    properties: [
        new OA\Property(property: "age", type: "integer", example: 28),
        new OA\Property(
            property: "nationality",
            type: "object",
            properties: [
                new OA\Property(property: "id", type: "integer"),
                new OA\Property(property: "name", type: "string")
            ]
        ),
        new OA\Property(
            property: "country",
            type: "object",
            properties: [
                new OA\Property(property: "id", type: "integer"),
                new OA\Property(property: "name", type: "string")
            ]
        ),
        new OA\Property(
            property: "city",
            type: "object",
            properties: [
                new OA\Property(property: "id", type: "integer"),
                new OA\Property(property: "name", type: "string")
            ]
        ),
        new OA\Property(property: "marital_status", type: "string"),
        new OA\Property(property: "number_of_children", type: "integer"),
        new OA\Property(property: "height_cm", type: "integer"),
        new OA\Property(property: "weight_kg", type: "integer"),
        new OA\Property(property: "skin_color", type: "string"),
        new OA\Property(property: "body_type", type: "string"),
        new OA\Property(property: "religious_level", type: "string"),
        new OA\Property(property: "prayer_level", type: "string"),
        new OA\Property(property: "smoking", type: "string"),
        new OA\Property(property: "beard_type", type: "string", description: "Males only - للذكور فقط"),
        new OA\Property(property: "hijab_type", type: "string", description: "Females only - للإناث فقط"),
        new OA\Property(property: "education_level", type: "string"),
        new OA\Property(property: "work_field", type: "object"),
        new OA\Property(property: "job_title", type: "string"),
        new OA\Property(property: "about_me", type: "string"),
        new OA\Property(property: "partner_preferences", type: "string"),
        new OA\Property(property: "profile_completion", type: "integer", example: 85)
    ]
)]
#[OA\Schema(
    schema: "ProfileCard",
    type: "object",
    description: "Simplified profile for listings - ملف شخصي مبسط للقوائم",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "slug", type: "string"),
        new OA\Property(property: "username", type: "string"),
        new OA\Property(property: "age", type: "integer"),
        new OA\Property(property: "city", type: "string"),
        new OA\Property(property: "country", type: "string"),
        new OA\Property(property: "marital_status", type: "string"),
        new OA\Property(property: "religious_level", type: "string"),
        new OA\Property(property: "primary_photo", ref: "#/components/schemas/Photo"),
        new OA\Property(property: "is_online", type: "boolean"),
        new OA\Property(property: "is_favorited", type: "boolean")
    ]
)]
#[OA\Schema(
    schema: "ProfileFull",
    type: "object",
    description: "Full profile view - عرض الملف الشخصي الكامل",
    allOf: [
        new OA\Schema(ref: "#/components/schemas/ProfileCard")
    ],
    properties: [
        new OA\Property(property: "profile", ref: "#/components/schemas/Profile"),
        new OA\Property(
            property: "photos",
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/Photo")
        ),
        new OA\Property(
            property: "interest_status",
            type: "string",
            enum: ["none", "sent", "received", "mutual"]
        )
    ]
)]
#[OA\Schema(
    schema: "Photo",
    type: "object",
    description: "User photo - صورة المستخدم",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "url", type: "string", format: "uri"),
        new OA\Property(property: "thumbnail_url", type: "string", format: "uri"),
        new OA\Property(property: "is_primary", type: "boolean"),
        new OA\Property(property: "sort_order", type: "integer")
    ]
)]
#[OA\Schema(
    schema: "Interest",
    type: "object",
    description: "Interest/like between users - الإعجاب بين المستخدمين",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "sender_id", type: "integer"),
        new OA\Property(property: "receiver_id", type: "integer"),
        new OA\Property(property: "message", type: "string"),
        new OA\Property(
            property: "status",
            type: "string",
            enum: ["pending", "accepted", "declined", "withdrawn"]
        ),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "responded_at", type: "string", format: "date-time")
    ]
)]
#[OA\Schema(
    schema: "InterestWithUser",
    type: "object",
    description: "Interest with user profile - الإعجاب مع الملف الشخصي",
    allOf: [
        new OA\Schema(ref: "#/components/schemas/Interest")
    ],
    properties: [
        new OA\Property(
            property: "user",
            ref: "#/components/schemas/ProfileCard",
            description: "The other user (sender or receiver) - المستخدم الآخر"
        )
    ]
)]
#[OA\Schema(
    schema: "Match",
    type: "object",
    description: "Match between two users - التوافق بين مستخدمين",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "matched_at", type: "string", format: "date-time"),
        new OA\Property(property: "status", type: "string", enum: ["active", "ended"])
    ]
)]
#[OA\Schema(
    schema: "MatchWithChatStatus",
    type: "object",
    description: "Match with chat request status - التوافق مع حالة طلب المحادثة",
    allOf: [
        new OA\Schema(ref: "#/components/schemas/Match")
    ],
    properties: [
        new OA\Property(property: "other_user", ref: "#/components/schemas/ProfileCard"),
        new OA\Property(
            property: "chat_request_status",
            type: "string",
            enum: ["none", "pending_female", "pending_guardian", "approved", "rejected"],
            description: "Current chat request status - حالة طلب المحادثة الحالية"
        ),
        new OA\Property(property: "can_request_chat", type: "boolean")
    ]
)]
#[OA\Schema(
    schema: "ChatRequest",
    type: "object",
    description: "Chat request for guardian approval - طلب المحادثة لموافقة ولي الأمر",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "match_id", type: "integer"),
        new OA\Property(property: "requester_id", type: "integer"),
        new OA\Property(property: "receiver_id", type: "integer"),
        new OA\Property(
            property: "status",
            type: "string",
            enum: ["pending_female", "pending_guardian", "approved", "rejected"]
        ),
        new OA\Property(
            property: "guardian_decision",
            type: "string",
            enum: ["approved", "rejected"],
            nullable: true
        ),
        new OA\Property(property: "guardian_rejection_reason", type: "string", nullable: true),
        new OA\Property(property: "firebase_conversation_id", type: "string", nullable: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "guardian_reviewed_at", type: "string", format: "date-time", nullable: true)
    ]
)]
#[OA\Schema(
    schema: "EnumValue",
    type: "object",
    description: "Enum value with translations - قيمة ثابتة مع الترجمات",
    properties: [
        new OA\Property(property: "value", type: "string", example: "single"),
        new OA\Property(property: "label_ar", type: "string", example: "أعزب"),
        new OA\Property(property: "label_en", type: "string", example: "Single")
    ]
)]
#[OA\Schema(
    schema: "Pagination",
    type: "object",
    description: "Pagination metadata - بيانات ترقيم الصفحات",
    properties: [
        new OA\Property(property: "current_page", type: "integer", example: 1),
        new OA\Property(property: "last_page", type: "integer", example: 10),
        new OA\Property(property: "per_page", type: "integer", example: 20),
        new OA\Property(property: "total", type: "integer", example: 195)
    ]
)]
class Schemas
{
}
