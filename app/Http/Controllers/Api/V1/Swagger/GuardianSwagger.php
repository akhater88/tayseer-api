<?php

namespace App\Http\Controllers\Api\V1\Swagger;

use OpenApi\Attributes as OA;

class GuardianSwagger
{
    #[OA\Post(
        path: "/api/v1/guardian/verify-invitation",
        tags: ["Guardian"],
        summary: "Verify guardian invitation code",
        description: "التحقق من رمز دعوة ولي الأمر",
        operationId: "verifyGuardianInvitation",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["invitation_code"],
                properties: [
                    new OA\Property(
                        property: "invitation_code",
                        type: "string",
                        example: "ABC12345",
                        minLength: 8,
                        maxLength: 8
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Invitation valid - الدعوة صالحة",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "valid", type: "boolean", example: true),
                                new OA\Property(
                                    property: "female_user",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "name", type: "string", example: "فاطمة"),
                                        new OA\Property(property: "age", type: "integer", example: 25),
                                        new OA\Property(property: "city", type: "string", example: "الرياض")
                                    ]
                                ),
                                new OA\Property(property: "relationship", type: "string", example: "father"),
                                new OA\Property(property: "expires_at", type: "string", format: "date-time")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Invalid or expired invitation - دعوة غير صالحة أو منتهية الصلاحية")
        ]
    )]
    public function verifyGuardianInvitation() {}

    #[OA\Post(
        path: "/api/v1/guardian/register",
        tags: ["Guardian"],
        summary: "Register as guardian",
        description: "تسجيل ولي الأمر",
        operationId: "registerGuardian",
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["invitation_code", "full_name", "phone", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "invitation_code", type: "string", example: "ABC12345"),
                    new OA\Property(property: "full_name", type: "string", example: "محمد أحمد"),
                    new OA\Property(property: "phone", type: "string", example: "+966509876543"),
                    new OA\Property(
                        property: "otp_code",
                        type: "string",
                        example: "123456",
                        description: "Must verify phone first - يجب التحقق من الهاتف أولاً"
                    ),
                    new OA\Property(property: "password", type: "string", minLength: 8),
                    new OA\Property(property: "password_confirmation", type: "string")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Guardian registered - تم تسجيل ولي الأمر",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "user", ref: "#/components/schemas/User"),
                                new OA\Property(property: "token", type: "string"),
                                new OA\Property(
                                    property: "ward",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "name", type: "string"),
                                        new OA\Property(property: "pending_requests", type: "integer")
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Invalid invitation code - رمز دعوة غير صالح"),
            new OA\Response(response: 422, description: "Validation errors - أخطاء التحقق")
        ]
    )]
    public function registerGuardian() {}

    #[OA\Get(
        path: "/api/v1/guardian/dashboard",
        tags: ["Guardian Dashboard"],
        summary: "Guardian dashboard overview",
        description: "لوحة تحكم ولي الأمر",
        operationId: "guardianDashboard",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Dashboard data - بيانات لوحة التحكم",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(
                                    property: "ward",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "name", type: "string"),
                                        new OA\Property(property: "age", type: "integer"),
                                        new OA\Property(property: "profile_completion", type: "integer")
                                    ]
                                ),
                                new OA\Property(
                                    property: "stats",
                                    type: "object",
                                    properties: [
                                        new OA\Property(property: "pending_requests", type: "integer"),
                                        new OA\Property(property: "approved_conversations", type: "integer"),
                                        new OA\Property(property: "rejected_requests", type: "integer")
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized - غير مصرح"),
            new OA\Response(response: 403, description: "Not a guardian - ليس ولي أمر")
        ]
    )]
    public function guardianDashboard() {}

    #[OA\Get(
        path: "/api/v1/guardian/chat-requests",
        tags: ["Guardian Dashboard"],
        summary: "Get chat requests for approval",
        description: "طلبات المحادثة المعلقة",
        operationId: "guardianChatRequests",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "status",
                in: "query",
                schema: new OA\Schema(type: "string", enum: ["pending", "approved", "rejected"])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Chat requests list - قائمة طلبات المحادثة",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "suitor", ref: "#/components/schemas/ProfileCard"),
                                    new OA\Property(property: "status", type: "string"),
                                    new OA\Property(property: "requested_at", type: "string", format: "date-time")
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized - غير مصرح")
        ]
    )]
    public function guardianChatRequests() {}

    #[OA\Get(
        path: "/api/v1/guardian/chat-requests/{chatRequestId}",
        tags: ["Guardian Dashboard"],
        summary: "Get suitor details for review",
        description: "عرض تفاصيل المتقدم للموافقة",
        operationId: "guardianViewSuitor",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "chatRequestId",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Full suitor profile - الملف الشخصي الكامل للمتقدم",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "chat_request", ref: "#/components/schemas/ChatRequest"),
                                new OA\Property(property: "suitor", ref: "#/components/schemas/ProfileFull")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Chat request not found - طلب المحادثة غير موجود")
        ]
    )]
    public function guardianViewSuitor() {}

    #[OA\Put(
        path: "/api/v1/guardian/chat-requests/{chatRequestId}",
        tags: ["Guardian Dashboard"],
        summary: "Approve or reject chat request",
        description: "الموافقة أو رفض طلب المحادثة",
        operationId: "guardianRespondToChatRequest",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "chatRequestId",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["decision"],
                properties: [
                    new OA\Property(property: "decision", type: "string", enum: ["approved", "rejected"]),
                    new OA\Property(property: "rejection_reason", type: "string", maxLength: 255)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Decision recorded - تم تسجيل القرار",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "chat_request", ref: "#/components/schemas/ChatRequest"),
                                new OA\Property(
                                    property: "firebase_conversation_id",
                                    type: "string",
                                    description: "If approved - إذا تمت الموافقة"
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Chat request not found - طلب المحادثة غير موجود")
        ]
    )]
    public function guardianRespondToChatRequest() {}

    #[OA\Delete(
        path: "/api/v1/guardian/approved/{chatRequestId}",
        tags: ["Guardian Dashboard"],
        summary: "Revoke chat approval",
        description: "سحب الموافقة على المحادثة",
        operationId: "guardianRevokeApproval",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "chatRequestId",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Approval revoked - تم سحب الموافقة",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "تم سحب الموافقة")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Chat request not found - طلب المحادثة غير موجود")
        ]
    )]
    public function guardianRevokeApproval() {}
}
