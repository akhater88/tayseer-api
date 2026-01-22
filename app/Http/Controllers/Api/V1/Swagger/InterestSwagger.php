<?php

namespace App\Http\Controllers\Api\V1\Swagger;

use OpenApi\Attributes as OA;

class InterestSwagger
{
    #[OA\Post(
        path: "/api/v1/interests",
        tags: ["Interests"],
        summary: "Send interest to a user",
        description: "إرسال إعجاب - Daily limit: 20 interests - الحد اليومي: 20 إعجاب",
        operationId: "sendInterest",
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["user_id"],
                properties: [
                    new OA\Property(property: "user_id", type: "integer", example: 5),
                    new OA\Property(
                        property: "message",
                        type: "string",
                        maxLength: 100,
                        example: "أعجبني ملفك الشخصي",
                        description: "Optional short message - رسالة قصيرة اختيارية"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Interest sent - تم إرسال الإعجاب",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "تم إرسال الإعجاب"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Interest")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Cannot send interest (blocked, same gender, already sent) - لا يمكن إرسال الإعجاب"),
            new OA\Response(response: 429, description: "Daily limit reached - تم الوصول للحد اليومي")
        ]
    )]
    public function sendInterest() {}

    #[OA\Get(
        path: "/api/v1/interests/sent",
        tags: ["Interests"],
        summary: "Get sent interests",
        description: "الإعجابات المرسلة",
        operationId: "sentInterests",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "status",
                in: "query",
                schema: new OA\Schema(type: "string", enum: ["pending", "accepted", "declined"])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Sent interests list - قائمة الإعجابات المرسلة",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/InterestWithUser")
                        )
                    ]
                )
            )
        ]
    )]
    public function sentInterests() {}

    #[OA\Get(
        path: "/api/v1/interests/received",
        tags: ["Interests"],
        summary: "Get received interests",
        description: "الإعجابات المستلمة",
        operationId: "receivedInterests",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "status",
                in: "query",
                schema: new OA\Schema(type: "string", enum: ["pending", "accepted", "declined"])
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Received interests list - قائمة الإعجابات المستلمة",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/InterestWithUser")
                        )
                    ]
                )
            )
        ]
    )]
    public function receivedInterests() {}

    #[OA\Put(
        path: "/api/v1/interests/{interestId}/respond",
        tags: ["Interests"],
        summary: "Respond to an interest",
        description: "الرد على إعجاب - Accept creates a match - القبول ينشئ توافق",
        operationId: "respondToInterest",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "interestId",
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
                    new OA\Property(property: "action", type: "string", enum: ["accept", "decline"])
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
                                new OA\Property(property: "interest", ref: "#/components/schemas/Interest"),
                                new OA\Property(
                                    property: "match",
                                    ref: "#/components/schemas/Match",
                                    description: "If accepted - إذا تم القبول"
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 404, description: "Interest not found - الإعجاب غير موجود")
        ]
    )]
    public function respondToInterest() {}

    #[OA\Delete(
        path: "/api/v1/interests/{interestId}",
        tags: ["Interests"],
        summary: "Withdraw sent interest",
        description: "سحب الإعجاب - Only pending interests - الإعجابات المعلقة فقط",
        operationId: "withdrawInterest",
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "interestId",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Interest withdrawn - تم سحب الإعجاب",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "تم سحب الإعجاب")
                    ]
                )
            ),
            new OA\Response(response: 400, description: "Cannot withdraw (not pending or not owner) - لا يمكن السحب")
        ]
    )]
    public function withdrawInterest() {}
}
