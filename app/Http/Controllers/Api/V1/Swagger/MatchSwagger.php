<?php

namespace App\Http\Controllers\Api\V1\Swagger;

use OpenApi\Attributes as OA;

class MatchSwagger
{
    #[OA\Get(
        path: "/api/v1/matches",
        tags: ["Matches"],
        summary: "Get all matches",
        description: "التوافقات - Mutual interests that became matches - الإعجابات المتبادلة التي أصبحت توافقات",
        operationId: "getMatches",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Matches list - قائمة التوافقات",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/MatchWithChatStatus")
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized - غير مصرح")
        ]
    )]
    public function getMatches() {}

    #[OA\Get(
        path: "/api/v1/matches/{matchId}",
        tags: ["Matches"],
        summary: "Get single match details",
        description: "تفاصيل توافق معين",
        operationId: "getMatch",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "matchId",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Match details - تفاصيل التوافق",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "data", ref: "#/components/schemas/MatchWithChatStatus")
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Match not found - التوافق غير موجود")
        ]
    )]
    public function getMatch() {}

    #[OA\Post(
        path: "/api/v1/matches/{matchId}/chat-request",
        tags: ["Matches"],
        summary: "Request to start chat",
        description: "طلب محادثة - For non-convert females, this triggers guardian invitation - للإناث غير المحتديات، هذا يفعّل دعوة ولي الأمر",
        operationId: "requestChat",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "matchId",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: "Chat request created - تم إنشاء طلب المحادثة",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "تم إرسال طلب المحادثة، في انتظار موافقة ولي الأمر"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "chat_request", ref: "#/components/schemas/ChatRequest"),
                                new OA\Property(
                                    property: "status",
                                    type: "string",
                                    enum: ["pending_female", "pending_guardian", "approved"]
                                ),
                                new OA\Property(
                                    property: "guardian_invited",
                                    type: "boolean",
                                    description: "True if SMS sent to guardian - صحيح إذا تم إرسال رسالة نصية لولي الأمر"
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Chat request already exists - طلب المحادثة موجود بالفعل")
        ]
    )]
    public function requestChat() {}

    #[OA\Put(
        path: "/api/v1/matches/{matchId}/chat-request",
        tags: ["Matches"],
        summary: "Respond to chat request (female)",
        description: "الرد على طلب المحادثة - Female accepts/rejects before going to guardian - الأنثى تقبل/ترفض قبل الذهاب لولي الأمر",
        operationId: "respondToChatRequest",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "matchId",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["action"],
                properties: [
                    new OA\Property(property: "action", type: "string", enum: ["accept", "reject"]),
                    new OA\Property(property: "rejection_reason", type: "string", maxLength: 255)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Response recorded - تم تسجيل الرد",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "chat_request", ref: "#/components/schemas/ChatRequest"),
                                new OA\Property(property: "next_step", type: "string", example: "pending_guardian")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Match or chat request not found - التوافق أو طلب المحادثة غير موجود")
        ]
    )]
    public function respondToChatRequest() {}

    #[OA\Get(
        path: "/api/v1/conversations",
        tags: ["Conversations"],
        summary: "Get approved conversations",
        description: "المحادثات النشطة - Returns Firebase conversation IDs - يُرجع معرفات محادثات Firebase",
        operationId: "getConversations",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Conversations list - قائمة المحادثات",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "match_id", type: "integer"),
                                    new OA\Property(property: "other_user", ref: "#/components/schemas/ProfileCard"),
                                    new OA\Property(property: "firebase_conversation_id", type: "string", example: "conv_abc123"),
                                    new OA\Property(property: "approved_at", type: "string", format: "date-time"),
                                    new OA\Property(property: "guardian_approved", type: "boolean")
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthorized - غير مصرح")
        ]
    )]
    public function getConversations() {}
}
